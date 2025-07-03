<?php

namespace VoxDev\Core\Infrastructure\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use VoxDev\Core\Application\DTOs\AuthenticationRequest;
use VoxDev\Core\Application\UseCases\AuthenticateUser;
use VoxDev\Core\Domain\ValueObjects\OAuthCredentials;

/**
 * OAuth Callback Controller
 *
 * Handles OAuth callback using clean architecture principles.
 */
class OAuthCallbackController
{
    public function __construct(
        private AuthenticateUser $authenticateUser
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $code = $request->get('code');
        $error = $request->get('error');
        $state = $request->get('state');

        if ($error) {
            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'OAuth authorization failed: ' . $error]);
        }

        if (! $code) {
            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'No authorization code received']);
        }

        try {
            $credentials = new OAuthCredentials(
                config('core.client_id'),
                config('core.client_secret'),
                config('core.redirect_uri'),
                explode(' ', config('core.scopes', ''))
            );

            $authRequest = new AuthenticationRequest($code, $credentials, $state);
            $authResponse = $this->authenticateUser->execute($authRequest);

            if (! $authResponse->isSuccessful()) {
                return redirect(config('core.login_url', '/'))
                    ->withErrors(['oauth' => $authResponse->getErrorMessage()]);
            }

            // Redirect to intended URL or dashboard
            $intendedUrl = session('url.intended', config('core.default_redirect_after_login', '/dashboard'));
            session()->forget('url.intended');

            return redirect($intendedUrl)->with('success', 'Successfully logged in!');
        } catch (\Exception $e) {
            logger()->error('OAuth callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'Authentication failed. Please try again.']);
        }
    }
}
