# Basic Usage Guide

Learn how to use the Core SDK in your Laravel application for OAuth authentication and API integration.

## 🚀 Quick Start

After [installation](../installation.md), you can start using the package immediately:

### 1. Protecting Routes

Use the `vauth` middleware to protect your routes:

```php
// routes/web.php
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});
```

### 2. Getting the Authenticated User

```php
use Illuminate\Support\Facades\Auth;

// In your controllers
public function dashboard()
{
    $user = Auth::guard('core')->user();

    return view('dashboard', ['user' => $user]);
}
```

### 3. Using the VAuth Service

```php
use VoxDev\Core\Facades\VAuth;

// Get users from the OAuth server
$users = VAuth::getUsers();

// Get locations
$locations = VAuth::getLocations();

// Check authentication status
$isAuthenticated = VAuth::hasValidToken();
```

## 🔐 Authentication Flow

### Login Process

1. **Redirect to OAuth**: Users visit `/auth/oauth/redirect`
2. **Authorization**: Users authorize on the OAuth server
3. **Callback**: OAuth server redirects to `/auth/oauth/callback`
4. **Token Exchange**: Package exchanges code for tokens
5. **User Session**: User is logged in and redirected

### Manual Login Redirect

```php
// In a controller
public function login(Request $request)
{
    $authUrl = VAuth::redirectUrl($request);
    return redirect($authUrl);
}
```

### Logout

```php
// Logout route (already provided by package)
Route::post('/auth/oauth/logout', function () {
    Auth::guard('core')->logout();
    return redirect('/');
});
```

## 🛡️ Using the Auth Guard

### Check Authentication

```php
// Check if user is authenticated
if (Auth::guard('core')->check()) {
    // User is authenticated
    $user = Auth::guard('core')->user();
}

// Get user or null
$user = Auth::guard('core')->user();

// Get user ID
$userId = Auth::guard('core')->id();
```

### In Blade Templates

```blade
{{-- Check authentication --}}
@auth('core')
    <p>Welcome, {{ Auth::guard('core')->user()->name }}!</p>

    <form method="POST" action="{{ route('core.auth.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
@else
    <a href="{{ route('core.auth.redirect') }}">Login</a>
@endauth

{{-- Get user data --}}
@if(Auth::guard('core')->check())
    <div class="user-info">
        <h3>{{ Auth::guard('core')->user()->name }}</h3>
        <p>{{ Auth::guard('core')->user()->email }}</p>
    </div>
@endif
```

### Using the HasCoreAuth Trait

Add the trait to your models for easy access:

```php
use VoxDev\Core\Traits\HasCoreAuth;

class User extends Model
{
    use HasCoreAuth;
}

// Usage
$user = new User();
$coreUser = $user->getCoreUser();
$userAttribute = $user->getCoreUserAttribute('role');
```

## 📡 API Integration

### Using the VAuth Facade

```php
use VoxDev\Core\Facades\VAuth;

class UserController extends Controller
{
    public function index()
    {
        // Get all users
        $users = VAuth::getUsers();

        return response()->json($users);
    }

    public function show($id)
    {
        // Get specific user
        $user = VAuth::getUser($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function locations()
    {
        // Get paginated locations
        $page = request('page', 1);
        $locations = VAuth::getPaginatedLocations($page, 20);

        return response()->json($locations);
    }
}
```

### Custom API Requests

```php
// Make custom API calls
$response = VAuth::makeApiRequest('GET', '/api/custom-endpoint', [
    'filter' => 'active',
    'sort' => 'name'
]);

// POST request
$response = VAuth::makeApiRequest('POST', '/api/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Error Handling

```php
// VAuth methods return empty arrays/null on error
$users = VAuth::getUsers();

if (empty($users)) {
    // Handle error - check logs for details
    Log::warning('Failed to fetch users from VAuth API');
}

// Check if user has valid token
if (!VAuth::hasValidToken()) {
    return redirect()->route('core.auth.redirect');
}
```

## 🎨 Using Built-in UI Components

### Livewire Components

The package includes reactive Livewire components:

```blade
{{-- Authentication status component --}}
<livewire:core-auth-status />

{{-- Login redirect component --}}
<livewire:core-auth-redirect />

{{-- Callback handling component --}}
<livewire:core-auth-callback />
```

### Pre-built Pages

Use the built-in pages or customize them:

```php
// routes/web.php

// Login page with Livewire component
Route::get('/login', function () {
    return view('core::auth.login');
});

// Dashboard page
Route::get('/dashboard', function () {
    return view('core::dashboard');
})->middleware('vauth');
```

## 🔧 Middleware Usage

### Basic Protection

```php
// Protect single route
Route::get('/admin', [AdminController::class, 'index'])->middleware('vauth');

// Protect route group
Route::middleware(['vauth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('posts', PostController::class);
});
```

### Combined Middleware

```php
// Combine with other middleware
Route::middleware(['web', 'vauth', 'verified'])->group(function () {
    Route::get('/verified-dashboard', [DashboardController::class, 'verified']);
});
```

### Middleware Groups

The package automatically creates middleware groups:

```php
// Use the auth.oauth group (includes 'web' and 'vauth')
Route::middleware(['auth.oauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

## 🧪 Testing Your Integration

### Feature Tests

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/auth/oauth/redirect');
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        // Mock authentication
        $this->actingAs($user, 'core');

        $response = $this->get('/dashboard');

        $response->assertOk();
    }
}
```

### Unit Tests

```php
use VoxDev\Core\Facades\VAuth;

class VAuthServiceTest extends TestCase
{
    public function test_can_get_users()
    {
        // Mock the service
        VAuth::shouldReceive('getUsers')
            ->once()
            ->andReturn([
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Doe'],
            ]);

        $users = VAuth::getUsers();

        $this->assertCount(2, $users);
    }
}
```

## 🔄 Common Patterns

### Service Layer Integration

```php
namespace App\Services;

use VoxDev\Core\Facades\VAuth;

class UserSyncService
{
    public function syncUsers(): int
    {
        $users = VAuth::getUsers();
        $synced = 0;

        foreach ($users as $userData) {
            // Sync user to local database
            User::updateOrCreate(
                ['oauth_id' => $userData['id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => $userData['role'] ?? 'user',
                ]
            );
            $synced++;
        }

        return $synced;
    }
}
```

### Repository Pattern

```php
namespace App\Repositories;

use VoxDev\Core\Facades\VAuth;

class LocationRepository
{
    public function getAllLocations(): array
    {
        return VAuth::getLocations();
    }

    public function getPaginatedLocations(int $page = 1, int $perPage = 15): array
    {
        return VAuth::getPaginatedLocations($page, $perPage);
    }

    public function findLocation(int $id): ?array
    {
        return VAuth::getLocation($id);
    }
}
```

## 📱 Frontend Integration

### JavaScript/AJAX

```javascript
// Check authentication status
fetch('/api/auth/status')
    .then(response => response.json())
    .then(data => {
        if (!data.authenticated) {
            window.location.href = '/auth/oauth/redirect';
        }
    });

// Make authenticated API calls
fetch('/api/users', {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    }
})
.then(response => response.json())
.then(users => {
    console.log('Users:', users);
});
```

### Vue.js/React Integration

```javascript
// Vue.js component
export default {
    data() {
        return {
            user: null,
            loading: true
        }
    },

    async mounted() {
        try {
            const response = await fetch('/api/auth/user');
            if (response.ok) {
                this.user = await response.json();
            }
        } catch (error) {
            console.error('Auth error:', error);
        } finally {
            this.loading = false;
        }
    }
}
```

## ⚡ Performance Tips

### Caching API Responses

```php
use Illuminate\Support\Facades\Cache;

class OptimizedUserService
{
    public function getUsers(): array
    {
        return Cache::remember('vauth.users', 3600, function () {
            return VAuth::getUsers();
        });
    }
}
```

### Lazy Loading

```php
// Only fetch when needed
class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'userCount' => fn() => count(VAuth::getUsers()),
            'locationCount' => fn() => count(VAuth::getLocations()),
        ]);
    }
}
```

## 🔗 Next Steps

- [VAuth Service Guide](vauth-service.md) - Detailed API integration
- [Middleware Guide](middleware.md) - Advanced route protection
- [Livewire Components](livewire-components.md) - UI component usage
- [Configuration](../configuration.md) - Advanced configuration options
