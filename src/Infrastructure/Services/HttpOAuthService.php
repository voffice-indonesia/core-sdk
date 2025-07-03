<?php

namespace VoxDev\Core\Infrastructure\Services;

use Illuminate\Support\Facades\Http;
use VoxDev\Core\Domain\Services\OAuthServiceInterface;
use VoxDev\Core\Domain\ValueObjects\AccessToken;
use VoxDev\Core\Domain\ValueObjects\OAuthCredentials;
use VoxDev\Core\Domain\ValueObjects\RefreshToken;

/**
 * HTTP OAuth Service
 *
 * Implements OAuth operations using HTTP client.
 */
class HttpOAuthService implements OAuthServiceInterface
{
    private string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function generateAuthorizationUrl(OAuthCredentials $credentials, string $state): string
    {
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $credentials->getClientId(),
            'redirect_uri' => $credentials->getRedirectUri(),
            'scope' => $credentials->getScopesAsString(),
            'state' => $state,
            'code_challenge' => $this->generateCodeChallenge(),
            'code_challenge_method' => 'S256',
        ]);

        return "{$this->baseUrl}/oauth/authorize?{$params}";
    }

    public function exchangeCodeForTokens(OAuthCredentials $credentials, string $code): array
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $credentials->getClientId(),
            'client_secret' => $credentials->getClientSecret(),
            'redirect_uri' => $credentials->getRedirectUri(),
            'code' => $code,
            'code_verifier' => session('oauth_code_verifier'),
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to exchange code for tokens: ' . $response->body());
        }

        return $response->json();
    }

    public function refreshAccessToken(OAuthCredentials $credentials, RefreshToken $refreshToken): array
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'refresh_token',
            'client_id' => $credentials->getClientId(),
            'client_secret' => $credentials->getClientSecret(),
            'refresh_token' => $refreshToken->getToken(),
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        return $response->json();
    }

    public function revokeToken(OAuthCredentials $credentials, AccessToken $accessToken): bool
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/tokens/revoke", [
            'token' => $accessToken->getToken(),
            'client_id' => $credentials->getClientId(),
            'client_secret' => $credentials->getClientSecret(),
        ]);

        return $response->successful();
    }

    public function getUserInfo(AccessToken $accessToken): array
    {
        $response = Http::withToken($accessToken->getToken())
            ->get("{$this->baseUrl}/api/user");

        if (! $response->successful()) {
            throw new \Exception('Failed to get user info: ' . $response->body());
        }

        return $response->json();
    }

    private function generateCodeChallenge(): string
    {
        $codeVerifier = $this->generateCodeVerifier();
        session(['oauth_code_verifier' => $codeVerifier]);

        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
