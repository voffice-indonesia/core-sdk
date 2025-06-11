<?php

namespace VoxDev\Core\Examples;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use VoxDev\Core\Helpers\VAuthHelper;

/**
 * Example controller showing how to use the Core SDK
 * This file is for reference only - copy the relevant parts to your own controllers
 */
class ExampleController
{
    /**
     * Show the dashboard (protected route)
     * Use the 'vauth' middleware on this route
     */
    public function dashboard()
    {
        // Get the authenticated user via the core guard
        $user = Auth::guard('core')->user();

        // Or get raw user data from session
        $userData = session('vauth_user');

        return view('dashboard', [
            'user' => $user,
            'userData' => $userData,
        ]);
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::guard('core')->user();

        return view('profile', [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'id' => $user->getId(),
        ]);
    }

    /**
     * Make an API call to the OAuth server
     */
    public function apiCall()
    {
        $token = VAuthHelper::getValidToken();

        if (! $token) {
            return redirect()->route('vauth.redirect');
        }

        // Make API call to your OAuth server
        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->get(config('core.url').'/api/some-endpoint');

        if ($response->successful()) {
            $data = $response->json();

            return response()->json($data);
        }

        return response()->json(['error' => 'API call failed'], 500);
    }

    /**
     * Check authentication status
     */
    public function checkAuth()
    {
        $isAuthenticated = Auth::guard('core')->check();
        $token = VAuthHelper::getValidToken();

        return response()->json([
            'authenticated' => $isAuthenticated,
            'has_valid_token' => ! is_null($token),
            'token_expired' => VAuthHelper::isTokenExpired(),
        ]);
    }

    /**
     * Manual logout
     */
    public function logout(Request $request)
    {
        // Clear OAuth cookies
        VAuthHelper::clearAuthCookies();

        // Clear session
        $request->session()->forget('vauth_user');

        // Log out from the guard
        Auth::guard('core')->logout();

        return redirect('/')->with('message', 'Logged out successfully');
    }
}
