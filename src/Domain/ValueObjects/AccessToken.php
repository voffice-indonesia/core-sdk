<?php

namespace VoxDev\Core\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * AccessToken Value Object
 *
 * Represents an OAuth access token in the domain layer.
 */
class AccessToken
{
    private string $token;

    private int $expiresAt;

    public function __construct(string $token, int $expiresAt)
    {
        if (empty(trim($token))) {
            throw new InvalidArgumentException('Access token cannot be empty');
        }

        if ($expiresAt <= 0) {
            throw new InvalidArgumentException('Expiration time must be positive');
        }

        $this->token = trim($token);
        $this->expiresAt = $expiresAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return time() >= $this->expiresAt;
    }

    public function isExpiringSoon(int $thresholdSeconds = 300): bool
    {
        return (time() + $thresholdSeconds) >= $this->expiresAt;
    }

    public function getRemainingLifetime(): int
    {
        return max(0, $this->expiresAt - time());
    }

    public function equals(AccessToken $other): bool
    {
        return $this->token === $other->token && $this->expiresAt === $other->expiresAt;
    }

    public function __toString(): string
    {
        return $this->token;
    }

    public static function fromTokenData(array $tokenData): self
    {
        $expiresIn = $tokenData['expires_in'] ?? 3600;
        $expiresAt = time() + $expiresIn;

        return new self($tokenData['access_token'], $expiresAt);
    }
}
