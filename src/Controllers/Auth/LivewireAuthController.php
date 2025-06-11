<?php

namespace VoxDev\Core\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LivewireAuthController
{
    /**
     * Show the OAuth login page
     */
    public function login(): View|RedirectResponse
    {
        // Check if already authenticated
        if (auth()->guard(config('core.guard_name', 'core'))->check()) {
            return redirect(config('core.default_redirect_after_login', '/dashboard'));
        }

        return view('core::auth.login');
    }

    /**
     * Show the OAuth callback processing page
     */
    public function callbackUi(Request $request): View|RedirectResponse
    {
        // If there's an error or code, show the callback processing page
        if ($request->has(['code', 'error'])) {
            return view('core::auth.callback');
        }

        // Otherwise redirect to login
        return redirect()->route('oauth.login');
    }

    /**
     * Show the sample dashboard
     */
    public function dashboard(): View
    {
        return view('core::dashboard');
    }
}
