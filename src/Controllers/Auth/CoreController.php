<?php

namespace VoxDev\Core\Controllers\Auth;

use Illuminate\Http\Request;
use VoxDev\Core\Core;
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
        // Clear authentication cookies
        VAuthHelper::clearAuthCookies();

        // Clear session data
        $request->session()->forget('vauth_user');

        // Redirect to login page or home
        return redirect(config('vauth.login_url', '/'));
    }
}
