<?php

namespace VoxDev\Core\Domain\Services;

use VoxDev\Core\Domain\ValueObjects\AccessToken;
use VoxDev\Core\Domain\ValueObjects\OAuthCredentials;
use VoxDev\Core\Domain\ValueObjects\RefreshToken;

/**
 * OAuth Service Interface
 *
 * Defines the contract for OAuth operations in the domain layer.
 */
interface OAuthServiceInterface
{
    /**
     * Generate authorization URL for OAuth flow
     */
    public function generateAuthorizationUrl(OAuthCredentials $credentials, string $state): string;

    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForTokens(OAuthCredentials $credentials, string $code): array;

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(OAuthCredentials $credentials, RefreshToken $refreshToken): array;

    /**
     * Revoke access token
     */
    public function revokeToken(OAuthCredentials $credentials, AccessToken $accessToken): bool;

    /**
     * Get user information using access token
     */
    public function getUserInfo(AccessToken $accessToken): array;
}
