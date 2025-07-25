# Package Structure

Understanding the organization and structure of the Core SDK package.

## 📁 Directory Structure

```
core-sdk/
├── src/                           # Source code
│   ├── Application/               # Application layer (Clean Architecture)
│   │   ├── DTOs/                 # Data Transfer Objects
│   │   └── UseCases/             # Business use cases
│   ├── Auth/                     # Authentication components
│   │   ├── CoreAuthGuard.php     # Custom auth guard
│   │   ├── CoreAuthUser.php      # User model
│   │   └── CoreAuthUserProvider.php # User provider
│   ├── Commands/                 # Artisan commands
│   │   └── CoreSetupCommand.php  # Setup command
│   ├── Controllers/              # HTTP controllers
│   │   └── Auth/                 # Authentication controllers
│   ├── Domain/                   # Domain layer (Clean Architecture)
│   │   ├── Entities/             # Domain entities
│   │   ├── Repositories/         # Repository interfaces
│   │   ├── Services/             # Service interfaces
│   │   └── ValueObjects/         # Value objects
│   ├── Events/                   # Laravel events
│   ├── Facades/                  # Laravel facades
│   ├── Helpers/                  # Helper classes
│   ├── Infrastructure/           # Infrastructure layer
│   │   ├── Auth/                 # Auth implementations
│   │   ├── Controllers/          # Infrastructure controllers
│   │   ├── Guards/               # Guard implementations
│   │   ├── Providers/            # Service providers
│   │   ├── Repositories/         # Repository implementations
│   │   └── Services/             # Service implementations
│   ├── Livewire/                 # Livewire components
│   ├── Middleware/               # HTTP middleware
│   ├── Traits/                   # Reusable traits
│   ├── Core.php                  # Main package class
│   └── CoreServiceProvider.php   # Main service provider
├── config/                       # Configuration files
│   └── core.php                  # Package configuration
├── database/                     # Database files
│   ├── factories/                # Model factories
│   └── migrations/               # Database migrations
├── docs/                         # Documentation
│   ├── usage/                    # Usage guides
│   ├── architecture/             # Architecture docs
│   ├── examples/                 # Code examples
│   └── api/                      # API reference
├── resources/                    # Laravel resources
│   └── views/                    # Blade templates
├── routes/                       # Route definitions
│   └── web.php                   # Web routes
├── tests/                        # Test suite
│   ├── Feature/                  # Feature tests
│   └── Unit/                     # Unit tests
├── composer.json                 # Package definition
├── README.md                     # Main documentation
└── CHANGELOG.md                  # Version history
```

## 🏗️ Architecture Layers

The package follows **Clean Architecture** principles with clear separation of concerns:

### 1. Domain Layer (`src/Domain/`)

The innermost layer containing business logic and rules:

- **Entities**: Core business objects
- **Value Objects**: Immutable objects representing domain concepts
- **Repository Interfaces**: Contracts for data access
- **Service Interfaces**: Contracts for business services

```php
src/Domain/
├── Entities/
│   ├── User.php                  # User domain entity
│   └── Token.php                 # Token domain entity
├── Repositories/
│   ├── UserRepositoryInterface.php
│   └── TokenRepositoryInterface.php
├── Services/
│   ├── OAuthServiceInterface.php
│   └── VAuthServiceInterface.php
└── ValueObjects/
    ├── UserId.php
    ├── Email.php
    └── Token.php
```

### 2. Application Layer (`src/Application/`)

Contains application-specific business logic:

- **Use Cases**: Application business rules
- **DTOs**: Data transfer objects for boundaries

```php
src/Application/
├── DTOs/
│   ├── UserDTO.php
│   └── TokenDTO.php
└── UseCases/
    ├── AuthenticateUser.php
    └── RefreshUserToken.php
```

### 3. Infrastructure Layer (`src/Infrastructure/`)

Framework-specific implementations:

- **Repositories**: Data access implementations
- **Services**: External service implementations
- **Guards**: Laravel auth guard implementations

```php
src/Infrastructure/
├── Auth/
├── Controllers/
├── Guards/
│   └── CleanArchitectureGuard.php
├── Providers/
│   └── CleanArchitectureServiceProvider.php
├── Repositories/
│   ├── SessionUserRepository.php
│   └── CookieTokenRepository.php
└── Services/
    ├── HttpOAuthService.php
    └── VAuthService.php
```

### 4. Presentation Layer (`src/Controllers/`, `src/Livewire/`)

User interface and API endpoints:

- **Controllers**: HTTP request handlers
- **Livewire Components**: Reactive UI components
- **Views**: Blade templates

## 🔧 Key Components

### Service Providers

#### CoreServiceProvider
Main service provider that handles:
- Package configuration
- Auto-registration of components
- Publishing of assets

#### CleanArchitectureServiceProvider
Registers clean architecture bindings:
- Repository implementations
- Service implementations
- Use case bindings

### Authentication Components

#### CoreAuthGuard
Custom Laravel auth guard that:
- Handles OAuth token validation
- Manages user sessions
- Integrates with clean architecture

#### CoreAuthUser
User model for OAuth authentication:
- Implements Authenticatable interface
- Provides user data from OAuth server
- Handles user attributes

### Middleware

#### VAuthMiddleware
Main authentication middleware:
- Validates OAuth tokens
- Handles token refresh
- Redirects unauthenticated users

### Services

#### VAuthService
Main service for API integration:
- OAuth flow management
- API request handling
- Token management

### Helpers

#### VAuthHelper
Utility functions for:
- Token validation
- URL generation
- Configuration access

## 📦 Package Configuration

### Configuration Files

#### `config/core.php`
Main configuration file containing:
- OAuth server settings
- Feature toggles
- Security settings
- Route configuration

### Environment Variables

Core environment variables:
```env
VAUTH_URL=              # OAuth server URL
VAUTH_CLIENT_ID=        # OAuth client ID
VAUTH_CLIENT_SECRET=    # OAuth client secret
VAUTH_REDIRECT_URI=     # Callback URL
```

## 🔌 Extension Points

### Custom Guards

Extend authentication by creating custom guards:

```php
// Register in service provider
Auth::extend('custom-oauth', function ($app, $name, $config) {
    return new CustomOAuthGuard(/* dependencies */);
});
```

### Custom Services

Implement service interfaces for custom behavior:

```php
class CustomVAuthService implements VAuthServiceInterface
{
    // Custom implementation
}

// Bind in service provider
$this->app->bind(VAuthServiceInterface::class, CustomVAuthService::class);
```

### Custom Repositories

Implement repository interfaces for different storage:

```php
class DatabaseTokenRepository implements TokenRepositoryInterface
{
    // Database implementation
}
```

## 🧪 Testing Structure

### Test Organization

```php
tests/
├── Feature/                      # Integration tests
│   ├── AuthenticationTest.php    # Auth flow tests
│   └── ApiIntegrationTest.php    # API tests
├── Unit/                         # Unit tests
│   ├── Services/                 # Service tests
│   ├── Guards/                   # Guard tests
│   └── Helpers/                  # Helper tests
├── TestCase.php                  # Base test case
└── Pest.php                      # Pest configuration
```

### Test Categories

1. **Feature Tests**: End-to-end testing of OAuth flows
2. **Unit Tests**: Individual component testing
3. **Architecture Tests**: Structure validation using Pest
4. **Integration Tests**: Component interaction testing

## 📋 Code Standards

### PSR Standards

The package follows:
- **PSR-4**: Autoloading standard
- **PSR-12**: Coding style standard
- **PSR-3**: Logger interface standard

### Static Analysis

Quality tools used:
- **PHPStan**: Static analysis
- **Laravel Pint**: Code formatting
- **Pest**: Testing framework

### Documentation Standards

- **PHPDoc**: All public methods documented
- **Markdown**: Documentation in docs/ folder
- **Examples**: Code examples for all features

## 🔄 Dependency Flow

```
Presentation Layer
       ↓ (depends on)
Application Layer
       ↓ (depends on)
Domain Layer
       ↑ (implemented by)
Infrastructure Layer
```

### Key Principles

1. **Dependency Inversion**: Inner layers define interfaces, outer layers implement
2. **Single Responsibility**: Each class has one reason to change
3. **Open/Closed**: Open for extension, closed for modification
4. **Interface Segregation**: Many specific interfaces vs. one general interface
5. **Liskov Substitution**: Objects are replaceable with instances of subtypes

## 🚀 Package Lifecycle

### Bootstrap Process

1. **Service Provider Registration**: Laravel registers CoreServiceProvider
2. **Configuration Merge**: Package config merged with app config
3. **Auto-Registration**: Guards, middleware, routes registered automatically
4. **Clean Architecture Setup**: Domain/Infrastructure bindings established
5. **Feature Detection**: Livewire, Filament integration if available

### Request Lifecycle

1. **Route Matching**: Laravel matches incoming request
2. **Middleware Stack**: VAuth middleware validates authentication
3. **Guard Resolution**: CoreAuthGuard handles user authentication
4. **Service Resolution**: VAuthService handles API calls
5. **Response**: Controller returns response

## 🔗 Related Documentation

- [Clean Architecture Guide](clean-architecture.md) - Detailed architecture explanation
- [Extending the Package](extending.md) - How to extend and customize
- [API Reference](../api/) - Complete API documentation
- [Configuration](../configuration.md) - Configuration options
