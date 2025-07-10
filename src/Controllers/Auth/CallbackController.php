<?php

namespace VoxDev\Core\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use VoxDev\Core\Auth\CoreAuthUser;
use VoxDev\Core\Core;
use VoxDev\Core\Events\UserLoggedIn;
use VoxDev\Core\Helpers\VAuthHelper;

class CallbackController
{
    /**
     * Handle the OAuth callback from the authorization server
     * Supports both PKCE and traditional Authorization Code Grant flows
     */
    public function callback(Request $request)
    {
        // Check for authorization code
        $code = $request->get('code');
        $state = $request->get('state');
        $error = $request->get('error');

        if ($error) {
            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'OAuth authorization failed: ' . $error]);
        }

        if (! $code) {
            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'No authorization code received']);
        }

        try {
            Log::debug("OAuth callback initiated", [
                'has_code' => !empty($code),
                'has_state' => !empty($state),
                'session_id' => Session::getId(),
                'all_session_keys' => array_keys(Session::all())
            ]);

            // Check if we should use PKCE (prioritize configuration, then session data)
            $configuresPKCE = config('core.use_pkce', true);
            $sessionState = Session::get('state');
            $codeVerifier = Session::get('code_verifier');

            // Use PKCE if either configured to use it OR if session has PKCE data
            $usesPKCE = $configuresPKCE || (!empty($sessionState) && !empty($codeVerifier));

            Log::debug("OAuth flow detection", [
                'configured_pkce' => $configuresPKCE,
                'has_session_state' => !empty($sessionState),
                'has_code_verifier' => !empty($codeVerifier),
                'final_uses_pkce' => $usesPKCE,
                'state_matches' => $state === $sessionState
            ]);

            if ($usesPKCE) {
                // PKCE Flow - check for duplicate/processed callback first
                $processedKey = 'oauth_processed_' . $state;
                if (Session::has($processedKey)) {
                    Log::info("Duplicate OAuth callback detected, redirecting to dashboard", [
                        'state' => $state,
                        'session_id' => Session::getId()
                    ]);

                    // For duplicate callbacks, just redirect to the intended URL or dashboard
                    $intendedUrl = session('url.intended', config('core.default_redirect_after_login', '/dashboard'));
                    return redirect($intendedUrl)->with('info', 'You are already logged in.');
                }

                // Validate state only for non-duplicate callbacks
                if ($state !== $sessionState) {
                    Log::error("OAuth state mismatch in PKCE flow", [
                        'received_state' => $state,
                        'session_state' => $sessionState,
                        'session_id' => Session::getId(),
                        'all_session_keys' => array_keys(Session::all())
                    ]);

                    // This is a real invalid state, redirect with error
                    return redirect(config('core.login_url', '/'))
                        ->withErrors(['oauth' => 'Invalid OAuth state. Please try logging in again.']);
                }

                // Use Core class for PKCE flow
                $core = new Core();
                $tokenData = $core->handleCallback($request);
            } else {
                // Traditional Authorization Code Grant flow (for Laravel Passport without PKCE)
                Log::debug("Using traditional OAuth flow with client_secret");

                $tokenResponse = Http::asForm()->post(VAuthHelper::getTokenUrl(), [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('core.client_id'),
                    'client_secret' => config('core.client_secret'),
                    'redirect_uri' => config('core.redirect_uri'),
                    'code' => $code,
                ]);

                if (! $tokenResponse->successful()) {
                    Log::error('OAuth token exchange failed (traditional flow)', [
                        'status' => $tokenResponse->status(),
                        'response' => $tokenResponse->body(),
                    ]);
                    throw new \Exception('Failed to obtain access token: ' . $tokenResponse->body());
                }

                $tokenData = $tokenResponse->json();
            }

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

            // Clear OAuth session data if using PKCE
            if ($usesPKCE) {
                Session::forget(['state', 'code_verifier']);
            }

            // Redirect to intended URL or dashboard
            $intendedUrl = session('url.intended', config('core.default_redirect_after_login', '/dashboard'));
            session()->forget('url.intended');

            return redirect($intendedUrl)->with('success', 'Successfully logged in!');
        } catch (\Exception $e) {
            Log::error('OAuth callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => Session::getId(),
                'has_code' => !empty($code),
                'has_state' => !empty($state),
                'uses_pkce' => $usesPKCE ?? false
            ]);

            // Handle specific OAuth-related errors more gracefully
            $errorMessage = $e->getMessage();
            if (
                strpos($errorMessage, 'already processed') !== false ||
                strpos($errorMessage, 'may have been completed') !== false
            ) {
                // This appears to be a duplicate/retry callback
                Log::info('Handling duplicate/retry OAuth callback gracefully', [
                    'state' => $state,
                    'session_id' => Session::getId()
                ]);

                $intendedUrl = session('url.intended', config('core.default_redirect_after_login', '/dashboard'));
                return redirect($intendedUrl)->with('info', 'Authentication was already completed.');
            }

            // For other errors, redirect to login with error message
            return redirect(config('core.login_url', '/'))
                ->withErrors(['oauth' => 'Authentication failed. Please try again.']);
        }
    }
}
