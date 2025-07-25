<?php

namespace VoxDev\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * VAuth Facade
 *
 * Provides convenient static access to the VAuthService.
 *
 * @method static string redirectUrl(\Illuminate\Http\Request $request)
 * @method static array getLocations()
 * @method static array getUsers()
 * @method static array getPaginatedUsers(int $page = 1, int $perPage = 15, array $filters = [])
 * @method static array|null getUser($userId)
 * @method static array getPaginatedLocations(int $page = 1, int $perPage = 15, array $filters = [])
 * @method static array|null getLocation($locationId)
 * @method static array|null makeApiRequest(string $method, string $endpoint, array $data = [], array $headers = [])
 * @method static bool hasValidToken()
 * @method static array getAuthStatus()
 * @method static array|null getCurrentUser()
 *
 * @see \VoxDev\Core\Infrastructure\Services\VAuthService
 */
class VAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vauth.service';
    }
}
