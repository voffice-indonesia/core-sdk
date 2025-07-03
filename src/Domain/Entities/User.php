<?php

namespace VoxDev\Core\Domain\Entities;

use VoxDev\Core\Domain\ValueObjects\Email;
use VoxDev\Core\Domain\ValueObjects\UserId;
use VoxDev\Core\Domain\ValueObjects\UserName;

/**
 * User Entity
 *
 * Represents a user in the domain layer.
 * This entity is framework-agnostic and contains only business logic.
 */
class User
{
    private UserId $id;

    private UserName $name;

    private Email $email;

    private ?string $avatar;

    private array $attributes;

    public function __construct(
        UserId $id,
        UserName $name,
        Email $email,
        ?string $avatar = null,
        array $attributes = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->avatar = $avatar;
        $this->attributes = $attributes;
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getName(): UserName
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;

        return $clone;
    }

    public function withoutAttribute(string $key): self
    {
        $clone = clone $this;
        unset($clone->attributes[$key]);

        return $clone;
    }

    public function isEqual(User $other): bool
    {
        return $this->id->equals($other->id);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name->getValue(),
            'email' => $this->email->getValue(),
            'avatar' => $this->avatar,
            'attributes' => $this->attributes,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            UserId::fromValue($data['id']),
            UserName::fromValue($data['name']),
            Email::fromValue($data['email']),
            $data['avatar'] ?? null,
            $data['attributes'] ?? []
        );
    }
}
