# Facades Reference

Complete reference for all facades provided by the Core SDK.

## 🎯 VAuth Facade

The main facade for OAuth authentication and API integration.

### Namespace

```php
use VoxDev\Core\Facades\VAuth;
```

### Authentication Methods

#### `redirectUrl(Request $request): string`

Generates OAuth authorization URL with PKCE for secure authentication.

```php
use Illuminate\Http\Request;
use VoxDev\Core\Facades\VAuth;

public function login(Request $request)
{
    $authUrl = VAuth::redirectUrl($request);
    return redirect($authUrl);
}
```

**Parameters:**
- `$request` (Request) - The current HTTP request

**Returns:** String - OAuth authorization URL

**Example Response:**
```
https://oauth-server.com/oauth/authorize?client_id=123&redirect_uri=...&state=abc&code_challenge=xyz
```

---

#### `hasValidToken(): bool`

Checks if the current user has a valid OAuth token.

```php
if (VAuth::hasValidToken()) {
    // User is authenticated and token is valid
    $data = VAuth::getUsers();
} else {
    // Redirect to login
    return redirect()->route('core.auth.redirect');
}
```

**Returns:** Boolean - True if valid token exists

---

#### `getAuthStatus(): array`

Gets comprehensive authentication status information.

```php
$status = VAuth::getAuthStatus();

// Example response:
[
    'authenticated' => true,
    'token_valid' => true,
    'token_expires_at' => '2024-01-15 14:30:00',
    'user_id' => 123,
    'scopes' => ['user:read', 'locations:read']
]
```

**Returns:** Array - Authentication status details

---

#### `getCurrentUser(): ?array`

Gets the currently authenticated user data from OAuth server.

```php
$user = VAuth::getCurrentUser();

if ($user) {
    echo "Welcome, " . $user['name'];
} else {
    // User not authenticated
}
```

**Returns:** Array|null - User data or null if not authenticated

**Example Response:**
```php
[
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'roles' => ['admin', 'user'],
    'avatar_url' => 'https://...',
    'created_at' => '2024-01-01T00:00:00Z'
]
```

### API Data Methods

#### `getUsers(): array`

Fetches all users from the OAuth server.

```php
$users = VAuth::getUsers();

foreach ($users as $user) {
    echo $user['name'] . ' - ' . $user['email'] . "\n";
}
```

**Returns:** Array - List of users

**Example Response:**
```php
[
    [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'roles' => ['admin']
    ],
    [
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'roles' => ['user']
    ]
]
```

---

#### `getPaginatedUsers(int $page = 1, int $perPage = 15, array $filters = []): array`

Fetches paginated users with optional filtering.

```php
// Basic pagination
$users = VAuth::getPaginatedUsers(1, 20);

// With filters
$users = VAuth::getPaginatedUsers(1, 10, [
    'role' => 'admin',
    'status' => 'active',
    'search' => 'john'
]);
```

**Parameters:**
- `$page` (int) - Page number (default: 1)
- `$perPage` (int) - Items per page (default: 15)
- `$filters` (array) - Filter criteria (optional)

**Returns:** Array - Paginated response

**Example Response:**
```php
[
    'data' => [
        ['id' => 1, 'name' => 'John Doe', ...],
        ['id' => 2, 'name' => 'Jane Smith', ...]
    ],
    'meta' => [
        'current_page' => 1,
        'last_page' => 5,
        'per_page' => 15,
        'total' => 67
    ],
    'links' => [
        'first' => 'https://...',
        'last' => 'https://...',
        'prev' => null,
        'next' => 'https://...'
    ]
]
```

---

#### `getUser($userId): ?array`

Fetches a specific user by ID.

```php
$user = VAuth::getUser(123);

if ($user) {
    echo "User: " . $user['name'];
} else {
    echo "User not found";
}
```

**Parameters:**
- `$userId` (int|string) - User ID

**Returns:** Array|null - User data or null if not found

---

#### `getLocations(): array`

Fetches all locations from the OAuth server.

```php
$locations = VAuth::getLocations();

foreach ($locations as $location) {
    echo $location['name'] . ' - ' . $location['address'] . "\n";
}
```

**Returns:** Array - List of locations

**Example Response:**
```php
[
    [
        'id' => 1,
        'name' => 'Main Office',
        'address' => '123 Business St',
        'city' => 'Business City',
        'country' => 'Business Country'
    ],
    [
        'id' => 2,
        'name' => 'Branch Office',
        'address' => '456 Branch Ave',
        'city' => 'Branch City',
        'country' => 'Branch Country'
    ]
]
```

---

#### `getPaginatedLocations(int $page = 1, int $perPage = 15, array $filters = []): array`

Fetches paginated locations with optional filtering.

```php
// Basic pagination
$locations = VAuth::getPaginatedLocations(1, 10);

// With filters
$locations = VAuth::getPaginatedLocations(1, 20, [
    'country' => 'USA',
    'active' => true
]);
```

**Parameters:**
- `$page` (int) - Page number (default: 1)
- `$perPage` (int) - Items per page (default: 15)
- `$filters` (array) - Filter criteria (optional)

**Returns:** Array - Paginated response (same structure as users)

---

#### `getLocation($locationId): ?array`

Fetches a specific location by ID.

```php
$location = VAuth::getLocation(1);

if ($location) {
    echo "Location: " . $location['name'];
} else {
    echo "Location not found";
}
```

**Parameters:**
- `$locationId` (int|string) - Location ID

**Returns:** Array|null - Location data or null if not found

### Generic API Methods

#### `makeApiRequest(string $method, string $endpoint, array $data = [], array $headers = []): ?array`

Makes custom API requests to the OAuth server.

```php
// GET request
$response = VAuth::makeApiRequest('GET', '/api/custom-data', [
    'filter' => 'active',
    'sort' => 'name'
]);

// POST request
$response = VAuth::makeApiRequest('POST', '/api/users', [
    'name' => 'New User',
    'email' => 'new@example.com'
]);

// With custom headers
$response = VAuth::makeApiRequest('GET', '/api/data', [], [
    'Accept' => 'application/vnd.api+json',
    'X-Custom-Header' => 'value'
]);
```

**Parameters:**
- `$method` (string) - HTTP method (GET, POST, PUT, DELETE, etc.)
- `$endpoint` (string) - API endpoint path
- `$data` (array) - Request data (query params for GET, body for POST)
- `$headers` (array) - Additional headers (optional)

**Returns:** Array|null - API response or null on error

## 🎯 Core Facade

Additional facade for core package functionality.

### Namespace

```php
use VoxDev\Core\Facades\Core;
```

### Configuration Methods

#### `getConfig(string $key = null): mixed`

Gets configuration values from the core config.

```php
// Get specific config
$clientId = Core::getConfig('client_id');

// Get nested config
$autoRegister = Core::getConfig('features.auto_register_guard');

// Get all config
$allConfig = Core::getConfig();
```

**Parameters:**
- `$key` (string|null) - Configuration key (optional)

**Returns:** Mixed - Configuration value

---

#### `isFeatureEnabled(string $feature): bool`

Checks if a specific feature is enabled.

```php
if (Core::isFeatureEnabled('auto_register_livewire')) {
    // Livewire components are auto-registered
}

if (Core::isFeatureEnabled('clean_architecture')) {
    // Clean architecture features are enabled
}
```

**Parameters:**
- `$feature` (string) - Feature name

**Returns:** Boolean - True if feature is enabled

### Utility Methods

#### `version(): string`

Gets the current package version.

```php
$version = Core::version();
echo "Core SDK Version: " . $version;
```

**Returns:** String - Package version

---

#### `getRoutes(): array`

Gets all registered OAuth routes.

```php
$routes = Core::getRoutes();

foreach ($routes as $route) {
    echo $route['name'] . ' - ' . $route['uri'] . "\n";
}
```

**Returns:** Array - List of registered routes

## 🔧 Usage Examples

### Complete Authentication Flow

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use VoxDev\Core\Facades\VAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Generate OAuth URL and redirect
        $authUrl = VAuth::redirectUrl($request);
        return redirect($authUrl);
    }

    public function dashboard()
    {
        // Check authentication
        if (!VAuth::hasValidToken()) {
            return redirect()->route('login');
        }

        // Get current user and data
        $user = VAuth::getCurrentUser();
        $users = VAuth::getUsers();
        $locations = VAuth::getLocations();

        return view('dashboard', compact('user', 'users', 'locations'));
    }

    public function apiProxy(Request $request)
    {
        // Proxy API requests
        $response = VAuth::makeApiRequest(
            $request->method(),
            $request->get('endpoint'),
            $request->all()
        );

        return response()->json($response);
    }
}
```

### Service Layer Integration

```php
<?php

namespace App\Services;

use VoxDev\Core\Facades\VAuth;

class DataSyncService
{
    public function syncAllData(): array
    {
        $results = [];

        // Sync users
        $users = VAuth::getUsers();
        $results['users'] = $this->syncUsers($users);

        // Sync locations
        $locations = VAuth::getLocations();
        $results['locations'] = $this->syncLocations($locations);

        return $results;
    }

    public function searchUsers(string $query, int $page = 1): array
    {
        return VAuth::getPaginatedUsers($page, 20, [
            'search' => $query
        ]);
    }

    private function syncUsers(array $users): int
    {
        $synced = 0;

        foreach ($users as $userData) {
            \App\Models\User::updateOrCreate(
                ['oauth_id' => $userData['id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'roles' => $userData['roles'] ?? [],
                ]
            );
            $synced++;
        }

        return $synced;
    }

    private function syncLocations(array $locations): int
    {
        $synced = 0;

        foreach ($locations as $locationData) {
            \App\Models\Location::updateOrCreate(
                ['oauth_id' => $locationData['id']],
                [
                    'name' => $locationData['name'],
                    'address' => $locationData['address'],
                    'city' => $locationData['city'],
                    'country' => $locationData['country'],
                ]
            );
            $synced++;
        }

        return $synced;
    }
}
```

## 🚨 Error Handling

All facade methods handle errors gracefully:

- **Authentication methods** return `false` or empty arrays on failure
- **Data retrieval methods** return empty arrays `[]` or `null` on failure
- **Errors are logged** automatically for debugging
- **No exceptions** are thrown to calling code

### Example Error Handling

```php
// Check for authentication errors
$status = VAuth::getAuthStatus();
if (!$status['authenticated']) {
    Log::warning('User not authenticated');
    return redirect()->route('login');
}

// Handle API errors
$users = VAuth::getUsers();
if (empty($users)) {
    Log::error('Failed to fetch users from OAuth API');
    return response()->json(['error' => 'Data unavailable'], 503);
}

// Validate API responses
$user = VAuth::getUser($id);
if (!$user) {
    return response()->json(['error' => 'User not found'], 404);
}
```

## 🔗 Related Documentation

- [VAuth Service Guide](../usage/vauth-service.md) - Detailed service usage
- [Basic Usage](../usage/basic-usage.md) - Getting started
- [Services Reference](services.md) - Service interfaces
- [Events Reference](events.md) - Available events
