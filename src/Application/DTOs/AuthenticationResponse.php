<?php

namespace VoxDev\Core\Application\DTOs;

use VoxDev\Core\Domain\Entities\User;
use VoxDev\Core\Domain\ValueObjects\AccessToken;

/**
 * Authentication Response DTO
 *
 * Data transfer object for authentication responses.
 */
class AuthenticationResponse
{
    private bool $successful;

    private ?User $user;

    private ?AccessToken $accessToken;

    private ?string $errorMessage;

    private function __construct(
        bool $successful,
        ?User $user = null,
        ?AccessToken $accessToken = null,
        ?string $errorMessage = null
    ) {
        $this->successful = $successful;
        $this->user = $user;
        $this->accessToken = $accessToken;
        $this->errorMessage = $errorMessage;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public static function success(User $user, AccessToken $accessToken): self
    {
        return new self(true, $user, $accessToken);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, null, $errorMessage);
    }
}
