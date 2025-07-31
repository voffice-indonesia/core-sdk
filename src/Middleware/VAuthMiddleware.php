<?php

namespace VoxDev\Core\Middleware;

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
        // Skip authentication for logout routes to prevent token refresh during logout
        if ($request->is('*/vauth/logout') || $request->is('*/auth/oauth/logout')) {
            return $next($request);
        }

        // If user is already authenticated in session, allow request
        if (session()->has('vauth_user')) {
            return $next($request);
        }

        // Get valid token (will refresh automatically if needed)
        $token = VAuthHelper::getValidToken();

        if (! $token) {
            // Store intended URL for redirect after login
            session(['url.intended' => $request->url()]);

            // No valid token available, redirect to login
            return redirect(config('core.login_url', '/vauth/redirect'));
        }

        // Get user info using the valid token
        $userInfo = VAuthHelper::getUserInfo();

        if (! $userInfo) {
            // API call failed even with valid token, clear cookies and redirect
            VAuthHelper::clearAuthCookies();
            session(['url.intended' => $request->url()]);

            return redirect(config('core.login_url', '/vauth/redirect'));
        }

        // Store the user data in the session for controllers to use
        session(['vauth_user' => $userInfo]);

        // Login the user with the core guard for Filament
        $coreUser = new CoreAuthUser($userInfo);
        Auth::guard(config('core.guard_name', 'core'))->login($coreUser);

        return $next($request);
    }
}
