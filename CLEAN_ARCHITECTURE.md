# Clean Architecture Implementation

The Core SDK has been refactored to implement **Clean Architecture** principles, making it more maintainable, testable, and framework-independent while maintaining its plug-and-play Laravel package functionality.

## Architecture Overview

The clean architecture implementation follows the dependency inversion principle, with inner layers defining interfaces that outer layers implement.

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
│  Controllers, Livewire Components, Views, CLI Commands     │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                   Infrastructure Layer                     │
│     Repositories, Services, Guards, Middleware             │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                       │
│              Use Cases, DTOs, Services                     │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                     Domain Layer                           │
│        Entities, Value Objects, Repository Interfaces      │
└─────────────────────────────────────────────────────────────┘
```

## Domain Layer (Core Business Logic)

The domain layer contains the core business logic and is completely framework-agnostic.

### Entities

**`VoxDev\Core\Domain\Entities\User`**
- Represents a user in the system
- Contains business logic for user operations
- Immutable with value object properties

```php
use VoxDev\Core\Domain\Entities\User;
use VoxDev\Core\Domain\ValueObjects\{UserId, UserName, Email};

$user = new User(
    UserId::fromValue(1),
    UserName::fromValue('John Doe'),
    Email::fromValue('john@example.com'),
    'https://example.com/avatar.jpg',
    ['role' => 'admin']
);

$userId = $user->getId(); // UserId value object
$email = $user->getEmail(); // Email value object
$role = $user->getAttribute('role'); // 'admin'
```

### Value Objects

**`VoxDev\Core\Domain\ValueObjects\Email`**
- Validates email format
- Normalizes to lowercase
- Provides domain/local part extraction

**`VoxDev\Core\Domain\ValueObjects\UserId`**
- Represents user identifier
- Supports string or integer IDs
- Provides equality comparison

**`VoxDev\Core\Domain\ValueObjects\UserName`**
- Validates user name
- Enforces length constraints
- Trims whitespace

**`VoxDev\Core\Domain\ValueObjects\AccessToken`**
- Represents OAuth access token
- Handles expiration logic
- Provides token refresh detection

**`VoxDev\Core\Domain\ValueObjects\RefreshToken`**
- Represents OAuth refresh token
- Immutable token value

**`VoxDev\Core\Domain\ValueObjects\OAuthCredentials`**
- Encapsulates OAuth client configuration
- Validates credentials
- Manages scopes

### Repository Interfaces

**`VoxDev\Core\Domain\Repositories\UserRepositoryInterface`**
```php
interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;
    public function save(User $user): void;
    public function delete(UserId $id): void;
    public function exists(UserId $id): bool;
}
```

**`VoxDev\Core\Domain\Repositories\TokenRepositoryInterface`**
```php
interface TokenRepositoryInterface
{
    public function storeTokens(UserId $userId, AccessToken $accessToken, ?RefreshToken $refreshToken = null): void;
    public function getAccessToken(UserId $userId): ?AccessToken;
    public function getRefreshToken(UserId $userId): ?RefreshToken;
    public function clearTokens(UserId $userId): void;
    public function hasValidTokens(UserId $userId): bool;
}
```

## Application Layer (Use Cases)

The application layer orchestrates domain entities and defines use cases.

### Use Cases

**`VoxDev\Core\Application\UseCases\AuthenticateUser`**
- Handles complete OAuth authentication flow
- Coordinates between OAuth service, user repository, and token repository
- Returns success/failure response DTOs

```php
use VoxDev\Core\Application\UseCases\AuthenticateUser;
use VoxDev\Core\Application\DTOs\AuthenticationRequest;

$useCase = app(AuthenticateUser::class);
$request = new AuthenticationRequest($code, $credentials, $state);
$response = $useCase->execute($request);

if ($response->isSuccessful()) {
    $user = $response->getUser();
    $token = $response->getAccessToken();
}
```

**`VoxDev\Core\Application\UseCases\RefreshUserToken`**
- Handles token refresh operations
- Validates user existence
- Updates stored tokens

### DTOs (Data Transfer Objects)

**`AuthenticationRequest`** / **`AuthenticationResponse`**
- Encapsulate authentication flow data
- Provide type safety for use case inputs/outputs

**`TokenRefreshRequest`** / **`TokenRefreshResponse`**
- Handle token refresh operations
- Contain user ID and OAuth credentials

## Infrastructure Layer (Framework Integration)

The infrastructure layer implements domain interfaces using Laravel-specific technologies.

### Repositories

**`VoxDev\Core\Infrastructure\Repositories\SessionUserRepository`**
- Implements `UserRepositoryInterface` using Laravel sessions
- Stores user data in `vauth_user` session key

**`VoxDev\Core\Infrastructure\Repositories\CookieTokenRepository`**
- Implements `TokenRepositoryInterface` using secure HTTP cookies
- Handles token expiration and secure cookie settings

### Services

**`VoxDev\Core\Infrastructure\Services\HttpOAuthService`**
- Implements `OAuthServiceInterface` using Laravel HTTP client
- Handles PKCE flow
- Manages OAuth server communication

### Guards

**`VoxDev\Core\Infrastructure\Guards\CleanArchitectureGuard`**
- Laravel auth guard implementation
- Uses domain repositories for user management
- Adapts domain entities to Laravel's Authenticatable interface

### Auth Adapter

**`VoxDev\Core\Infrastructure\Auth\AuthenticatableUser`**
- Adapts domain `User` entity to Laravel's `Authenticatable` interface
- Provides Laravel-compatible user methods
- Maintains reference to domain user

```php
// Access domain user through Laravel auth
$laravelUser = auth()->user(); // AuthenticatableUser
$domainUser = $laravelUser->getDomainUser(); // Domain\Entities\User

// Direct access to domain properties
$email = $domainUser->getEmail()->getValue();
$attributes = $domainUser->getAttributes();
```

## Usage Examples

### Using Clean Architecture Components

```php
// In a controller
use VoxDev\Core\Traits\HasCoreAuth;

class ProfileController extends Controller
{
    use HasCoreAuth;

    public function show()
    {
        // Get Laravel authenticatable user
        $user = $this->getCoreUser(); // AuthenticatableUser

        // Get domain user entity
        $domainUser = $this->getCoreDomainUser(); // Domain\Entities\User

        // Access domain properties
        $email = $domainUser->getEmail()->getValue();
        $customAttribute = $domainUser->getAttribute('role');

        return view('profile.show', compact('user', 'domainUser'));
    }
}
```

### Direct Use Case Execution

```php
// In a service or controller
use VoxDev\Core\Application\UseCases\RefreshUserToken;
use VoxDev\Core\Application\DTOs\TokenRefreshRequest;

class TokenController extends Controller
{
    public function refresh(RefreshUserToken $useCase)
    {
        $request = new TokenRefreshRequest(
            UserId::fromValue(auth()->id()),
            $this->getOAuthCredentials()
        );

        $response = $useCase->execute($request);

        if ($response->isSuccessful()) {
            return response()->json(['token' => $response->getAccessToken()->getToken()]);
        }

        return response()->json(['error' => $response->getErrorMessage()], 400);
    }
}
```

### Repository Usage

```php
// Direct repository usage
use VoxDev\Core\Domain\Repositories\UserRepositoryInterface;
use VoxDev\Core\Domain\ValueObjects\UserId;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getUser(int $id): ?User
    {
        return $this->userRepository->findById(UserId::fromValue($id));
    }
}
```

## Benefits of Clean Architecture

1. **Framework Independence**: Domain logic is not tied to Laravel
2. **Testability**: Each layer can be tested in isolation
3. **Maintainability**: Clear separation of concerns
4. **Extensibility**: Easy to add new features without breaking existing code
5. **Flexibility**: Can swap implementations without affecting business logic

## Testing

The clean architecture enables comprehensive testing at each layer:

```php
// Domain layer tests (no framework dependencies)
class UserTest extends TestCase
{
    public function test_user_creation()
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John'),
            Email::fromValue('john@example.com')
        );

        $this->assertEquals(1, $user->getId()->getValue());
    }
}

// Application layer tests (with mocked dependencies)
class AuthenticateUserTest extends TestCase
{
    public function test_successful_authentication()
    {
        $oAuthService = $this->createMock(OAuthServiceInterface::class);
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $tokenRepo = $this->createMock(TokenRepositoryInterface::class);

        $useCase = new AuthenticateUser($oAuthService, $userRepo, $tokenRepo);
        // ... test implementation
    }
}
```

## Migration from Legacy Code

The clean architecture implementation is designed to coexist with the existing Laravel-specific implementation. You can:

1. **Use both implementations side by side**
2. **Gradually migrate to clean architecture**
3. **Keep existing APIs for backward compatibility**

The enhanced `HasCoreAuth` trait provides access to both layers:

```php
// Legacy access
$user = $this->getCoreUser(); // Laravel Authenticatable

// Clean architecture access
$domainUser = $this->getCoreDomainUser(); // Domain Entity
$attribute = $this->getCoreUserAttribute('role'); // Domain attribute access
```

This implementation maintains the plug-and-play nature of the package while providing a solid foundation for complex business logic and enterprise-grade applications.
