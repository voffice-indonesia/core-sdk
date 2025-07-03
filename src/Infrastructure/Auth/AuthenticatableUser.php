<?php

namespace VoxDev\Core\Infrastructure\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use VoxDev\Core\Domain\Entities\User;

/**
 * Authenticatable User Adapter
 *
 * Adapts domain User entity to Laravel's Authenticatable interface.
 */
class AuthenticatableUser implements Authenticatable
{
    public function __construct(
        private User $domainUser
    ) {}

    public function getDomainUser(): User
    {
        return $this->domainUser;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->domainUser->getId()->getValue();
    }

    public function getAuthPassword(): string
    {
        // OAuth doesn't use passwords
        return '';
    }

    public function getAuthPasswordName(): string
    {
        // OAuth doesn't use passwords
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        // Not implemented for OAuth
        return null;
    }

    public function setRememberToken($value): void
    {
        // Not implemented for OAuth
    }

    public function getRememberTokenName(): string
    {
        // Not implemented for OAuth
        return '';
    }

    // Additional helper methods
    public function getName(): string
    {
        return $this->domainUser->getName()->getValue();
    }

    public function getEmail(): string
    {
        return $this->domainUser->getEmail()->getValue();
    }

    public function getAvatar(): ?string
    {
        return $this->domainUser->getAvatar();
    }

    public function getAttribute(string $key): mixed
    {
        return $this->domainUser->getAttribute($key);
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'id' => $this->getAuthIdentifier(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'avatar' => $this->getAvatar(),
            default => $this->getAttribute($name)
        };
    }

    public function __isset(string $name): bool
    {
        return in_array($name, ['id', 'name', 'email', 'avatar']) ||
            $this->domainUser->hasAttribute($name);
    }
}
