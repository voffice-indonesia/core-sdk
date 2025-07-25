<?php

namespace VoxDev\Core\Domain\Services;

use Illuminate\Http\Request;

/**
 * VAuth Service Interface
 *
 * Defines the contract for VAuth service implementations.
 * This interface ensures consistent OAuth and API functionality
 * across different implementations.
 */
interface VAuthServiceInterface
{
    /**
     * Generate OAuth authorization redirect URL with PKCE
     *
     * @param Request $request The current HTTP request
     * @return string The authorization URL to redirect users to
     */
    public function redirectUrl(Request $request): string;

    /**
     * Get all locations from the VAuth API
     *
     * @return array Array of locations or empty array if request fails
     */
    public function getLocations(): array;

    /**
     * Get all users from the VAuth API
     *
     * @return array Array of users or empty array if request fails
     */
    public function getUsers(): array;

    /**
     * Get paginated users from the VAuth API
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param array $filters Optional filters for the request
     * @return array Paginated users response or empty array if request fails
     */
    public function getPaginatedUsers(int $page = 1, int $perPage = 15, array $filters = []): array;

    /**
     * Get a specific user by ID from the VAuth API
     *
     * @param int|string $userId The user ID to fetch
     * @return array|null User data or null if not found/error
     */
    public function getUser($userId): ?array;

    /**
     * Get paginated locations from the VAuth API
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param array $filters Optional filters for the request
     * @return array Paginated locations response or empty array if request fails
     */
    public function getPaginatedLocations(int $page = 1, int $perPage = 15, array $filters = []): array;

    /**
     * Get a specific location by ID from the VAuth API
     *
     * @param int|string $locationId The location ID to fetch
     * @return array|null Location data or null if not found/error
     */
    public function getLocation($locationId): ?array;

    /**
     * Make a custom authenticated API request to the VAuth server
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $endpoint API endpoint (without base URL)
     * @param array $data Request data for POST/PUT requests
     * @param array $headers Additional headers to include
     * @return array|null Response data or null if request fails
     */
    public function makeApiRequest(string $method, string $endpoint, array $data = [], array $headers = []): ?array;

    /**
     * Check if the service has a valid authentication token
     *
     * @return bool True if a valid token is available
     */
    public function hasValidToken(): bool;

    /**
     * Get the current authentication status and token information
     *
     * @return array Token status information
     */
    public function getAuthStatus(): array;

    /**
     * Get the currently authenticated user information
     *
     * @return array|null User information or null if not authenticated
     */
    public function getCurrentUser(): ?array;
}
