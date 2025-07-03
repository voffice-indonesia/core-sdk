<?php

namespace VoxDev\Core\Infrastructure\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;
use VoxDev\Core\Domain\Repositories\UserRepositoryInterface;
use VoxDev\Core\Domain\ValueObjects\UserId;
use VoxDev\Core\Infrastructure\Auth\AuthenticatableUser;

/**
 * Clean Architecture Auth Guard
 *
 * Laravel auth guard implementation using clean architecture principles.
 */
class CleanArchitectureGuard implements Guard
{
    use GuardHelpers;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private Session $session
    ) {}

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = $this->session->get('auth_user_id');

        if (! $userId) {
            return null;
        }

        $domainUser = $this->userRepository->findById(UserId::fromValue($userId));

        if (! $domainUser) {
            return null;
        }

        $this->user = new AuthenticatableUser($domainUser);

        return $this->user;
    }

    public function validate(array $credentials = []): bool
    {
        // OAuth validation would happen during the callback flow
        // This guard doesn't handle direct credential validation
        return false;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;

        if ($user instanceof AuthenticatableUser) {
            $this->session->put('auth_user_id', $user->getDomainUser()->getId()->getValue());
        }

        return $this;
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->forget('auth_user_id');
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function id(): mixed
    {
        $user = $this->user();

        return $user?->getAuthIdentifier();
    }
}
