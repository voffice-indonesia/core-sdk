<?php

namespace VoxDev\Core\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * RefreshToken Value Object
 *
 * Represents an OAuth refresh token in the domain layer.
 */
class RefreshToken
{
    private string $token;

    public function __construct(string $token)
    {
        if (empty(trim($token))) {
            throw new InvalidArgumentException('Refresh token cannot be empty');
        }

        $this->token = trim($token);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function equals(RefreshToken $other): bool
    {
        return $this->token === $other->token;
    }

    public function __toString(): string
    {
        return $this->token;
    }

    public static function fromValue(string $token): self
    {
        return new self($token);
    }
}
