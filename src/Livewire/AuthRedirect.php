<?php

namespace VoxDev\Core\Livewire;

use Livewire\Component;
use VoxDev\Core\Core;

class AuthRedirect extends Component
{
    public $showLoading = false;
    public $errorMessage = null;

    public function mount()
    {
        // Check if already authenticated
        if (auth()->guard(config('core.guard_name', 'core'))->check()) {
            return redirect(config('core.default_redirect_after_login', '/dashboard'));
        }
    }

    public function redirectToOAuth()
    {
        $this->showLoading = true;

        try {
            $coreService = new Core();
            $redirectUrl = $coreService->redirectUrl(request());

            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to redirect to OAuth server. Please try again.';
            $this->showLoading = false;
        }
    }

    public function render()
    {
        return view('core::livewire.auth-redirect');
    }
}
