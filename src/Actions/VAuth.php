<?php

namespace VoxDev\Core\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class VAuthAction
{
    public static function logout(): RedirectResponse
    {
        // Use the configured guard name, default to 'core' (vauth)
        $guard = config('core.guard_name', 'core');
        Auth::guard($guard)->logout();
        // Optionally, invalidate the session
        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
        // Redirect to the core page ('/')
        return redirect(config('core.url') . '/');
    }
}
