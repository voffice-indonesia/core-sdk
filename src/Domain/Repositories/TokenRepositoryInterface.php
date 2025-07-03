<?php

namespace VoxDev\Core\Domain\Repositories;

use VoxDev\Core\Domain\ValueObjects\AccessToken;
use VoxDev\Core\Domain\ValueObjects\RefreshToken;
use VoxDev\Core\Domain\ValueObjects\UserId;

/**
 * Token Repository Interface
 *
 * Defines the contract for token persistence in the domain layer.
 */
interface TokenRepositoryInterface
{
    /**
     * Store access and refresh tokens for a user
     */
    public function storeTokens(UserId $userId, AccessToken $accessToken, ?RefreshToken $refreshToken = null): void;

    /**
     * Retrieve access token for a user
     */
    public function getAccessToken(UserId $userId): ?AccessToken;

    /**
     * Retrieve refresh token for a user
     */
    public function getRefreshToken(UserId $userId): ?RefreshToken;

    /**
     * Remove all tokens for a user
     */
    public function clearTokens(UserId $userId): void;

    /**
     * Check if user has valid tokens
     */
    public function hasValidTokens(UserId $userId): bool;
}
