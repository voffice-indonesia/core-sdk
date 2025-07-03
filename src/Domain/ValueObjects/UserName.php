<?php

namespace VoxDev\Core\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * UserName Value Object
 *
 * Represents a user name in the domain layer.
 */
class UserName
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new InvalidArgumentException('User name cannot be empty');
        }

        if (strlen($trimmed) > 255) {
            throw new InvalidArgumentException('User name cannot exceed 255 characters');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(UserName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromValue(string $value): self
    {
        return new self($value);
    }
}
