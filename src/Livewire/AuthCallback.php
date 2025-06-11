<?php

namespace VoxDev\Core\Livewire;

use Livewire\Component;

class AuthCallback extends Component
{
    public $processing = true;

    public $success = false;

    public $error = null;

    public $redirectUrl = null;

    public function mount()
    {
        $this->processCallback();
    }

    private function processCallback()
    {
        try {
            $code = request()->get('code');
            $error = request()->get('error');

            if ($error) {
                $this->error = 'OAuth authorization failed: '.$error;
                $this->processing = false;

                return;
            }

            if (! $code) {
                $this->error = 'No authorization code received';
                $this->processing = false;

                return;
            }

            // This will be handled by the CallbackController
            // This component is for displaying the processing state
            $this->success = true;
            $this->redirectUrl = session('url.intended', config('core.default_redirect_after_login', '/dashboard'));

            // Auto-redirect after 2 seconds
            $this->dispatch('redirect-after-delay', url: $this->redirectUrl, delay: 2000);
        } catch (\Exception $e) {
            $this->error = 'Authentication failed. Please try again.';
        }

        $this->processing = false;
    }

    public function render()
    {
        return view('core::livewire.auth-callback');
    }
}
