# Installation Guide

This guide will walk you through installing and setting up the Core SDK in your Laravel application.

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 10.x, 11.x, or 12.x
- **Laravel Passport OAuth Server**: Running and accessible

## 🚀 Step 1: Install the Package

Install the package via Composer:

```bash
composer require voffice-indonesia/core-sdk
```

## ⚙️ Step 2: Run Setup Command

Run the interactive setup command to configure the package:

```bash
php artisan core:setup
```

This command will:
- Publish the configuration file
- Guide you through environment setup
- Configure authentication guards
- Set up middleware aliases
- Validate your configuration

## 🔧 Step 3: Environment Configuration

Add the following to your `.env` file:

```env
# OAuth Server Configuration
VAUTH_URL=https://your-oauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/auth/oauth/callback

# OAuth Scopes (optional)
VAUTH_SCOPES=user:read

# Package Behavior (optional - these are defaults)
CORE_AUTO_REGISTER_GUARD=true
CORE_AUTO_REGISTER_MIDDLEWARE=true
CORE_AUTO_REGISTER_LIVEWIRE=true
CORE_AUTO_REGISTER_ROUTES=true
CORE_AUTO_CONFIGURE_FILAMENT=true

# Routing (optional)
CORE_ROUTE_PREFIX=auth/oauth

# Session & Security (optional)
VAUTH_COOKIE_SAME_SITE=lax
VAUTH_SESSION_LIFETIME=720
```

## 🔑 Step 4: Configure Authentication Guard

### Option A: Automatic Configuration (Recommended)

The package automatically configures a new auth guard. You can start using it immediately:

```php
// In your controllers, middleware, etc.
Auth::guard('core')->user(); // Get authenticated user
```

### Option B: Manual Configuration

If you prefer manual setup, publish and edit the auth config:

```bash
php artisan vendor:publish --tag=config
```

Then add to `config/auth.php`:

```php
'guards' => [
    // ...existing guards...

    'core' => [
        'driver' => 'core',
        'provider' => 'core_users',
    ],
],

'providers' => [
    // ...existing providers...

    'core_users' => [
        'driver' => 'core',
        'model' => VoxDev\Core\Auth\CoreAuthUser::class,
    ],
],
```

## 🛡️ Step 5: Set Up Route Protection

Protect your routes using the `vauth` middleware:

```php
// In routes/web.php
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});

// Or protect individual routes
Route::get('/admin', [AdminController::class, 'index'])->middleware('vauth');
```

## 🎨 Step 6: Set Up UI Components (Optional)

If you want to use the built-in Livewire components:

```bash
# Publish Livewire components
php artisan vendor:publish --tag=core-sdk-livewire

# Publish views for customization
php artisan vendor:publish --tag=core-sdk-views
```

## 🧪 Step 7: Test the Installation

Create a simple test route to verify everything works:

```php
// routes/web.php
Route::get('/test-auth', function () {
    if (Auth::guard('core')->check()) {
        return 'Authenticated as: ' . Auth::guard('core')->user()->name;
    }
    return redirect('/auth/oauth/redirect');
})->middleware('web');
```

Visit `/test-auth` in your browser to test the OAuth flow.

## 📂 Step 8: Optional Customizations

### Publish Configuration for Advanced Setup

```bash
php artisan vendor:publish --tag=core-sdk-config
```

This creates `config/core.php` where you can customize:
- Route protection patterns
- OAuth flow settings
- Cookie and session configuration
- Feature toggles

### Publish Views for UI Customization

```bash
php artisan vendor:publish --tag=core-sdk-views
```

This publishes views to `resources/views/vendor/core/` for customization.

### Publish Individual Pages

```bash
php artisan vendor:publish --tag=core-sdk-pages
```

This publishes specific page templates:
- `resources/views/auth/oauth-login.blade.php`
- `resources/views/auth/oauth-callback.blade.php`
- `resources/views/oauth-dashboard.blade.php`

## 🎯 Next Steps

After installation, check out:

1. [Configuration Guide](configuration.md) - Detailed configuration options
2. [Basic Usage](usage/basic-usage.md) - How to use the package
3. [VAuth Service](usage/vauth-service.md) - API integration examples
4. [Middleware Guide](usage/middleware.md) - Advanced route protection

## 🚨 Troubleshooting

If you encounter issues during installation:

1. **Check Laravel version compatibility**
2. **Ensure OAuth server is accessible**
3. **Verify environment variables**
4. **Check file permissions**

See [Troubleshooting Guide](troubleshooting.md) for common issues and solutions.

## 🔄 Upgrading

When upgrading to a new version:

```bash
composer update voffice-indonesia/core-sdk
php artisan core:setup --force
```

The `--force` flag will update configuration and republish assets if needed.
