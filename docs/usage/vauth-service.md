# VAuthService Usage Guide

The VAuthService provides OAuth authentication and API access functionality for VoxDev applications. This service is designed to be used directly in client applications without exposing HTTP endpoints.

## Installation

The VAuthService is automatically registered when you install the Core SDK package.

## Configuration

Make sure your `.env` file has the following VAuth configuration:

```env
VAUTH_URL=https://your-vauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/auth/oauth/callback
VAUTH_SCOPES=user:read
```

## Usage

### Using the Facade (Recommended)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use VoxDev\Core\Facades\VAuth;

class YourController extends Controller
{
    public function redirectToOAuth(Request $request)
    {
        // Generate OAuth authorization URL
        $authUrl = VAuth::redirectUrl($request);
        
        return redirect($authUrl);
    }
    
    public function getLocations()
    {
        // Get all locations
        $locations = VAuth::getLocations();
        
        return response()->json($locations);
    }
    
    public function getUsers()
    {
        // Get all users
        $users = VAuth::getUsers();
        
        return response()->json($users);
    }
    
    public function getPaginatedUsers(Request $request)
    {
        // Get paginated users
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $filters = $request->only(['search', 'role', 'status']);
        
        $users = VAuth::getPaginatedUsers($page, $perPage, $filters);
        
        return response()->json($users);
    }
    
    public function getUser($userId)
    {
        // Get specific user
        $user = VAuth::getUser($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json($user);
    }
    
    public function customApiCall()
    {
        // Make custom API request
        $result = VAuth::makeApiRequest('GET', '/api/custom-endpoint', [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);
        
        return response()->json($result);
    }
    
    public function checkAuthStatus()
    {
        // Check if user has valid token
        $hasToken = VAuth::hasValidToken();
        $authStatus = VAuth::getAuthStatus();
        $currentUser = VAuth::getCurrentUser();
        
        return response()->json([
            'has_token' => $hasToken,
            'status' => $authStatus,
            'user' => $currentUser
        ]);
    }
}
```

### Using Dependency Injection

```php
<?php

namespace App\Services;

use VoxDev\Core\Domain\Services\VAuthServiceInterface;

class YourService
{
    public function __construct(
        private VAuthServiceInterface $vAuthService
    ) {}
    
    public function syncUsers(): array
    {
        return $this->vAuthService->getUsers();
    }
    
    public function syncLocations(): array
    {
        return $this->vAuthService->getLocations();
    }
}
```

### In Livewire Components

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use VoxDev\Core\Facades\VAuth;

class UsersList extends Component
{
    public $users = [];
    public $currentPage = 1;
    public $perPage = 15;
    
    public function mount()
    {
        $this->loadUsers();
    }
    
    public function loadUsers()
    {
        $this->users = VAuth::getPaginatedUsers($this->currentPage, $this->perPage);
    }
    
    public function nextPage()
    {
        $this->currentPage++;
        $this->loadUsers();
    }
    
    public function render()
    {
        return view('livewire.users-list');
    }
}
```

## Available Methods

### Authentication Methods

- `redirectUrl(Request $request): string` - Generate OAuth authorization URL with PKCE
- `hasValidToken(): bool` - Check if user has a valid authentication token
- `getAuthStatus(): array` - Get current authentication status
- `getCurrentUser(): ?array` - Get currently authenticated user data

### Users API Methods

- `getUsers(): array` - Get all users
- `getPaginatedUsers(int $page = 1, int $perPage = 15, array $filters = []): array` - Get paginated users
- `getUser($userId): ?array` - Get specific user by ID

### Locations API Methods

- `getLocations(): array` - Get all locations  
- `getPaginatedLocations(int $page = 1, int $perPage = 15, array $filters = []): array` - Get paginated locations
- `getLocation($locationId): ?array` - Get specific location by ID

### Generic API Methods

- `makeApiRequest(string $method, string $endpoint, array $data = [], array $headers = []): ?array` - Make custom API requests

## Error Handling

All methods handle errors gracefully:

- Authentication methods return empty arrays `[]` or `false` on failure
- Data retrieval methods return empty arrays `[]` or `null` on failure
- Errors are logged automatically for debugging
- No exceptions are thrown to the calling code

## Token Management

The service automatically handles token validation and refresh. You don't need to manage tokens manually - the service will:

- Check token validity before making API calls
- Return empty results if no valid token is available
- Log authentication issues for debugging

## Middleware Protection

Remember to protect your routes with the `vauth` middleware to ensure users are authenticated:

```php
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/users', [UserController::class, 'index']);
});
```
