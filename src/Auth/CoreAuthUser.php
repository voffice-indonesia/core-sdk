<?php

namespace VoxDev\Core\Auth;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;

class CoreAuthUser implements Authenticatable, FilamentUser
{
    protected array $attributes;

    public function __construct(array $userData)
    {
        $this->attributes = $userData;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->attributes['id'] ?? null;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // Not implemented for OAuth
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Allow access since they've already authenticated via OAuth
    }

    // Magic method to access user attributes
    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __isset($key): bool
    {
        return isset($this->attributes[$key]);
    }

    // Commonly used methods for Filament
    public function getFilamentName(): string
    {
        return $this->attributes['name'] ?? $this->attributes['email'] ?? 'User';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->attributes['avatar'] ?? $this->attributes['profile_photo_url'] ?? null;
    }

    // Additional helper methods
    public function getName(): string
    {
        return $this->attributes['name'] ?? 'Unknown User';
    }

    public function getEmail(): string
    {
        return $this->attributes['email'] ?? '';
    }

    public function getId()
    {
        return $this->attributes['id'] ?? null;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    // Methods that Filament might expect
    public function getAttributeValue($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function hasAttribute($key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    // Additional methods for Laravel/Filament compatibility
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttribute($key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getRawOriginal($key = null, $default = null)
    {
        if ($key === null) {
            return $this->attributes;
        }
        return $this->attributes[$key] ?? $default;
    }

    public function getOriginal($key = null, $default = null)
    {
        return $this->getRawOriginal($key, $default);
    }

    // Model-like methods for Filament compatibility
    public function isDirty($attributes = null): bool
    {
        return false; // OAuth user is never dirty
    }

    public function isClean($attributes = null): bool
    {
        return true; // OAuth user is always clean
    }

    public function wasChanged($attributes = null): bool
    {
        return false; // OAuth user never changes
    }

    public function getChanges(): array
    {
        return []; // OAuth user has no changes
    }

    public function getDirty(): array
    {
        return []; // OAuth user is never dirty
    }

    public function getTable(): string
    {
        return 'vauth_users'; // Virtual table name
    }

    public function getConnectionName(): ?string
    {
        return null;
    }

    public function getIncrementing(): bool
    {
        return true;
    }

    public function getKeyType(): string
    {
        return 'int';
    }

    public function usesTimestamps(): bool
    {
        return false;
    }

    // For Filament's name resolution
    public function getKey()
    {
        return $this->getId();
    }

    public function getKeyName(): string
    {
        return 'id';
    }

    public function markAsRead()
    {
        // No-op for OAuth users
        return $this;
    }

    public function markAsUnread()
    {
        // No-op for OAuth users
        return $this;
    }

    // Additional Laravel model methods that Filament might expect
    public function exists(): bool
    {
        return true;
    }

    public function wasRecentlyCreated(): bool
    {
        return false;
    }

    public function fresh($with = [])
    {
        return $this;
    }

    public function refresh()
    {
        return $this;
    }

    public function replicate(?array $except = null)
    {
        return new static($this->attributes);
    }

    public function is($model): bool
    {
        return $model instanceof static && $this->getKey() === $model->getKey();
    }

    public function isNot($model): bool
    {
        return !$this->is($model);
    }

    // For debugging
    public function __toString(): string
    {
        return $this->getName();
    }
}
