<?php

namespace VoxDev\Core\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use VoxDev\Core\Auth\CoreAuthUser;
use VoxDev\Core\Events\UserLoggedIn;
use VoxDev\Core\Helpers\VAuthHelper;

class CallbackController
{
    /**
     * Handle the OAuth callback from the authorization server
     */
    public function callback(Request $request)
    {
        // Check for authorization code
        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'OAuth authorization failed: '.$error]);
        }

        if (! $code) {
            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'No authorization code received']);
        }

        try {
            // Exchange authorization code for access token
            $tokenResponse = Http::asForm()->post(VAuthHelper::getTokenUrl(), [
                'grant_type' => 'authorization_code',
                'client_id' => config('core.client_id'),
                'client_secret' => config('core.client_secret'),
                'redirect_uri' => config('core.redirect_uri'),
                'code' => $code,
            ]);

            if (! $tokenResponse->successful()) {
                Log::error('OAuth token exchange failed', [
                    'status' => $tokenResponse->status(),
                    'response' => $tokenResponse->body(),
                ]);

                return redirect(config('core.login_url', '/'))
                    ->withErrors(['oauth' => 'Failed to obtain access token']);
            }

            $tokenData = $tokenResponse->json();

            if (! isset($tokenData['access_token'])) {
                return redirect(config('core.login_url', '/'))
                    ->withErrors(['oauth' => 'Invalid token response']);
            }

            // Store tokens in cookies
            VAuthHelper::setCookiesFromTokenData($tokenData);

            // Get user information
            $userResponse = Http::withToken($tokenData['access_token'])
                ->get(VAuthHelper::getUserApiUrl());

            if ($userResponse->successful()) {
                $userData = $userResponse->json();
                // Store user data in session
                session(['vauth_user' => $userData]);

                // Create CoreAuthUser instance and dispatch login event
                $user = new CoreAuthUser($userData);
                event(new UserLoggedIn($user));
            }

            // Redirect to intended URL or dashboard
            $intendedUrl = session('url.intended', config('core.default_redirect_after_login', '/dashboard'));
            session()->forget('url.intended');

            return redirect($intendedUrl)->with('success', 'Successfully logged in!');
        } catch (\Exception $e) {
            Log::error('OAuth callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'Authentication failed. Please try again.']);
        }
    }
}
