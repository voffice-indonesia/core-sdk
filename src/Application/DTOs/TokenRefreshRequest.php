<?php

namespace VoxDev\Core\Application\DTOs;

use VoxDev\Core\Domain\ValueObjects\OAuthCredentials;
use VoxDev\Core\Domain\ValueObjects\UserId;

/**
 * Token Refresh Request DTO
 *
 * Data transfer object for token refresh requests.
 */
class TokenRefreshRequest
{
    private UserId $userId;

    private OAuthCredentials $credentials;

    public function __construct(UserId $userId, OAuthCredentials $credentials)
    {
        $this->userId = $userId;
        $this->credentials = $credentials;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getCredentials(): OAuthCredentials
    {
        return $this->credentials;
    }
}
