<?php

namespace VoxDev\Core\Infrastructure\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use VoxDev\Core\Domain\Services\VAuthServiceInterface;
use VoxDev\Core\Helpers\VAuthHelper;

/**
 * VAuth Service
 *
 * Provides comprehensive OAuth authentication and API access functionality
 * for VoxDev applications. This service handles OAuth flows, token management,
 * and authorized API calls to the VAuth server.
 */
class VAuthService implements VAuthServiceInterface
{
    /**
     * Generate OAuth authorization redirect URL with PKCE
     *
     * @param Request $request The current HTTP request
     * @return string The authorization URL to redirect users to
     */
    public function redirectUrl(Request $request): string
    {
        // Generate and store state for CSRF protection
        $state = Str::random(40);
        $request->session()->put('state', $state);

        // Generate PKCE code verifier and challenge
        $codeVerifier = Str::random(128);
        $request->session()->put('code_verifier', $codeVerifier);

        $codeChallenge = strtr(rtrim(
            base64_encode(hash('sha256', $codeVerifier, true)),
            '='
        ), '+/', '-_');

        // Build authorization query parameters
        $query = http_build_query([
            'client_id' => config('core.client_id'),
            'redirect_uri' => config('core.redirect_uri'),
            'response_type' => 'code',
            'scope' => config('core.scopes', 'user:read'),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return VAuthHelper::getAuthorizeUrl($query);
    }

    /**
     * Get all locations from the VAuth API
     *
     * @return array Array of locations or empty array if request fails
     */
    public function getLocations(): array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            Log::warning('VAuthService: No valid token available for locations request');
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->get(config('core.url') . '/api/locations');

            if ($response->successful()) {
                $locations = $response->json();
                return is_array($locations) ? $locations : [];
            }

            Log::warning('VAuthService: Failed to fetch locations', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('VAuthService: Exception while fetching locations', [
                'message' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Get all users from the VAuth API
     *
     * @return array Array of users or empty array if request fails
     */
    public function getUsers(): array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            Log::warning('VAuthService: No valid token available for users request');
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->get(config('core.url') . '/api/users');

            if ($response->successful()) {
                $users = $response->json();
                return is_array($users) ? $users : [];
            }

            Log::warning('VAuthService: Failed to fetch users', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('VAuthService: Exception while fetching users', [
                'message' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Get paginated users from the VAuth API
     *
     * @param int $page Page number (default: 1)
     * @param int $perPage Items per page (default: 15)
     * @param array $filters Optional filters for the request
     * @return array Paginated users response or empty array if request fails
     */
    public function getPaginatedUsers(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            Log::warning('VAuthService: No valid token available for paginated users request');
            return [];
        }

        try {
            $queryParams = array_merge([
                'page' => $page,
                'per_page' => $perPage,
            ], $filters);

            $response = Http::withToken($token)
                ->get(config('core.url') . '/api/users', $queryParams);

            if ($response->successful()) {
                $users = $response->json();
                return is_array($users) ? $users : [];
            }

            Log::warning('VAuthService: Failed to fetch paginated users', [
                'status' => $response->status(),
                'response' => $response->body(),
                'params' => $queryParams
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('VAuthService: Exception while fetching paginated users', [
                'message' => $e->getMessage(),
                'page' => $page,
                'per_page' => $perPage
            ]);

            return [];
        }
    }

    /**
     * Get a specific user by ID from the VAuth API
     *
     * @param int|string $userId The user ID to fetch
     * @return array|null User data or null if not found/error
     */
    public function getUser($userId): ?array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            Log::warning('VAuthService: No valid token available for user request');
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get(config('core.url') . "/api/users/{$userId}");

            if ($response->successful()) {
                $user = $response->json();
                return is_array($user) ? $user : null;
            }

            if ($response->status() === 404) {
                Log::info('VAuthService: User not found', ['user_id' => $userId]);
                return null;
            }

            Log::warning('VAuthService: Failed to fetch user', [
                'user_id' => $userId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('VAuthService: Exception while fetching user', [
                'user_id' => $userId,
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Get paginated locations from the VAuth API
     *
     * @param int $page Page number (default: 1)
     * @param int $perPage Items per page (default: 15)
     * @param array $filters Optional filters for the request
     * @return array Paginated locations response or empty array if request fails
     */
    public function getPaginatedLocations(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            Log::warning('VAuthService: No valid token available for paginated locations request');
            return [];
        }

        try {
            $queryParams = array_merge([
                'page' => $page,
                'per_page' => $perPage,
            ], $filters);

            $response = Http::withToken($token)
                ->get(config('core.url') . '/api/locations', $queryParams);

            if ($response->successful()) {
                $locations = $response->json();
                return is_array($locations) ? $locations : [];
            }

            Log::warning('VAuthService: Failed to fetch paginated locations', [
                'status' => $response->status(),
                'response' => $response->body(),
                'params' => $queryParams
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('VAuthService: Exception while fetching paginated locations', [
                'message' => $e->getMessage(),
                'page' => $page,
                'per_page' => $perPage
            ]);

            return [];
        }
    }

    /**
     * Get a specific location by ID from the VAuth API
     *
     * @param int|string $locationId The location ID to fetch
     * @return array|null Location data or null if not found/error
     */
    public function getLocation($locationId): ?array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            Log::warning('VAuthService: No valid token available for location request');
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get(config('core.url') . "/api/locations/{$locationId}");

            if ($response->successful()) {
                $location = $response->json();
                return is_array($location) ? $location : null;
            }

            if ($response->status() === 404) {
                Log::info('VAuthService: Location not found', ['location_id' => $locationId]);
                return null;
            }

            Log::warning('VAuthService: Failed to fetch location', [
                'location_id' => $locationId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('VAuthService: Exception while fetching location', [
                'location_id' => $locationId,
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Make a custom authenticated API request to the VAuth server
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $endpoint API endpoint (without base URL)
     * @param array $data Request data for POST/PUT requests
     * @param array $headers Additional headers to include
     * @return array|null Response data or null if request fails
     */
    public function makeApiRequest(string $method, string $endpoint, array $data = [], array $headers = []): ?array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            Log::warning('VAuthService: No valid token available for API request', [
                'method' => $method,
                'endpoint' => $endpoint
            ]);
            return null;
        }

        try {
            $url = config('core.url') . '/' . ltrim($endpoint, '/');

            $httpClient = Http::withToken($token);

            if (!empty($headers)) {
                $httpClient = $httpClient->withHeaders($headers);
            }

            $response = match (strtoupper($method)) {
                'GET' => $httpClient->get($url, $data),
                'POST' => $httpClient->post($url, $data),
                'PUT' => $httpClient->put($url, $data),
                'PATCH' => $httpClient->patch($url, $data),
                'DELETE' => $httpClient->delete($url, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
            };

            if ($response->successful()) {
                $responseData = $response->json();
                return is_array($responseData) ? $responseData : null;
            }

            Log::warning('VAuthService: API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('VAuthService: Exception during API request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Check if the service has a valid authentication token
     *
     * @return bool True if a valid token is available
     */
    public function hasValidToken(): bool
    {
        return VAuthHelper::getValidToken() !== null;
    }

    /**
     * Get the current authentication status and token information
     *
     * @return array Token status information
     */
    public function getAuthStatus(): array
    {
        return VAuthHelper::getTokenStatus();
    }

    /**
     * Get the currently authenticated user information
     *
     * @return array|null User information or null if not authenticated
     */
    public function getCurrentUser(): ?array
    {
        return VAuthHelper::getUserInfo();
    }
}
