<?php

namespace VoxDev\Core\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use VoxDev\Core\Core;
use VoxDev\Core\Events\UserLoggedOut;
use VoxDev\Core\Helpers\VAuthHelper;

class CoreController
{
    protected $vAuthService;

    public function __construct(Core $vAuthService)
    {
        $this->vAuthService = $vAuthService;
    }

    public function redirect(Request $request)
    {
        $redirectUrl = $this->vAuthService->redirectUrl($request);

        // Redirect to the vAuth authorization URL
        return redirect()->away($redirectUrl);
    }

    public function logout(Request $request)
    {
        // Get the current token before clearing cookies
        $currentToken = VAuthHelper::getValidToken();

        // Dispatch logout event
        event(new UserLoggedOut);

        // Log out from the authentication guard
        if (\Illuminate\Support\Facades\Auth::guard(config('core.guard_name', 'core'))->check()) {
            \Illuminate\Support\Facades\Auth::guard(config('core.guard_name', 'core'))->logout();
        }

        // Clear authentication cookies first
        VAuthHelper::clearAuthCookies();

        // Clear session data
        $request->session()->forget('vauth_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Call the core app logout endpoint to revoke all tokens
        if ($currentToken) {
            try {
                Http::withToken($currentToken)
                    ->post(config('core.url') . '/api/user/logout');
            } catch (\Exception $e) {
                // Log the error but don't fail the logout process
                \Illuminate\Support\Facades\Log::warning('Failed to call core app logout endpoint', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Redirect to login page or home
        return redirect(config('core.login_url', '/'));
    }
}
