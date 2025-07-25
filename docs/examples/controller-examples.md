# Controller Examples

Real-world controller implementations using the Core SDK for various use cases.

## 🏠 Dashboard Controller

A comprehensive dashboard controller showcasing user authentication and data fetching.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use VoxDev\Core\Facades\VAuth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('vauth');
    }

    public function index()
    {
        // Get authenticated user
        $user = Auth::guard('core')->user();

        // Get dashboard data with caching
        $stats = Cache::remember('dashboard.stats', 300, function () {
            return $this->getDashboardStats();
        });

        // Get recent activity
        $recentUsers = $this->getRecentUsers();
        $recentLocations = $this->getRecentLocations();

        return view('dashboard.index', compact(
            'user',
            'stats',
            'recentUsers',
            'recentLocations'
        ));
    }

    public function profile()
    {
        $user = VAuth::getCurrentUser();

        if (!$user) {
            return redirect()->route('core.auth.redirect');
        }

        return view('dashboard.profile', compact('user'));
    }

    public function settings()
    {
        $user = Auth::guard('core')->user();
        $authStatus = VAuth::getAuthStatus();

        return view('dashboard.settings', compact('user', 'authStatus'));
    }

    private function getDashboardStats(): array
    {
        $users = VAuth::getUsers();
        $locations = VAuth::getLocations();

        return [
            'total_users' => count($users),
            'total_locations' => count($locations),
            'admin_users' => $this->countUsersByRole($users, 'admin'),
            'active_users' => $this->countActiveUsers($users),
        ];
    }

    private function getRecentUsers(int $limit = 5): array
    {
        $users = VAuth::getPaginatedUsers(1, $limit, [
            'sort' => 'created_at',
            'order' => 'desc'
        ]);

        return $users['data'] ?? [];
    }

    private function getRecentLocations(int $limit = 5): array
    {
        $locations = VAuth::getPaginatedLocations(1, $limit, [
            'sort' => 'created_at',
            'order' => 'desc'
        ]);

        return $locations['data'] ?? [];
    }

    private function countUsersByRole(array $users, string $role): int
    {
        return collect($users)->filter(function ($user) use ($role) {
            return in_array($role, $user['roles'] ?? []);
        })->count();
    }

    private function countActiveUsers(array $users): int
    {
        return collect($users)->filter(function ($user) {
            return $user['status'] === 'active';
        })->count();
    }
}
```

## 👥 User Management Controller

Complete CRUD operations using OAuth API data.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use VoxDev\Core\Facades\VAuth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('vauth');
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $role = $request->get('role');

        $filters = array_filter([
            'search' => $search,
            'role' => $role,
        ]);

        $users = VAuth::getPaginatedUsers($page, $perPage, $filters);

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return view('users.index', compact('users', 'search', 'role'));
    }

    public function show($id)
    {
        $user = VAuth::getUser($id);

        if (!$user) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return redirect()->route('users.index')
                ->with('error', 'User not found');
        }

        if (request()->wantsJson()) {
            return response()->json($user);
        }

        return view('users.show', compact('user'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string|in:admin,user,manager',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $response = VAuth::makeApiRequest('POST', '/api/users', [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if (!$response) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create user'], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create user')
                ->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json($response, 201);
        }

        return redirect()->route('users.show', $response['id'])
            ->with('success', 'User created successfully');
    }

    public function edit($id)
    {
        $user = VAuth::getUser($id);

        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User not found');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = VAuth::getUser($id);

        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return redirect()->route('users.index')
                ->with('error', 'User not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'role' => 'sometimes|required|string|in:admin,user,manager',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $response = VAuth::makeApiRequest('PUT', "/api/users/{$id}",
            $request->only(['name', 'email', 'role'])
        );

        if (!$response) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update user'], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update user')
                ->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json($response);
        }

        return redirect()->route('users.show', $id)
            ->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = VAuth::getUser($id);

        if (!$user) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return redirect()->route('users.index')
                ->with('error', 'User not found');
        }

        $response = VAuth::makeApiRequest('DELETE', "/api/users/{$id}");

        if (!$response) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete user'], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete user');
        }

        if (request()->wantsJson()) {
            return response()->json(['message' => 'User deleted successfully']);
        }

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $users = VAuth::getPaginatedUsers(1, 20, [
            'search' => $query
        ]);

        return response()->json($users);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $users = VAuth::getUsers();

        switch ($format) {
            case 'json':
                return response()->json($users);

            case 'csv':
                return $this->exportToCsv($users);

            default:
                return response()->json(['error' => 'Unsupported format'], 400);
        }
    }

    private function exportToCsv(array $users): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',
        ];

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Roles', 'Created At']);

            // CSV data
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user['id'],
                    $user['name'],
                    $user['email'],
                    implode(', ', $user['roles'] ?? []),
                    $user['created_at'] ?? '',
                ]);
            }

            fclose($handle);
        }, 'users.csv', $headers);
    }
}
```

## 🏢 Location Management Controller

Managing location data with geographical features.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use VoxDev\Core\Facades\VAuth;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('vauth');
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $country = $request->get('country');
        $search = $request->get('search');

        $filters = array_filter([
            'country' => $country,
            'search' => $search,
        ]);

        $locations = VAuth::getPaginatedLocations($page, $perPage, $filters);

        // Get countries for filter dropdown
        $countries = $this->getUniqueCountries();

        if ($request->wantsJson()) {
            return response()->json($locations);
        }

        return view('locations.index', compact('locations', 'countries', 'country', 'search'));
    }

    public function show($id)
    {
        $location = VAuth::getLocation($id);

        if (!$location) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Location not found'], 404);
            }

            return redirect()->route('locations.index')
                ->with('error', 'Location not found');
        }

        // Get nearby locations
        $nearbyLocations = $this->getNearbyLocations($location);

        if (request()->wantsJson()) {
            return response()->json([
                'location' => $location,
                'nearby' => $nearbyLocations
            ]);
        }

        return view('locations.show', compact('location', 'nearbyLocations'));
    }

    public function map(Request $request)
    {
        $locations = VAuth::getLocations();

        // Filter locations with coordinates
        $mappableLocations = collect($locations)->filter(function ($location) {
            return isset($location['latitude']) && isset($location['longitude']);
        })->values()->toArray();

        if ($request->wantsJson()) {
            return response()->json($mappableLocations);
        }

        return view('locations.map', compact('mappableLocations'));
    }

    public function stats()
    {
        $locations = VAuth::getLocations();

        $stats = [
            'total' => count($locations),
            'by_country' => $this->groupByCountry($locations),
            'by_type' => $this->groupByType($locations),
            'active' => $this->countActive($locations),
        ];

        return response()->json($stats);
    }

    public function nearby(Request $request, $id)
    {
        $location = VAuth::getLocation($id);

        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        $radius = $request->get('radius', 50); // km
        $nearby = $this->findNearbyLocations($location, $radius);

        return response()->json($nearby);
    }

    private function getUniqueCountries(): array
    {
        $locations = VAuth::getLocations();

        return collect($locations)
            ->pluck('country')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    private function getNearbyLocations(array $location, int $limit = 5): array
    {
        if (!isset($location['latitude']) || !isset($location['longitude'])) {
            return [];
        }

        return $this->findNearbyLocations($location, 100, $limit);
    }

    private function findNearbyLocations(array $centerLocation, float $radiusKm, int $limit = null): array
    {
        $allLocations = VAuth::getLocations();
        $centerLat = $centerLocation['latitude'];
        $centerLng = $centerLocation['longitude'];

        $nearby = collect($allLocations)
            ->filter(function ($location) use ($centerLocation) {
                return $location['id'] !== $centerLocation['id'] &&
                       isset($location['latitude']) &&
                       isset($location['longitude']);
            })
            ->map(function ($location) use ($centerLat, $centerLng) {
                $distance = $this->calculateDistance(
                    $centerLat, $centerLng,
                    $location['latitude'], $location['longitude']
                );

                $location['distance'] = round($distance, 2);
                return $location;
            })
            ->filter(function ($location) use ($radiusKm) {
                return $location['distance'] <= $radiusKm;
            })
            ->sortBy('distance');

        if ($limit) {
            $nearby = $nearby->take($limit);
        }

        return $nearby->values()->toArray();
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    private function groupByCountry(array $locations): array
    {
        return collect($locations)
            ->groupBy('country')
            ->map(function ($group) {
                return count($group);
            })
            ->toArray();
    }

    private function groupByType(array $locations): array
    {
        return collect($locations)
            ->groupBy('type')
            ->map(function ($group) {
                return count($group);
            })
            ->toArray();
    }

    private function countActive(array $locations): int
    {
        return collect($locations)->filter(function ($location) {
            return $location['status'] === 'active';
        })->count();
    }
}
```

## 🔐 Admin Controller

Administrative functions with role-based access control.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use VoxDev\Core\Facades\VAuth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['vauth', 'role:admin']);
    }

    public function dashboard()
    {
        $user = Auth::guard('core')->user();

        // Verify admin role
        if (!$this->hasAdminRole($user)) {
            abort(403, 'Access denied. Admin role required.');
        }

        $stats = $this->getAdminStats();
        $systemHealth = $this->checkSystemHealth();
        $recentActivity = $this->getRecentActivity();

        return view('admin.dashboard', compact(
            'user',
            'stats',
            'systemHealth',
            'recentActivity'
        ));
    }

    public function users(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 25);
        $filters = $request->only(['search', 'role', 'status']);

        $users = VAuth::getPaginatedUsers($page, $perPage, $filters);

        // Add admin actions for each user
        if (isset($users['data'])) {
            $users['data'] = collect($users['data'])->map(function ($user) {
                $user['can_edit'] = $this->canEditUser($user);
                $user['can_delete'] = $this->canDeleteUser($user);
                return $user;
            })->toArray();
        }

        return view('admin.users', compact('users', 'filters'));
    }

    public function userActivity($userId)
    {
        $user = VAuth::getUser($userId);

        if (!$user) {
            return redirect()->route('admin.users')
                ->with('error', 'User not found');
        }

        // Get user activity from API
        $activity = VAuth::makeApiRequest('GET', "/api/users/{$userId}/activity");

        return view('admin.user-activity', compact('user', 'activity'));
    }

    public function systemSettings()
    {
        $settings = VAuth::makeApiRequest('GET', '/api/admin/settings');
        $authStatus = VAuth::getAuthStatus();

        return view('admin.settings', compact('settings', 'authStatus'));
    }

    public function updateSettings(Request $request)
    {
        $settings = $request->validate([
            'max_users' => 'integer|min:1',
            'session_timeout' => 'integer|min:60',
            'enable_2fa' => 'boolean',
            'allow_registration' => 'boolean',
        ]);

        $response = VAuth::makeApiRequest('PUT', '/api/admin/settings', $settings);

        if (!$response) {
            return redirect()->back()
                ->with('error', 'Failed to update settings');
        }

        Log::info('Admin updated system settings', [
            'admin_id' => Auth::guard('core')->id(),
            'settings' => $settings
        ]);

        return redirect()->back()
            ->with('success', 'Settings updated successfully');
    }

    public function auditLog(Request $request)
    {
        $page = $request->get('page', 1);
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);

        $auditLog = VAuth::makeApiRequest('GET', '/api/admin/audit-log', array_merge(
            ['page' => $page, 'per_page' => 50],
            $filters
        ));

        return view('admin.audit-log', compact('auditLog', 'filters'));
    }

    public function impersonateUser($userId)
    {
        $currentUser = Auth::guard('core')->user();
        $targetUser = VAuth::getUser($userId);

        if (!$targetUser) {
            return redirect()->back()
                ->with('error', 'User not found');
        }

        // Check if impersonation is allowed
        if (!$this->canImpersonate($currentUser, $targetUser)) {
            abort(403, 'Cannot impersonate this user');
        }

        // Start impersonation session
        session(['impersonating' => [
            'original_user_id' => $currentUser->id,
            'target_user_id' => $userId,
            'started_at' => now(),
        ]]);

        Log::warning('Admin started impersonating user', [
            'admin_id' => $currentUser->id,
            'target_user_id' => $userId
        ]);

        return redirect()->route('dashboard')
            ->with('warning', "You are now impersonating {$targetUser['name']}");
    }

    public function stopImpersonation()
    {
        $impersonation = session('impersonating');

        if (!$impersonation) {
            return redirect()->route('dashboard');
        }

        session()->forget('impersonating');

        Log::info('Admin stopped impersonating user', [
            'admin_id' => $impersonation['original_user_id'],
            'target_user_id' => $impersonation['target_user_id'],
            'duration' => now()->diffInMinutes($impersonation['started_at'])
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Stopped impersonating user');
    }

    private function getAdminStats(): array
    {
        $users = VAuth::getUsers();
        $locations = VAuth::getLocations();

        return [
            'total_users' => count($users),
            'admin_users' => $this->countByRole($users, 'admin'),
            'active_users' => $this->countByStatus($users, 'active'),
            'total_locations' => count($locations),
            'system_uptime' => $this->getSystemUptime(),
            'api_health' => VAuth::hasValidToken(),
        ];
    }

    private function checkSystemHealth(): array
    {
        return [
            'oauth_server' => VAuth::hasValidToken(),
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
        ];
    }

    private function getRecentActivity(): array
    {
        return VAuth::makeApiRequest('GET', '/api/admin/recent-activity', [
            'limit' => 10
        ]) ?: [];
    }

    private function hasAdminRole($user): bool
    {
        return in_array('admin', $user->roles ?? []);
    }

    private function canEditUser(array $user): bool
    {
        // Super admins can edit anyone, regular admins can't edit other admins
        $currentUser = Auth::guard('core')->user();

        if (in_array('super_admin', $currentUser->roles ?? [])) {
            return true;
        }

        return !in_array('admin', $user['roles'] ?? []);
    }

    private function canDeleteUser(array $user): bool
    {
        // Similar logic to edit, but more restrictive
        $currentUser = Auth::guard('core')->user();

        // Can't delete yourself
        if ($user['id'] === $currentUser->id) {
            return false;
        }

        // Only super admins can delete other admins
        if (in_array('admin', $user['roles'] ?? [])) {
            return in_array('super_admin', $currentUser->roles ?? []);
        }

        return true;
    }

    private function canImpersonate($currentUser, array $targetUser): bool
    {
        // Can't impersonate yourself
        if ($targetUser['id'] === $currentUser->id) {
            return false;
        }

        // Can't impersonate other admins unless you're super admin
        if (in_array('admin', $targetUser['roles'] ?? [])) {
            return in_array('super_admin', $currentUser->roles ?? []);
        }

        return true;
    }

    private function countByRole(array $users, string $role): int
    {
        return collect($users)->filter(function ($user) use ($role) {
            return in_array($role, $user['roles'] ?? []);
        })->count();
    }

    private function countByStatus(array $users, string $status): int
    {
        return collect($users)->filter(function ($user) use ($status) {
            return $user['status'] === $status;
        })->count();
    }

    private function getSystemUptime(): string
    {
        // Implementation depends on your system monitoring
        return '99.9%';
    }

    private function checkDatabaseHealth(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkCacheHealth(): bool
    {
        try {
            \Cache::put('health_check', 'ok', 1);
            return \Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkStorageHealth(): bool
    {
        try {
            return \Storage::exists('test') || \Storage::put('test', 'ok');
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

## 🌐 API Controller

RESTful API endpoints for frontend applications.

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use VoxDev\Core\Facades\VAuth;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('vauth');
    }

    public function user()
    {
        $user = VAuth::getCurrentUser();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function authStatus()
    {
        $status = VAuth::getAuthStatus();

        return response()->json($status);
    }

    public function users(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 15), 100); // Max 100 per page
        $filters = $request->only(['search', 'role', 'status']);

        $users = VAuth::getPaginatedUsers($page, $perPage, $filters);

        return response()->json($users);
    }

    public function locations(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 15), 100);
        $filters = $request->only(['search', 'country', 'type']);

        $locations = VAuth::getPaginatedLocations($page, $perPage, $filters);

        return response()->json($locations);
    }

    public function proxy(Request $request)
    {
        // Validate endpoint
        $endpoint = $request->get('endpoint');

        if (!$this->isAllowedEndpoint($endpoint)) {
            return response()->json(['error' => 'Endpoint not allowed'], 403);
        }

        // Make API request
        $response = VAuth::makeApiRequest(
            $request->method(),
            $endpoint,
            $request->except('endpoint')
        );

        if ($response === null) {
            return response()->json(['error' => 'API request failed'], 502);
        }

        return response()->json($response);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // users, locations, all

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $results = [];

        if (in_array($type, ['users', 'all'])) {
            $users = VAuth::getPaginatedUsers(1, 10, ['search' => $query]);
            $results['users'] = $users['data'] ?? [];
        }

        if (in_array($type, ['locations', 'all'])) {
            $locations = VAuth::getPaginatedLocations(1, 10, ['search' => $query]);
            $results['locations'] = $locations['data'] ?? [];
        }

        return response()->json($results);
    }

    public function stats()
    {
        $users = VAuth::getUsers();
        $locations = VAuth::getLocations();

        $stats = [
            'users' => [
                'total' => count($users),
                'by_role' => $this->groupByField($users, 'roles'),
                'by_status' => $this->groupByField($users, 'status'),
            ],
            'locations' => [
                'total' => count($locations),
                'by_country' => $this->groupByField($locations, 'country'),
                'by_type' => $this->groupByField($locations, 'type'),
            ],
            'system' => [
                'auth_status' => VAuth::hasValidToken(),
                'timestamp' => now()->toISOString(),
            ]
        ];

        return response()->json($stats);
    }

    private function isAllowedEndpoint(string $endpoint): bool
    {
        $allowedPatterns = [
            '/api/users',
            '/api/locations',
            '/api/public/',
        ];

        foreach ($allowedPatterns as $pattern) {
            if (str_starts_with($endpoint, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function groupByField(array $items, string $field): array
    {
        $result = [];

        foreach ($items as $item) {
            $value = $item[$field] ?? 'unknown';

            if (is_array($value)) {
                // Handle array fields like roles
                foreach ($value as $v) {
                    $result[$v] = ($result[$v] ?? 0) + 1;
                }
            } else {
                $result[$value] = ($result[$value] ?? 0) + 1;
            }
        }

        return $result;
    }
}
```

## 🔗 Related Examples

- [Service Examples](service-examples.md) - Service layer implementations
- [Testing Examples](testing-examples.md) - Test cases for controllers
- [Basic Usage](../usage/basic-usage.md) - Getting started guide
- [VAuth Service](../usage/vauth-service.md) - API integration details
