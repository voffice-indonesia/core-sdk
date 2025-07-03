<?php

namespace VoxDev\Core\Application\DTOs;

use VoxDev\Core\Domain\ValueObjects\OAuthCredentials;

/**
 * Authentication Request DTO
 *
 * Data transfer object for authentication requests.
 */
class AuthenticationRequest
{
    private string $code;

    private OAuthCredentials $credentials;

    private ?string $state;

    public function __construct(
        string $code,
        OAuthCredentials $credentials,
        ?string $state = null
    ) {
        $this->code = $code;
        $this->credentials = $credentials;
        $this->state = $state;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCredentials(): OAuthCredentials
    {
        return $this->credentials;
    }

    public function getState(): ?string
    {
        return $this->state;
    }
}
