<?php

namespace VoxDev\Core\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * UserId Value Object
 *
 * Represents a user identifier in the domain layer.
 */
class UserId
{
    private string|int $value;

    public function __construct(string|int $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('User ID cannot be empty');
        }

        $this->value = $value;
    }

    public function getValue(): string|int
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public static function fromValue(string|int $value): self
    {
        return new self($value);
    }
}
