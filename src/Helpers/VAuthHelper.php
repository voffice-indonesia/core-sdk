<?php

namespace VoxDev\Core\Helpers;

class VAuthHelper
{
    public static function getAuthorizeUrl(string $query): string
    {
        return config('core.url') . '/oauth/authorize?' . $query;
    }

    public static function getTokenUrl(): string
    {
        return config('core.url') . '/oauth/token';
    }

    public static function getUserApiUrl(): string
    {
        return config('core.url') . '/api/user';
    }

    /**
     * Refresh the access token using the refresh token
     */
    public static function refreshToken(string $refreshToken): array
    {
        $response = \Illuminate\Support\Facades\Http::asForm()->post(self::getTokenUrl(), [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => config('core.client_id'),
        ]);

        return $response->json();
    }

    /**
     * Check if the current access token is expired or will expire soon
     */
    public static function isTokenExpired(): bool
    {
        $expiresIn = $_COOKIE['vauth_expires_in'] ?? null;

        if (! $expiresIn) {
            return true;
        }

        // Add buffer time (5 minutes) to refresh token before it actually expires
        $bufferTime = 300; // 5 minutes in seconds

        return (now()->timestamp + $bufferTime) >= intval($expiresIn);
    }

    /**
     * Get current access token if valid, refresh if needed
     */
    public static function getValidToken(): ?string
    {
        $accessToken = $_COOKIE['vauth_access_token'] ?? null;

        if (! $accessToken) {
            return null;
        }

        // If token is not expired, return it
        if (! self::isTokenExpired()) {
            return $accessToken;
        }

        // Try to refresh the token
        if (self::refreshTokenIfNeeded()) {
            return $_COOKIE['vauth_access_token'] ?? null;
        }

        return null;
    }

    /**
     * Attempt to refresh the token automatically if it's expired
     */
    public static function ensureValidToken(): bool
    {
        return self::getValidToken() !== null;
    }

    /**
     * Refresh token if needed and return success status
     */
    private static function refreshTokenIfNeeded(): bool
    {
        $refreshToken = $_COOKIE['vauth_refresh_token'] ?? null;

        if (! $refreshToken) {
            return false; // No refresh token available
        }

        $tokenData = self::refreshToken($refreshToken);

        if (! isset($tokenData['access_token'])) {
            // Log refresh failure for debugging
            \Illuminate\Support\Facades\Log::warning('Token refresh failed', [
                'response' => $tokenData,
                'refresh_token_exists' => true,
            ]);

            return false; // Refresh failed
        }

        // Update cookies with new token data
        self::setCookiesFromTokenData($tokenData);

        return true;
    }

    /**
     * Set authentication cookies from token data
     */
    public static function setCookiesFromTokenData(array $tokenData): void
    {
        $domain = config('core.domain');

        // Calculate expiration time in minutes for Laravel cookies
        $accessTokenExpires = isset($tokenData['expires_in'])
            ? intval($tokenData['expires_in'] / 60) // Convert seconds to minutes
            : 60; // Default 1 hour

        // Set access token cookie
        \Illuminate\Support\Facades\Cookie::queue(
            'vauth_access_token',
            $tokenData['access_token'],
            $accessTokenExpires,
            '/',
            $domain,
            false,
            false,
            false,
            'lax'
        );

        // Set token type cookie
        \Illuminate\Support\Facades\Cookie::queue(
            'vauth_token_type',
            $tokenData['token_type'] ?? 'Bearer',
            $accessTokenExpires,
            '/',
            $domain,
            false,
            false,
            false,
            'lax'
        );

        // Set refresh token if available (longer expiry - 30 days)
        if (isset($tokenData['refresh_token'])) {
            \Illuminate\Support\Facades\Cookie::queue(
                'vauth_refresh_token',
                $tokenData['refresh_token'],
                43200, // 30 days in minutes
                '/',
                $domain,
                false,
                false,
                false,
                'lax'
            );
        }

        // Set expiration timestamp if available
        if (isset($tokenData['expires_in'])) {
            \Illuminate\Support\Facades\Cookie::queue(
                'vauth_expires_in',
                now()->addSeconds($tokenData['expires_in'])->timestamp,
                $accessTokenExpires,
                '/',
                $domain,
                false,
                false,
                false,
                'lax'
            );
        }
    }

    /**
     * Clear all authentication cookies (logout)
     */
    public static function clearAuthCookies(): void
    {
        $domain = config('core.domain');

        $cookieNames = [
            'vauth_access_token',
            'vauth_token_type',
            'vauth_refresh_token',
            'vauth_expires_in',
        ];

        foreach ($cookieNames as $cookieName) {
            \Illuminate\Support\Facades\Cookie::queue(
                \Illuminate\Support\Facades\Cookie::forget($cookieName, '/', $domain)
            );
        }
    }

    /**
     * Get user information from the OAuth server using the access token
     */
    public static function getUserInfo(): ?array
    {
        $token = self::getValidToken();

        if (! $token) {
            return null;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get(self::getUserApiUrl());

            if ($response->successful()) {
                return $response->json();
            }

            // Log the error for debugging
            \Illuminate\Support\Facades\Log::warning('Failed to get user info', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception while getting user info', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get token status for debugging
     */
    public static function getTokenStatus(): array
    {
        $accessToken = $_COOKIE['vauth_access_token'] ?? null;
        $refreshToken = $_COOKIE['vauth_refresh_token'] ?? null;
        $expiresIn = $_COOKIE['vauth_expires_in'] ?? null;
        $tokenType = $_COOKIE['vauth_token_type'] ?? null;

        $status = [
            'has_access_token' => ! empty($accessToken),
            'has_refresh_token' => ! empty($refreshToken),
            'token_type' => $tokenType,
            'expires_in' => $expiresIn,
            'expires_at' => $expiresIn ? date('Y-m-d H:i:s', $expiresIn) : null,
            'current_time' => now()->format('Y-m-d H:i:s'),
            'is_expired' => self::isTokenExpired(),
            'time_until_expiry' => $expiresIn ? (intval($expiresIn) - now()->timestamp) : null,
        ];

        return $status;
    }
}
