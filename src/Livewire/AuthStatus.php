<?php

namespace VoxDev\Core\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use VoxDev\Core\Auth\CoreAuthUser;
use VoxDev\Core\Helpers\VAuthHelper;

class AuthStatus extends Component
{
    public $isAuthenticated = false;

    public $userName = null;

    public $userEmail = null;

    public $showUserMenu = false;

    public function mount()
    {
        $this->checkAuthStatus();
    }

    public function checkAuthStatus()
    {
        $this->isAuthenticated = Auth::guard(config('core.guard_name', 'core'))->check();

        if ($this->isAuthenticated) {
            $user = Auth::guard(config('core.guard_name', 'core'))->user();
            if ($user instanceof CoreAuthUser) {
                $this->userName = $user->getName();
                $this->userEmail = $user->getEmail();
            }
        }
    }

    public function logout()
    {
        // Clear OAuth cookies
        VAuthHelper::clearAuthCookies();

        // Clear session
        session()->forget('vauth_user');

        // Log out from the guard
        Auth::guard(config('core.guard_name', 'core'))->logout();

        // Refresh the component state
        $this->checkAuthStatus();

        // Redirect to home
        return redirect('/')->with('message', 'Logged out successfully');
    }

    public function toggleUserMenu()
    {
        $this->showUserMenu = ! $this->showUserMenu;
    }

    public function render(): View
    {
        return view('core::livewire.auth-status');
    }
}
