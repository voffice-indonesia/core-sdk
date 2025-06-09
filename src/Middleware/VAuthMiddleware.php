<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use VoxDev\Core\Auth\CoreAuthUser;
use VoxDev\Core\Helpers\VAuthHelper;

class VAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get valid token (will refresh automatically if needed)
        $token = VAuthHelper::getValidToken();

        if (! $token) {
            // No valid token available, redirect to login
            return redirect(config('vauth.login_url'));
        }

        // Get user info using the valid token
        $userInfo = VAuthHelper::getUserInfo();

        if (! $userInfo) {
            // API call failed even with valid token, clear cookies and redirect
            VAuthHelper::clearAuthCookies();

            return redirect(config('vauth.login_url'));
        }

        // Store the user data in the session for controllers to use
        session(['vauth_user' => $userInfo]);

        // Login the user with the vauth guard for Filament
        $vAuthUser = new CoreAuthUser($userInfo);
        Auth::guard('vauth')->login($vAuthUser);

        return $next($request);
    }
}
