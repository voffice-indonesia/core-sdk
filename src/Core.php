<?php

namespace VoxDev\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use VoxDev\Core\Helpers\VAuthHelper;

class Core
{
    public function redirectUrl(?Request $request = null): string
    {
        // Check if PKCE should be used (configurable, defaults to true for security)
        $usePKCE = config('core.use_pkce', true);

        // Ensure session is started and regenerate for security
        if (!Session::isStarted()) {
            Session::start();
        }

        // Regenerate session ID to prevent session fixation attacks
        Session::regenerate();

        // Generate state for security (always used)
        $state = Str::random(40);
        $this->putSessionValue('state', $state);

        $query = [
            'client_id' => config('core.client_id'),
            'redirect_uri' => config('core.redirect_uri'),
            'response_type' => 'code',
            'scope' => config('core.scopes', 'user:read'),
            'state' => $state,
        ];

        if ($usePKCE) {
            // PKCE flow - generate code challenge (required by OAuth server)
            $codeVerifier = Str::random(128);
            $this->putSessionValue('code_verifier', $codeVerifier);

            $codeChallenge = strtr(rtrim(
                base64_encode(hash('sha256', $codeVerifier, true)),
                '='
            ), '+/', '-_');

            $query['code_challenge'] = $codeChallenge;
            $query['code_challenge_method'] = 'S256';

            Log::debug("OAuth redirect initiated with PKCE", [
                'session_id' => Session::getId(),
                'state' => $state,
                'code_verifier_length' => strlen($codeVerifier),
                'code_challenge' => $codeChallenge
            ]);
        } else {
            // Traditional flow - no PKCE (only for confidential clients)
            Log::debug("OAuth redirect initiated (traditional)", [
                'session_id' => Session::getId(),
                'state' => $state,
                'uses_pkce' => false
            ]);
        }

        // Explicitly save session to ensure state and code_verifier are persisted
        // before redirecting to OAuth server (prevents race conditions)
        Session::save();

        // Verify session values were saved correctly
        $verifyState = Session::get('state');
        $verifyCodeVerifier = $usePKCE ? Session::get('code_verifier') : null;

        Log::debug("Session saved and verified before OAuth redirect", [
            'session_id' => Session::getId(),
            'session_keys' => array_keys(Session::all()),
            'state_verified' => $verifyState === $state,
            'code_verifier_verified' => $usePKCE ? ($verifyCodeVerifier !== null) : true,
            'uses_pkce' => $usePKCE
        ]);

        return VAuthHelper::getAuthorizeUrl(http_build_query($query));
    }

    /**
     * Enhanced session put with better error handling
     */
    protected function putSessionValue(string $key, $value): bool
    {
        try {
            Session::put($key, $value);
            // Note: Session::save() is called once at the end of redirectUrl()
            // to avoid multiple rapid saves which could cause race conditions

            // Log for debugging
            Log::debug("Session value stored", [
                'key' => $key,
                'value' => substr((string)$value, 0, 10) . '...', // Truncate for security
                'session_id' => Session::getId()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to store session value", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Enhanced session get with better error handling
     */
    protected function getSessionValue(string $key, $default = null)
    {
        try {
            $value = Session::get($key, $default);

            // Log for debugging
            Log::debug("Session value retrieved", [
                'key' => $key,
                'found' => $value !== $default,
                'session_id' => Session::getId()
            ]);

            return $value;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve session value", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Get code verifier from session (for debugging OAuth issues)
     */
    public function getCodeVerifier(): ?string
    {
        $codeVerifier = $this->getSessionValue('code_verifier');

        Log::debug("Code verifier retrieval", [
            'exists' => !empty($codeVerifier),
            'length' => $codeVerifier ? strlen($codeVerifier) : 0,
            'session_id' => Session::getId()
        ]);

        return $codeVerifier;
    }

    /**
     * Get state from session (for debugging OAuth issues)
     */
    public function getState(): ?string
    {
        return $this->getSessionValue('state');
    }

    public function getLocations(): array
    {
        $token = VAuthHelper::getValidToken();
        if (! $token) {
            return [];
        }
        $locations = Http::withToken($token)
            ->get(config('core.url') . '/api/locations')
            ->json();

        if (is_array($locations)) {
            return $locations;
        }

        return [];
    }

    /**
     * Handle OAuth callback and exchange code for token
     */
    public function handleCallback(Request $request): array
    {
        // Ensure session is available
        self::ensureSessionPersistence();

        $code = $request->get('code');
        $state = $request->get('state');

        // Debug session state before processing
        $debugInfo = $this->debugSessionState();
        Log::debug("OAuth callback session state", $debugInfo);

        // Check if this OAuth flow was already processed
        $processedKey = 'oauth_processed_' . $state;
        if (Session::has($processedKey)) {
            Log::info("OAuth callback already processed for this state", [
                'state' => $state,
                'session_id' => Session::getId()
            ]);

            // Return cached result if available
            $cachedResult = Session::get($processedKey);
            if ($cachedResult && is_array($cachedResult)) {
                Log::info("Returning cached OAuth result", [
                    'has_access_token' => isset($cachedResult['access_token']),
                    'session_id' => Session::getId()
                ]);
                return $cachedResult;
            }

            throw new \Exception('OAuth flow already processed');
        }

        // Verify state parameter
        $sessionState = $this->getSessionValue('state');
        if (!$state || $state !== $sessionState) {
            // Check if this might be a duplicate/retry request after successful OAuth
            Log::error("OAuth state mismatch", [
                'received_state' => $state,
                'session_state' => $sessionState,
                'session_id' => Session::getId(),
                'possible_duplicate' => !$sessionState && $state
            ]);

            // If we don't have state in session but got one from request,
            // this might be a browser retry after successful OAuth
            if (!$sessionState && $state) {
                throw new \Exception('OAuth flow may have been completed already');
            }

            throw new \Exception('Invalid state parameter');
        }

        // Get code verifier from session
        $codeVerifier = $this->getSessionValue('code_verifier');
        if (!$codeVerifier) {
            Log::error("Code verifier not found in session", [
                'session_id' => Session::getId(),
                'session_keys' => array_keys(Session::all()),
                'session_driver' => config('session.driver'),
                'has_state' => Session::has('state'),
                'has_code_verifier' => Session::has('code_verifier')
            ]);
            throw new \Exception('Code verifier not found in session');
        }

        Log::debug("OAuth callback processing", [
            'code_length' => strlen($code ?? ''),
            'state_match' => $state === $sessionState,
            'code_verifier_length' => strlen($codeVerifier),
            'session_id' => Session::getId()
        ]);

        // Exchange code for token
        try {
            $tokenPayload = [
                'grant_type' => 'authorization_code',
                'client_id' => config('core.client_id'),
                'code' => $code,
                'code_verifier' => $codeVerifier,
                'redirect_uri' => config('core.redirect_uri'),
            ];

            Log::debug("Token exchange payload", [
                'grant_type' => $tokenPayload['grant_type'],
                'client_id' => substr($tokenPayload['client_id'], 0, 8) . '...',
                'code_length' => strlen($tokenPayload['code']),
                'code_verifier_length' => strlen($tokenPayload['code_verifier']),
                'redirect_uri' => $tokenPayload['redirect_uri']
            ]);

            $response = Http::post(config('core.url') . '/oauth/token', $tokenPayload);

            if (!$response->successful()) {
                Log::error("OAuth token exchange failed", [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'headers' => $response->headers(),
                    'url' => config('core.url') . '/oauth/token'
                ]);
                throw new \Exception('Token exchange failed: ' . $response->body());
            }

            $tokenData = $response->json();

            // Mark this OAuth flow as processed and cache the result
            Session::put($processedKey, $tokenData);
            Session::put($processedKey . '_timestamp', time());

            // Clear OAuth session data AFTER caching the result
            self::clearOAuthSession();

            Log::info("OAuth token exchange successful", [
                'has_access_token' => isset($tokenData['access_token']),
                'token_type' => $tokenData['token_type'] ?? 'unknown',
                'cached_for_duplicates' => true
            ]);

            return $tokenData;
        } catch (\Exception $e) {
            Log::error("OAuth token exchange exception", [
                'error' => $e->getMessage(),
                'code_verifier_present' => $codeVerifier,
                'code_present' => !empty($code),
                'session_id' => Session::getId()
            ]);
            throw $e;
        }
    }

    /**
     * Debug session state for OAuth troubleshooting
     */
    public function debugSessionState(): array
    {
        $sessionData = [
            'session_id' => Session::getId(),
            'session_started' => Session::isStarted(),
            'has_state' => Session::has('state'),
            'has_code_verifier' => Session::has('code_verifier'),
            'state_value' => Session::get('state'),
            'code_verifier_length' => Session::get('code_verifier') ? strlen(Session::get('code_verifier')) : 0,
            'all_session_keys' => array_keys(Session::all()),
            'session_driver' => config('session.driver'),
        ];

        Log::debug("Session debug state", $sessionData);

        return $sessionData;
    }

    /**
     * Static method for VAuthHelper to retrieve code verifier
     * This ensures consistent session handling across the OAuth flow
     */
    public static function getStoredCodeVerifier(): ?string
    {
        $instance = new self();
        $codeVerifier = $instance->getSessionValue('code_verifier');

        Log::debug("Static code verifier retrieval", [
            'exists' => !empty($codeVerifier),
            'length' => $codeVerifier ? strlen($codeVerifier) : 0,
            'session_id' => Session::getId(),
            'session_started' => Session::isStarted(),
            'all_session_keys' => array_keys(Session::all())
        ]);

        return $codeVerifier;
    }

    /**
     * Static method for VAuthHelper to retrieve state
     */
    public static function getStoredState(): ?string
    {
        $instance = new self();
        $state = $instance->getSessionValue('state');

        Log::debug("Static state retrieval", [
            'exists' => !empty($state),
            'session_id' => Session::getId()
        ]);

        return $state;
    }

    /**
     * Static method for VAuthHelper to clear OAuth session data and clean up old processed entries
     */
    public static function clearOAuthSession(): void
    {
        // Clear current OAuth flow data
        Session::forget(['state', 'code_verifier']);

        // Clean up old processed OAuth entries (older than 5 minutes)
        $sessionData = Session::all();
        $now = time();
        $maxAge = 300; // 5 minutes

        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'oauth_processed_') === 0) {
                $timestampKey = $key . '_timestamp';
                $timestamp = Session::get($timestampKey, 0);

                if ($now - $timestamp > $maxAge) {
                    Session::forget($key);
                    Session::forget($timestampKey);
                    Log::debug("Cleaned up old OAuth processed entry", [
                        'key' => substr($key, 0, 20) . '...',
                        'age_seconds' => $now - $timestamp
                    ]);
                }
            }
        }

        Session::save();

        Log::debug("OAuth session data cleared", [
            'session_id' => Session::getId()
        ]);
    }

    /**
     * Static method to ensure session persistence across OAuth flow
     */
    public static function ensureSessionPersistence(): void
    {
        if (!Session::isStarted()) {
            Session::start();
        }

        // Force session save to ensure it's written to storage
        Session::save();

        Log::debug("Session persistence ensured", [
            'session_id' => Session::getId(),
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime')
        ]);
    }
}
