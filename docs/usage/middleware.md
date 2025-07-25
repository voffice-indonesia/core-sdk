# Middleware Guide

Learn how to use the Core SDK middleware for protecting routes and handling authentication.

## 🛡️ Available Middleware

The Core SDK provides several middleware for route protection:

### 1. `vauth` - Main Authentication Middleware

Protects routes and ensures users are authenticated via OAuth:

```php
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### 2. `auth.oauth` - Middleware Group

Combines `web` and `vauth` middleware:

```php
Route::middleware(['auth.oauth'])->group(function () {
    Route::resource('users', UserController::class);
});
```

## 🔧 Basic Usage

### Protecting Individual Routes

```php
// Single route protection
Route::get('/profile', [ProfileController::class, 'show'])->middleware('vauth');

// Multiple middleware
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['vauth', 'role:admin']);
```

### Protecting Route Groups

```php
// Protect entire group
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::resource('posts', PostController::class);
});

// Nested groups with additional middleware
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/settings', [SettingsController::class, 'index']);
    });
});
```

### API Route Protection

```php
// routes/api.php
Route::middleware(['api', 'vauth'])->group(function () {
    Route::get('/user', function () {
        return Auth::guard('core')->user();
    });

    Route::apiResource('locations', LocationController::class);
});
```

## ⚙️ Middleware Behavior

### Authentication Flow

1. **Check Token**: Middleware checks for valid OAuth token
2. **Validate Token**: Verifies token with OAuth server if needed
3. **Refresh Token**: Automatically refreshes expired tokens
4. **Redirect**: Redirects to OAuth login if no valid token
5. **Continue**: Allows request to proceed if authenticated

### Automatic Token Refresh

The middleware automatically handles token refresh:

```php
// Token refresh happens transparently
Route::get('/data', function () {
    // This route will work even if token expires during the request
    return VAuth::getUsers();
})->middleware('vauth');
```

## 🎯 Advanced Configuration

### Custom Redirect URLs

Configure where unauthenticated users are redirected:

```php
// In your RouteServiceProvider or middleware
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
})->defaults('auth_redirect', '/custom-login');
```

### Conditional Middleware

```php
// Apply middleware conditionally
Route::get('/data', [DataController::class, 'index'])
    ->middleware(config('app.env') === 'production' ? 'vauth' : '');
```

### Multiple Guards

```php
// Use different guards for different routes
Route::middleware(['auth:core'])->group(function () {
    Route::get('/user-dashboard', [UserController::class, 'dashboard']);
});

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin-dashboard', [AdminController::class, 'dashboard']);
});
```

## 🔄 Automatic Route Protection

### Pattern-Based Protection

Configure automatic protection in `config/core.php`:

```php
'routing' => [
    // Automatically protect these patterns
    'protected_patterns' => [
        'admin/*',
        'dashboard/*',
        'profile/*',
        'settings/*',
        'api/private/*',
    ],

    // Exclude these patterns
    'excluded_patterns' => [
        'auth/*',
        'login',
        'register',
        'public/*',
        'api/public/*',
    ],
],
```

### Environment-Based Protection

```env
# Enable automatic route protection
CORE_AUTO_PROTECT_ROUTES=true

# Protect additional patterns
CORE_PROTECTED_PATTERNS=admin/*,dashboard/*,profile/*

# Exclude patterns
CORE_EXCLUDED_PATTERNS=auth/*,public/*,api/public/*
```

## 🧪 Testing with Middleware

### Feature Tests

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    public function test_vauth_middleware_redirects_unauthenticated_users()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/auth/oauth/redirect');
    }

    public function test_authenticated_user_can_access_protected_route()
    {
        // Mock authentication
        $this->withSession(['auth_user_id' => 1]);

        $response = $this->get('/dashboard');

        $response->assertOk();
    }

    public function test_api_routes_protected()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
```

### Unit Tests

```php
use VoxDev\Core\Middleware\VAuthMiddleware;
use Illuminate\Http\Request;

class VAuthMiddlewareTest extends TestCase
{
    public function test_middleware_allows_authenticated_users()
    {
        $middleware = new VAuthMiddleware();
        $request = Request::create('/dashboard', 'GET');

        // Mock authentication
        Auth::shouldReceive('guard->check')->andReturn(true);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }
}
```

## 🔧 Custom Middleware

### Creating Custom OAuth Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomVAuthMiddleware
{
    public function handle(Request $request, Closure $next, $role = null)
    {
        // Check authentication
        if (!Auth::guard('core')->check()) {
            return redirect()->route('core.auth.redirect');
        }

        // Check role if specified
        if ($role && !$this->hasRole($request, $role)) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }

    private function hasRole(Request $request, string $role): bool
    {
        $user = Auth::guard('core')->user();
        return $user && in_array($role, $user->roles ?? []);
    }
}
```

### Registering Custom Middleware

```php
// In app/Http/Kernel.php
protected $routeMiddleware = [
    // ... existing middleware
    'custom.vauth' => \App\Http\Middleware\CustomVAuthMiddleware::class,
];

// Usage
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('custom.vauth:admin');
```

## 🌐 API-Specific Middleware

### JSON Response Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiVAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('core')->check()) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please authenticate to access this resource',
                'auth_url' => route('core.auth.redirect')
            ], 401);
        }

        return $next($request);
    }
}
```

### CORS with Authentication

```php
Route::middleware(['cors', 'vauth'])->group(function () {
    Route::apiResource('locations', LocationController::class);
});
```

## 🔐 Security Considerations

### Rate Limiting

```php
// Combine with rate limiting
Route::middleware(['vauth', 'throttle:60,1'])->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### CSRF Protection

```php
// Web routes with CSRF protection
Route::middleware(['web', 'vauth'])->group(function () {
    Route::post('/profile', [ProfileController::class, 'update']);
});
```

### Input Validation

```php
// Combine with validation middleware
Route::middleware(['vauth', 'validate.input'])->group(function () {
    Route::post('/data', [DataController::class, 'store']);
});
```

## 📊 Monitoring and Logging

### Request Logging

```php
// Log authenticated requests
Route::middleware(['vauth', 'log.requests'])->group(function () {
    Route::apiResource('sensitive-data', SensitiveController::class);
});
```

### Performance Monitoring

```php
// Monitor route performance
Route::middleware(['vauth', 'monitor.performance'])->group(function () {
    Route::get('/heavy-operation', [HeavyController::class, 'process']);
});
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Redirect Loops
```php
// Check for circular redirects
if ($request->is('auth/*')) {
    return $next($request); // Don't protect auth routes
}
```

#### 2. AJAX/API Issues
```php
// Handle AJAX requests differently
if ($request->wantsJson()) {
    return response()->json(['error' => 'Unauthenticated'], 401);
}
return redirect()->route('core.auth.redirect');
```

#### 3. Session Issues
```php
// Ensure session middleware is applied
Route::middleware(['web', 'vauth'])->group(function () {
    // Routes that need session
});
```

### Debug Mode

Enable debug mode for middleware troubleshooting:

```env
VAUTH_DEBUG_MODE=true
LOG_LEVEL=debug
```

## 🔗 Next Steps

- [VAuth Service Guide](vauth-service.md) - API integration with middleware
- [Livewire Components](livewire-components.md) - UI components with middleware
- [Basic Usage](basic-usage.md) - General package usage
- [Configuration](../configuration.md) - Advanced middleware configuration
