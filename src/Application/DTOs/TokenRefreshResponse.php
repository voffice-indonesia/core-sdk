<?php

namespace VoxDev\Core\Application\DTOs;

use VoxDev\Core\Domain\ValueObjects\AccessToken;

/**
 * Token Refresh Response DTO
 *
 * Data transfer object for token refresh responses.
 */
class TokenRefreshResponse
{
    private bool $successful;

    private ?AccessToken $accessToken;

    private ?string $errorMessage;

    private function __construct(
        bool $successful,
        ?AccessToken $accessToken = null,
        ?string $errorMessage = null
    ) {
        $this->successful = $successful;
        $this->accessToken = $accessToken;
        $this->errorMessage = $errorMessage;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public static function success(AccessToken $accessToken): self
    {
        return new self(true, $accessToken);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }
}
