# Core SDK - Laravel Passport OAuth Integration

A powerful, plug-and-play Laravel package for seamless OAuth integration with Laravel Passport servers. Built with **Clean Architecture** principles for maximum maintainability, testability, and flexibility.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/voffice-indonesia/core-sdk.svg?style=flat-square)](https://packagist.org/packages/voffice-indonesia/core-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/voffice-indonesia/core-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/voffice-indonesia/core-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/voffice-indonesia/core-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/voffice-indonesia/core-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/voffice-indonesia/core-sdk.svg?style=flat-square)](https://packagist.org/packages/voffice-indonesia/core-sdk)

## ✨ Key Features

### 🏗️ **Clean Architecture Implementation**
- **Domain-Driven Design**: Framework-independent business logic
- **Dependency Inversion**: Interfaces in domain, implementations in infrastructure
- **Testable Code**: Full unit test coverage with 33+ tests
- **SOLID Principles**: Maintainable and extensible codebase

### 🚀 **Plug & Play Installation**
- **Zero Configuration**: Works out of the box with intelligent defaults
- **Auto-Registration**: Guards, middleware, routes, and components
- **Smart Detection**: Automatically configures Filament, Livewire, and Laravel features
- **Environment-Based Control**: Fine-grained control via environment variables

### 🔐 **Enterprise-Grade Security**
- **OAuth2 + PKCE**: Industry-standard authorization code flow with PKCE
- **Automatic Token Refresh**: Background token renewal before expiration
- **Secure Cookie Storage**: HttpOnly, Secure, SameSite cookie configuration
- **Session Management**: Optimized session handling for OAuth flows

### 🎨 **Modern UI Components**
- **Livewire 3.0**: Reactive, server-side rendered components
- **Tailwind CSS**: Beautiful, responsive design out of the box
- **Customizable Views**: Publish and modify all templates
- **Progressive Enhancement**: Works with or without JavaScript

### ⚡ **Laravel Integration**
- **Custom Auth Guard**: Seamless multi-auth support
- **Filament Compatible**: Works perfectly with Filament admin panels
- **Middleware Protection**: Easy route protection with automatic token refresh
- **Event System**: Built-in OAuth events with logging

## 📦 Installation

### 1. Install the Package

```bash
composer require voffice-indonesia/core-sdk
```

### 2. Run Setup Command

```bash
php artisan core:setup
```

### 3. Configure Environment

Add your OAuth server details to `.env`:

```env
# OAuth Server Configuration
VAUTH_URL=https://your-oauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/vauth/callback
VAUTH_DOMAIN=your-domain.com
VAUTH_SCOPES=user:read
```

**🎉 That's it!** The SDK automatically registers:

| Feature | Auto-Registered | Description |
|---------|-----------------|-------------|
| **Auth Guard** | ✅ | Custom `core` guard with user provider |
| **Routes** | ✅ | OAuth endpoints (`/vauth/*` and `/oauth/*`) |
| **Middleware** | ✅ | `vauth` middleware and `auth.oauth` group |
| **Livewire Components** | ✅ | 3 ready-to-use authentication components |
| **Event Listeners** | ✅ | OAuth login/logout events with logging |
| **Session Config** | ✅ | Optimized session settings for OAuth |
| **Filament Integration** | ✅ | Automatic Filament configuration (if installed) |
| **Clean Architecture** | ✅ | Domain entities, use cases, and repositories |

## ⚙️ Configuration

The SDK works with zero configuration but provides extensive customization options.

### Environment Variables

Update your `.env` file with your OAuth server details:

```env
# OAuth Server Configuration
VAUTH_URL=https://your-oauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/vauth/callback
VAUTH_DOMAIN=your-domain.com
VAUTH_SCOPES=user:read

# Auto-Registration Control (all default to true)
VAUTH_AUTO_REGISTER_GUARD=true           # Auto-register auth guard
VAUTH_AUTO_REGISTER_MIDDLEWARE=true      # Auto-register middleware
VAUTH_AUTO_REGISTER_ROUTES=true          # Auto-register OAuth routes
VAUTH_AUTO_REGISTER_LIVEWIRE=true        # Auto-register Livewire components
VAUTH_AUTO_REGISTER_EVENTS=true          # Auto-register OAuth events
VAUTH_AUTO_CONFIGURE_FILAMENT=true       # Auto-configure Filament (if installed)
VAUTH_AUTO_CONFIGURE_SESSION=true        # Auto-configure session settings

# Security Settings
VAUTH_COOKIE_SECURE=true                 # Use secure cookies in production
VAUTH_COOKIE_SAME_SITE=lax               # Cookie SameSite policy
VAUTH_SESSION_LIFETIME=720               # Session lifetime in minutes (12 hours)
```

### Advanced Configuration

Publish the configuration file for advanced customization:

```bash
php artisan vendor:publish --tag=core-sdk-config
```

This creates `config/core.php` with options for:

- **Route Protection Patterns**: Automatically protect routes by pattern
- **Cookie Settings**: Fine-tune cookie security and expiration
- **Guard Configuration**: Customize auth guard behavior
- **Event Configuration**: Control OAuth event handling
- **Filament Integration**: Advanced Filament panel configuration

```php
// config/core.php
return [
    // Automatically protect these route patterns
    'protected_route_patterns' => [
        'admin/*',
        'dashboard/*',
        'profile/*',
    ],

    // Exclude these patterns from automatic protection
    'exclude_route_patterns' => [
        'auth/*',
        'login',
        'register',
        'vauth/*',
    ],

    // Clean Architecture feature flags
    'enable_clean_architecture' => true,
    'use_domain_entities' => true,
    'enable_use_cases' => true,
];
```

## 🚀 Usage

### 1. Basic Route Protection

Use the `vauth` middleware to protect your routes:

```php
// In your routes/web.php
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});

// Or use the pre-configured middleware group
Route::middleware(['auth.oauth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Individual route protection
Route::get('/settings', [SettingsController::class, 'index'])->middleware('vauth');
```

### 2. Accessing User Data

The SDK provides multiple ways to access authenticated user data:

#### Traditional Laravel Way

```php
// Using the core guard
$user = Auth::guard('core')->user();

// In controllers/middleware
$user = session('vauth_user'); // Raw user data array

// Check authentication
if (Auth::guard('core')->check()) {
    // User is authenticated
}
```

#### Clean Architecture Way (Recommended)

```php
use VoxDev\Core\Traits\HasCoreAuth;

class ProfileController extends Controller
{
    use HasCoreAuth;

    public function show()
    {
        // Get Laravel authenticatable user
        $user = $this->getCoreUser(); // AuthenticatableUser instance

        // Get domain user entity (Clean Architecture)
        $domainUser = $this->getCoreDomainUser(); // Domain\Entities\User

        // Access typed properties
        $userId = $domainUser->getId(); // UserId value object
        $email = $domainUser->getEmail(); // Email value object
        $name = $domainUser->getName(); // UserName value object

        // Access custom attributes
        $role = $domainUser->getAttribute('role');
        $permissions = $domainUser->getAttribute('permissions', []);

        return view('profile.show', compact('user', 'domainUser'));
    }
}
```

### 3. Advanced Use Cases

#### Direct Use Case Execution

```php
use VoxDev\Core\Application\UseCases\RefreshUserToken;
use VoxDev\Core\Application\DTOs\TokenRefreshRequest;
use VoxDev\Core\Domain\ValueObjects\UserId;

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
            return response()->json([
                'access_token' => $response->getAccessToken()->getToken(),
                'expires_in' => $response->getAccessToken()->getExpiresAt()
            ]);
        }

        return response()->json(['error' => $response->getErrorMessage()], 400);
    }
}
```

#### Repository Pattern Usage

```php
use VoxDev\Core\Domain\Repositories\UserRepositoryInterface;
use VoxDev\Core\Domain\ValueObjects\UserId;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getUserProfile(int $userId): ?User
    {
        $userId = UserId::fromValue($userId);
        return $this->userRepository->findById($userId);
    }

    public function updateUserAttributes(int $userId, array $attributes): void
    {
        $userId = UserId::fromValue($userId);
        $user = $this->userRepository->findById($userId);

        if ($user) {
            $updatedUser = $user->withAttributes($attributes);
            $this->userRepository->save($updatedUser);
        }
    }
}
```

### 4. Filament Integration

For Filament admin panels, the SDK automatically configures everything:

```php
// In your Filament Panel Provider
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('/admin')
        ->authGuard('core') // SDK's auth guard
        ->login() // Redirects to OAuth if not authenticated
        ->userMenuItems([
            'profile' => MenuItem::make()->url('/profile'),
            'logout' => MenuItem::make()->url('/vauth/logout'),
        ])
        // ... other panel configuration
}
```

**Automatic Configuration**: If Filament is detected, the SDK automatically:
- Sets the auth guard to `core`
- Disables default login forms
- Configures user provider
- Sets up logout handling

### 5. Available Routes

The package automatically registers these routes:

#### Core OAuth Routes
```
GET  /vauth/redirect     # Initiates OAuth flow
GET  /vauth/callback     # Handles OAuth callback
POST /vauth/logout       # Logs out user and clears tokens
```

#### Livewire-Powered UI Routes (Optional)
```
GET  /oauth/login        # Login page with Livewire component
GET  /oauth/callback-ui  # Callback processing page
GET  /oauth/dashboard    # Sample protected dashboard
```

### 6. Livewire Components

The package includes three production-ready Livewire components:

#### Authentication Components

```blade
{{-- Login/redirect component with loading states --}}
<livewire:core-auth-redirect />

{{-- OAuth callback processing component --}}
<livewire:core-auth-callback />

{{-- User status/menu component with dropdown --}}
<livewire:core-auth-status />
```

#### Component Features

| Component | Features | Use Case |
|-----------|----------|----------|
| **AuthRedirect** | Loading states, error handling, auto-redirect | Login pages |
| **AuthCallback** | Processing states, success/error feedback | OAuth callbacks |
| **AuthStatus** | User menu, avatar, logout, responsive design | Navigation bars |

#### Custom Component Usage

```blade
{{-- In your layout file --}}
@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <div class="container mx-auto px-4">
                <livewire:core-auth-status />
            </div>
        </nav>

        <main class="container mx-auto px-4 py-8">
            @if(Auth::guard('core')->check())
                @yield('protected-content')
            @else
                <livewire:core-auth-redirect />
            @endif
        </main>
    </div>
@endsection
```

## 🎨 Customization

### Publishing Assets

You can publish and customize views, components, and configuration:

```bash
# Publish everything
php artisan vendor:publish --provider="VoxDev\Core\CoreServiceProvider"

# Publish specific assets
php artisan vendor:publish --tag=core-sdk-views        # All Blade views
php artisan vendor:publish --tag=core-sdk-pages        # Page templates only
php artisan vendor:publish --tag=core-sdk-livewire     # Livewire components
php artisan vendor:publish --tag=core-sdk-config       # Configuration file
```

### Published File Locations

After publishing, files will be available at:

```
resources/
├── views/
│   ├── vendor/core/           # All package views
│   ├── auth/
│   │   ├── oauth-login.blade.php
│   │   └── oauth-callback.blade.php
│   └── oauth-dashboard.blade.php
│
config/
└── core.php                   # Configuration file

app/
└── Livewire/
    └── Core/                  # Customizable Livewire components
        ├── AuthRedirect.php
        ├── AuthCallback.php
        └── AuthStatus.php
```

### Customizing Views

After publishing views, you can customize the authentication flow:

```blade
{{-- resources/views/auth/oauth-login.blade.php --}}
@extends('layouts.app')

@section('title', 'Sign In to ' . config('app.name'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
                <p class="text-gray-600 mt-2">Sign in to access your account</p>
            </div>

            {{-- Custom styling for the auth component --}}
            <livewire:core-auth-redirect />
        </div>
    </div>
</div>
@endsection
```

### Customizing Livewire Components

After publishing Livewire components, you can modify their behavior:

```php
// app/Livewire/Core/AuthRedirect.php
<?php

namespace App\Livewire\Core;

use VoxDev\Core\Livewire\AuthRedirect as BaseAuthRedirect;

class AuthRedirect extends BaseAuthRedirect
{
    public string $customMessage = 'Sign in with your company account';

    public function redirectToOAuth()
    {
        // Add custom logic before redirect
        $this->logAuthAttempt();

        // Call parent method
        parent::redirectToOAuth();
    }

    private function logAuthAttempt(): void
    {
        logger()->info('User initiated OAuth login', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function render()
    {
        return view('livewire.core.auth-redirect', [
            'customMessage' => $this->customMessage,
        ]);
    }
}
```

### Custom Styling

The package uses Tailwind CSS classes. You can override styles by:

1. **Using your own CSS classes** in published views
2. **Adding custom CSS** to override specific components
3. **Modifying Livewire component templates** after publishing

```css
/* In your app.css */
.oauth-login-card {
    @apply bg-white rounded-xl shadow-2xl p-8 max-w-md mx-auto;
}

.oauth-button {
    @apply w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors;
}

.oauth-loading {
    @apply flex items-center justify-center space-x-2 opacity-75;
}
```

## 🧪 Testing

The SDK includes comprehensive test coverage:

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run static analysis
composer analyse

# Fix code style
composer format
```

### Test Coverage

- **33+ Tests**: Comprehensive test suite
- **84+ Assertions**: Thorough validation
- **Domain Layer**: Full unit test coverage for entities and value objects
- **Integration Tests**: Auto-registration and Livewire component tests
- **Architecture Tests**: Code quality and structure validation

### Writing Tests with Clean Architecture

```php
// Unit testing domain entities
use VoxDev\Core\Domain\Entities\User;
use VoxDev\Core\Domain\ValueObjects\{UserId, UserName, Email};

class UserTest extends TestCase
{
    #[Test]
    public function it_creates_user_with_valid_data(): void
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com')
        );

        $this->assertEquals(1, $user->getId()->getValue());
        $this->assertEquals('john@example.com', $user->getEmail()->getValue());
    }
}
```

## 🛠️ Troubleshooting

### Common Issues

1. **"Invalid redirect URI"** - Ensure `VAUTH_REDIRECT_URI` matches exactly what's configured in your OAuth server
2. **"Token refresh failed"** - Check that your OAuth server supports refresh tokens and verify client credentials
3. **"User info API failed"** - Ensure your OAuth server has a `/api/user` endpoint that returns user data

### Debug Mode

Enable debug logging by adding to your `.env`:

```env
LOG_LEVEL=debug
```

Check `storage/logs/laravel.log` for detailed OAuth flow information.

## 📚 Documentation

- 📖 **[Clean Architecture Guide](CLEAN_ARCHITECTURE.md)** - Comprehensive guide to using the clean architecture features
- 📖 **[Livewire Integration Guide](LIVEWIRE_GUIDE.md)** - Comprehensive guide for using and customizing Livewire components
- 📊 **[Implementation Status](IMPLEMENTATION_STATUS.md)** - Detailed feature implementation tracking
- 🔧 **Configuration Reference** - All available configuration options in `config/core.php`
- 🛠️ **API Reference** - Helper methods in `VAuthHelper` class

## 📋 Requirements

- **PHP**: 8.4+
- **Laravel**: 10.0+ || 11.0+ || 12.0+
- **Livewire**: 3.0+ (automatically installed)
- **OAuth Server**: Laravel Passport-compatible OAuth2 server

### Recommended Packages

The SDK works great with:
- **Filament**: Admin panels with automatic configuration
- **Spatie Laravel Permission**: Role and permission management
- **Laravel Sanctum**: API token authentication alongside OAuth
- **Laravel Telescope**: Request debugging and monitoring

## 🔧 OAuth Server Requirements

Your OAuth server should provide:

### 1. OAuth Client Configuration
Create an OAuth client with:
- **Grant Type**: Authorization Code with PKCE
- **Redirect URI**: Your application's callback URL
- **Scopes**: At minimum `user:read` for user info

### 2. Required API Endpoints

| Endpoint | Method | Purpose | Example |
|----------|---------|---------|---------|
| `/oauth/authorize` | GET | Authorization endpoint | `GET /oauth/authorize?client_id=...` |
| `/oauth/token` | POST | Token exchange | `POST /oauth/token` with auth code |
| `/api/user` | GET | User info endpoint | `GET /api/user` with Bearer token |

### 3. User API Response Format

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar": "https://example.com/avatar.jpg",
    "role": "admin",
    "permissions": ["read", "write", "delete"],
    "department": "Engineering",
    "created_at": "2023-01-01T00:00:00Z"
}
```

**Note**: Only `id`, `name`, and `email` are required. Additional fields are stored as user attributes.

### 4. Server Configuration Example

For Laravel Passport servers, ensure:

```php
// In your OAuth server's AuthServiceProvider
Passport::routes();

// OAuth client setup
php artisan passport:client --password=false --redirect_uri=https://your-app.com/vauth/callback

// API route protection
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
```

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### Development Setup

```bash
# Clone the repository
git clone https://github.com/voffice-indonesia/core-sdk.git
cd core-sdk

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer format

# Run static analysis
composer analyse
```

### Code Standards

- **PSR-12**: Follow PSR-12 coding standards
- **PHPStan Level 5**: All code must pass static analysis
- **Clean Architecture**: New features should follow clean architecture principles
- **Test Coverage**: All new features must include tests

### Submitting Changes

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes following our coding standards
4. Add tests for new functionality
5. Ensure all tests pass (`composer test`)
6. Run static analysis (`composer analyse`)
7. Fix code style (`composer format`)
8. Commit your changes (`git commit -am 'Add amazing feature'`)
9. Push to the branch (`git push origin feature/amazing-feature`)
10. Open a Pull Request

### Reporting Issues

Please use the [GitHub Issues](https://github.com/voffice-indonesia/core-sdk/issues) page to report bugs or request features. Include:

- Laravel version
- PHP version
- Steps to reproduce
- Expected vs actual behavior
- Any error messages

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👥 Credits

- **[zydnrbrn](https://github.com/zydnrbrn)** - Creator and maintainer
- **[All Contributors](../../contributors)** - Thank you for your contributions!

## 🌟 Support

If this package helped you, please consider:

- ⭐ **Starring** the repository
- 🐛 **Reporting** any bugs you find
- 💡 **Suggesting** new features
- 📖 **Improving** documentation
- 🤝 **Contributing** code

---

<div align="center">
  <p><strong>Built with ❤️ for the Laravel community</strong></p>
  <p>
    <a href="https://github.com/voffice-indonesia/core-sdk">GitHub</a> •
    <a href="https://packagist.org/packages/voffice-indonesia/core-sdk">Packagist</a> •
    <a href="https://github.com/voffice-indonesia/core-sdk/issues">Issues</a> •
    <a href="CONTRIBUTING.md">Contributing</a>
  </p>
</div>
