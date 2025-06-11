<?php

namespace VoxDev\Core\Livewire;

use Livewire\Component;
use VoxDev\Core\Helpers\VAuthHelper;
use Illuminate\Support\Facades\Auth;

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
            $this->userName = $user->getName();
            $this->userEmail = $user->getEmail();
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
        $this->showUserMenu = !$this->showUserMenu;
    }

    public function render()
    {
        return view('core::livewire.auth-status');
    }
}
