# Core SDK - Laravel Passport OAuth Integration

A plug-and-play Laravel package for seamless OAuth integration with Laravel Passport servers. Install, configure, and you're ready to go!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/voffice-indonesia/core-sdk.svg?style=flat-square)](https://packagist.org/packages/voffice-indonesia/core-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/voffice-indonesia/core-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/voffice-indonesia/core-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/voffice-indonesia/core-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/voffice-indonesia/core-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/voffice-indonesia/core-sdk.svg?style=flat-square)](https://packagist.org/packages/voffice-indonesia/core-sdk)

## Features

- 🚀 **Plug & Play**: Simple installation and configuration
- 🔐 **Secure Authentication**: OAuth2 flow with automatic token refresh
- 🛡️ **Middleware Protection**: Easy route protection
- 🎨 **Filament Ready**: Built-in support for Filament Admin panels
- 🍪 **Cookie Management**: Automatic token storage and refresh
- ⚡ **Laravel Integration**: Custom auth guard and user provider
- 🎭 **Livewire Components**: Pre-built UI components for authentication
- 📦 **Publishable**: Customize views and components to match your design

## Installation

Install the package via Composer:

```bash
composer require voffice-indonesia/core-sdk
```

Run the setup command:

```bash
php artisan core:setup
```

**That's it!** The SDK automatically registers:
- ✅ Custom auth guard (`core`)
- ✅ OAuth routes (`/vauth/*` and `/oauth/*`)
- ✅ Middleware (`vauth` and `auth.oauth` group)
- ✅ Livewire components
- ✅ Event listeners for OAuth flows
- ✅ Session and cookie configuration
- ✅ Filament integration (if Filament is installed)
- ✅ Configuration auto-merge

## Configuration

Update your `.env` file with your OAuth server details:

```env
# OAuth Server Configuration
VAUTH_URL=https://your-oauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/vauth/callback
VAUTH_DOMAIN=your-domain.com
VAUTH_SCOPES=user:read
```

**No additional configuration files needed!** Everything is auto-registered.

### Auto-Registration Features

The SDK includes intelligent auto-registration that can be controlled via environment variables:

```env
# Auto-Registration Control (all default to true)
VAUTH_AUTO_REGISTER_GUARD=true           # Auto-register auth guard
VAUTH_AUTO_REGISTER_MIDDLEWARE=true      # Auto-register middleware
VAUTH_AUTO_REGISTER_ROUTES=true          # Auto-register OAuth routes
VAUTH_AUTO_REGISTER_LIVEWIRE=true        # Auto-register Livewire components
VAUTH_AUTO_REGISTER_EVENTS=true          # Auto-register OAuth events
VAUTH_AUTO_CONFIGURE_FILAMENT=true       # Auto-configure Filament (if installed)
VAUTH_AUTO_CONFIGURE_SESSION=true        # Auto-configure session settings
```

**Automatic Middleware Groups**: Creates `auth.oauth` middleware group combining `web` + `vauth`.

**Automatic Route Protection**: Configure route patterns for automatic OAuth protection:

```php
// In config/core.php after publishing
'protected_route_patterns' => [
    'admin/*',
    'dashboard/*',
    'profile/*',
],
'exclude_route_patterns' => [
    'auth/*',
    'login',
    'register',
    'vauth/*',
],
```

## Usage

### 1. Protecting Routes

Use the `vauth` middleware to protect your routes:

```php
// In your routes/web.php
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});

// Or on individual routes
Route::get('/admin', [AdminController::class, 'index'])->middleware('vauth');
```

### 2. Filament Integration

For Filament panels, update your panel provider:

```php
// In your Filament Panel Provider
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('/admin')
        ->authGuard('core') // Use the core guard
        ->login() // This will redirect to OAuth if not authenticated
        // ... other panel configuration
}
```

### 3. Accessing User Data

Get the authenticated user:

```php
// Using the core guard
$user = Auth::guard('core')->user();

// Or in controllers/middleware
$user = session('vauth_user'); // Raw user data array
```

### 4. Available Routes

The package automatically registers these routes:

- `GET /vauth/redirect` - Redirects to OAuth server
- `GET /vauth/callback` - Handles OAuth callback
- `POST /vauth/logout` - Logs out the user

**Optional Livewire-powered UI routes:**
- `GET /oauth/login` - Login page with Livewire component
- `GET /oauth/callback-ui` - Callback processing page
- `GET /oauth/dashboard` - Sample dashboard (protected route)

### 5. Livewire Components

The package includes ready-to-use Livewire components:

```blade
{{-- Login/redirect component --}}
<livewire:core-auth-redirect />

{{-- Callback processing component --}}
<livewire:core-auth-callback />

{{-- User status/menu component --}}
<livewire:core-auth-status />
```

### 6. Publishing Views and Components

You can publish and customize the views and components:

```bash
# Publish all views
php artisan vendor:publish --tag=core-sdk-views

# Publish page templates only
php artisan vendor:publish --tag=core-sdk-pages

# Publish Livewire components for customization
php artisan vendor:publish --tag=core-sdk-livewire

# Publish configuration
php artisan vendor:publish --tag=core-sdk-config
```

After publishing views, they'll be available in:
- `resources/views/vendor/core/` (all views)
- `resources/views/auth/oauth-*.blade.php` (page templates)
- `app/Livewire/Core/` (Livewire components)

### 7. Customizing the UI

After publishing, you can customize the authentication flow:

```blade
{{-- In your own blade file --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <livewire:core-auth-redirect />
    </div>
@endsection
```

Or create your own components:

```php
// app/Livewire/Core/AuthRedirect.php (after publishing)
class AuthRedirect extends Component
{
    // Customize the component behavior
    public function redirectToOAuth()
    {
        // Your custom logic here
        // ...
    }
}
```

## Troubleshooting

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

## Documentation

- 📖 **[Livewire Integration Guide](LIVEWIRE_GUIDE.md)** - Comprehensive guide for using and customizing Livewire components
- 🔧 **Configuration Reference** - All available configuration options in `config/core.php`
- 🛠️ **API Reference** - Helper methods in `VAuthHelper` class

## Requirements

- PHP 8.4+
- Laravel 10.0+ || 11.0+ || 12.0+
- Livewire 3.0+ (automatically installed)
- A Laravel Passport OAuth2 server

## OAuth Server Requirements

Your OAuth server should have:

1. **OAuth Client**: Create a client with your redirect URI
2. **API Endpoints**:
   - `GET /oauth/authorize` - Authorization endpoint
   - `POST /oauth/token` - Token endpoint
   - `GET /api/user` - User info endpoint (protected)

3. **User API Response**: Should return user data in this format:
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar": "https://example.com/avatar.jpg"
}
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [zydnrbrn](https://github.com/zydnrbrn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
