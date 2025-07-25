# Troubleshooting Guide

Common issues and solutions when using the Core SDK package.

## 🚨 Common Issues

### 1. Installation Issues

#### Package not found
```bash
composer require voffice-indonesia/core-sdk
# Error: Could not find package
```

**Solution:**
```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Try again
composer require voffice-indonesia/core-sdk
```

#### Version conflicts
```
Problem: Package requires php ^8.2
```

**Solution:**
- Ensure PHP 8.2+ is installed
- Check Laravel version compatibility (10.x, 11.x, 12.x)
- Update dependencies: `composer update`

### 2. Configuration Issues

#### Environment variables not working
```
VAUTH_URL not recognized
```

**Solution:**
```bash
# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Restart server
php artisan serve
```

#### OAuth server connection failed
```
Connection refused to OAuth server
```

**Solution:**
1. Verify `VAUTH_URL` is correct and accessible
2. Check network connectivity
3. Verify SSL certificates (if using HTTPS)
4. Test with curl: `curl -I https://your-oauth-server.com`

### 3. Authentication Issues

#### Users redirected in loops
```
Infinite redirect between /dashboard and /auth/oauth/redirect
```

**Solution:**
```php
// Check middleware order
Route::middleware(['web', 'vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Ensure auth routes are excluded
Route::get('/auth/oauth/redirect', [CoreController::class, 'redirect'])
    ->withoutMiddleware('vauth'); // Don't protect auth routes
```

#### "Invalid state" errors
```
OAuth callback error: Invalid state parameter
```

**Solution:**
1. Ensure session middleware is applied
2. Check session driver configuration
3. Verify cookies are enabled in browser
4. Clear browser cookies and try again

```env
# Use database/redis for sessions
SESSION_DRIVER=database
# or
SESSION_DRIVER=redis
```

#### Tokens not refreshing
```
Token expired and not refreshing automatically
```

**Solution:**
```env
# Check token refresh settings
VAUTH_TOKEN_REFRESH_THRESHOLD=300  # 5 minutes before expiry

# Enable debug logging
LOG_LEVEL=debug
VAUTH_DEBUG_MODE=true
```

### 4. Middleware Issues

#### Routes not protected
```
Can access /dashboard without authentication
```

**Solution:**
```php
// Verify middleware is applied
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Check if auto-registration is enabled
// In .env
CORE_AUTO_REGISTER_MIDDLEWARE=true

// Or register manually in Kernel.php
protected $routeMiddleware = [
    'vauth' => \VoxDev\Core\Middleware\VAuthMiddleware::class,
];
```

#### API routes returning HTML
```
API routes returning login page instead of JSON
```

**Solution:**
```php
// Use API-specific middleware
Route::middleware(['api', 'vauth'])->group(function () {
    Route::apiResource('users', UserController::class);
});

// Or handle JSON responses in middleware
if ($request->wantsJson() || $request->is('api/*')) {
    return response()->json(['error' => 'Unauthenticated'], 401);
}
```

### 5. VAuth Service Issues

#### Empty responses from API
```php
$users = VAuth::getUsers(); // Returns empty array
```

**Solutions:**
1. **Check token validity:**
```php
if (!VAuth::hasValidToken()) {
    // Token is invalid, user needs to re-authenticate
    return redirect()->route('core.auth.redirect');
}
```

2. **Check OAuth server API:**
```bash
# Test API directly
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-oauth-server.com/api/users
```

3. **Enable API logging:**
```env
CORE_LOG_API_CALLS=true
LOG_LEVEL=debug
```

4. **Check OAuth server permissions:**
- Verify client has correct scopes
- Check API endpoint permissions
- Verify user has access to requested resources

#### Connection timeouts
```
API requests timing out
```

**Solution:**
```php
// Increase timeout in config/core.php
'api' => [
    'timeout' => 30, // seconds
    'retry_attempts' => 3,
],
```

### 6. Livewire Component Issues

#### Components not rendering
```
Livewire component not found: core-auth-status
```

**Solution:**
```bash
# Publish Livewire components
php artisan vendor:publish --tag=core-sdk-livewire

# Clear Livewire cache
php artisan livewire:discover
```

#### JavaScript errors
```
Livewire: Component not found
```

**Solution:**
```bash
# Republish assets
php artisan vendor:publish --tag=core-sdk-views --force

# Clear all caches
php artisan optimize:clear
```

### 7. Filament Integration Issues

#### Filament login not working
```
Filament admin panel shows default login
```

**Solution:**
```env
# Enable Filament auto-configuration
CORE_AUTO_CONFIGURE_FILAMENT=true
VAUTH_GUARD_NAME=admin
```

```php
// In Filament PanelProvider
public function panel(Panel $panel): Panel
{
    return $panel
        ->authGuard('core') // Use core guard
        ->login(false) // Disable default login
        ->middleware(['vauth']); // Use OAuth middleware
}
```

### 8. Testing Issues

#### Tests failing with authentication
```
Tests can't authenticate users
```

**Solution:**
```php
// In your test
use Illuminate\Support\Facades\Auth;

public function test_authenticated_user_can_access_dashboard()
{
    // Mock authentication
    $this->withSession(['auth_user_id' => 1]);

    // Or use actingAs with core guard
    $this->actingAs($user, 'core');

    $response = $this->get('/dashboard');
    $response->assertOk();
}
```

#### VAuth service mocking
```php
// Mock VAuth facade in tests
use VoxDev\Core\Facades\VAuth;

VAuth::shouldReceive('getUsers')
    ->once()
    ->andReturn([
        ['id' => 1, 'name' => 'John Doe'],
    ]);
```

## 🔧 Debug Tools

### Enable Debug Mode

```env
APP_DEBUG=true
LOG_LEVEL=debug
VAUTH_DEBUG_MODE=true
CORE_LOG_AUTH_EVENTS=true
CORE_LOG_API_CALLS=true
```

### Check Configuration

```bash
# Validate configuration
php artisan core:config:check

# Test OAuth connection
php artisan core:test:connection

# Show current configuration
php artisan config:show core
```

### View Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Specific OAuth logs
grep "VAuth" storage/logs/laravel.log

# API call logs
grep "API" storage/logs/laravel.log
```

### Test Routes

```bash
# List all routes
php artisan route:list | grep vauth

# Test OAuth redirect
curl -I http://your-app.com/auth/oauth/redirect

# Test protected route
curl -H "Accept: application/json" \
     http://your-app.com/dashboard
```

## 🔍 Diagnostic Commands

### Check Package Installation

```bash
# Check if package is installed
composer show voffice-indonesia/core-sdk

# Check autoloading
composer dump-autoload
```

### Verify Environment

```bash
# Check PHP version
php -v

# Check Laravel version
php artisan --version

# Check installed packages
composer show --installed
```

### Test OAuth Flow

```php
// Create test route for debugging
Route::get('/test-oauth', function () {
    return [
        'has_token' => VAuth::hasValidToken(),
        'auth_status' => VAuth::getAuthStatus(),
        'user' => Auth::guard('core')->user(),
        'config' => [
            'url' => config('core.url'),
            'client_id' => config('core.client_id'),
            'redirect_uri' => config('core.redirect_uri'),
        ]
    ];
});
```

## 🚑 Emergency Fixes

### Clear All Caches

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload
```

### Reset Configuration

```bash
# Republish config
php artisan vendor:publish --tag=core-sdk-config --force

# Reset to defaults
php artisan core:reset --force
```

### Disable Auto-Registration

If auto-registration is causing issues:

```env
CORE_AUTO_REGISTER_GUARD=false
CORE_AUTO_REGISTER_MIDDLEWARE=false
CORE_AUTO_REGISTER_ROUTES=false
CORE_AUTO_REGISTER_LIVEWIRE=false
```

Then manually register components as needed.

## 📞 Getting Help

### Before Asking for Help

1. **Check this troubleshooting guide**
2. **Enable debug mode and check logs**
3. **Test with minimal configuration**
4. **Verify OAuth server is working**
5. **Check package version compatibility**

### Information to Include

When reporting issues, please include:

- Laravel version
- PHP version
- Package version
- Environment (local/staging/production)
- Relevant configuration
- Error messages and stack traces
- Steps to reproduce

### Where to Get Help

- [GitHub Issues](https://github.com/voffice-indonesia/core-sdk/issues) - Bug reports and feature requests
- [GitHub Discussions](https://github.com/voffice-indonesia/core-sdk/discussions) - Questions and community help
- [Documentation](../README.md) - Complete package documentation

## 🔧 Manual Configuration

If auto-configuration fails, you can configure manually:

### Manual Guard Registration

```php
// In AppServiceProvider::boot()
use Illuminate\Support\Facades\Auth;
use VoxDev\Core\Auth\CoreAuthGuard;
use VoxDev\Core\Auth\CoreAuthUserProvider;

Auth::extend('core', function ($app) {
    return new CoreAuthGuard(
        new CoreAuthUserProvider,
        $app['session.store']
    );
});

Auth::provider('core', function ($app, array $config) {
    return new CoreAuthUserProvider;
});
```

### Manual Middleware Registration

```php
// In app/Http/Kernel.php
protected $routeMiddleware = [
    'vauth' => \VoxDev\Core\Middleware\VAuthMiddleware::class,
];
```

### Manual Route Registration

```php
// In routes/web.php
use VoxDev\Core\Controllers\Auth\CoreController;
use VoxDev\Core\Controllers\Auth\CallbackController;

Route::middleware(['web'])->group(function () {
    Route::get('/auth/oauth/redirect', [CoreController::class, 'redirect'])
        ->name('core.auth.redirect');

    Route::get('/auth/oauth/callback', [CallbackController::class, 'callback'])
        ->name('core.auth.callback');

    Route::post('/auth/oauth/logout', [CoreController::class, 'logout'])
        ->name('core.auth.logout');
});
```
