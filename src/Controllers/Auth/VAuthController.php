<?php

namespace VoxDev\Core\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VoxDev\Core\Facades\VAuth;
use VoxDev\Core\Infrastructure\Services\VAuthService;

/**
 * VAuth API Controller
 *
 * Provides API endpoints for VAuth service functionality.
 * This controller demonstrates how to use the VAuthService
 * in your Laravel applications.
 */
class VAuthController extends Controller
{
    protected VAuthService $vAuthService;

    public function __construct(VAuthService $vAuthService)
    {
        $this->vAuthService = $vAuthService;
    }

    /**
     * Get OAuth authorization redirect URL
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAuthUrl(Request $request): JsonResponse
    {
        try {
            $authUrl = $this->vAuthService->redirectUrl($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'auth_url' => $authUrl
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate authorization URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all locations
     *
     * @return JsonResponse
     */
    public function getLocations(): JsonResponse
    {
        try {
            $locations = $this->vAuthService->getLocations();

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated locations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaginatedLocations(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['search', 'status', 'type']);

            $locations = $this->vAuthService->getPaginatedLocations($page, $perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch paginated locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific location by ID
     *
     * @param int|string $locationId
     * @return JsonResponse
     */
    public function getLocation($locationId): JsonResponse
    {
        try {
            $location = $this->vAuthService->getLocation($locationId);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $location
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users
     *
     * @return JsonResponse
     */
    public function getUsers(): JsonResponse
    {
        try {
            $users = $this->vAuthService->getUsers();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaginatedUsers(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['search', 'role', 'status']);

            $users = $this->vAuthService->getPaginatedUsers($page, $perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch paginated users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user by ID
     *
     * @param int|string $userId
     * @return JsonResponse
     */
    public function getUser($userId): JsonResponse
    {
        try {
            $user = $this->vAuthService->getUser($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     *
     * @return JsonResponse
     */
    public function getCurrentUser(): JsonResponse
    {
        try {
            $user = $this->vAuthService->getCurrentUser();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authenticated user found'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authentication status
     *
     * @return JsonResponse
     */
    public function getAuthStatus(): JsonResponse
    {
        try {
            $status = $this->vAuthService->getAuthStatus();

            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch auth status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make a custom API request (example using facade)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function customApiRequest(Request $request): JsonResponse
    {
        try {
            $method = $request->get('method', 'GET');
            $endpoint = $request->get('endpoint');
            $data = $request->get('data', []);
            $headers = $request->get('headers', []);

            if (!$endpoint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint is required'
                ], 400);
            }

            // Example using the facade
            $result = VAuth::makeApiRequest($method, $endpoint, $data, $headers);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'API request failed'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to make API request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
