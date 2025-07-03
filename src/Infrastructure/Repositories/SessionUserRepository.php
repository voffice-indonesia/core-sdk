<?php

namespace VoxDev\Core\Infrastructure\Repositories;

use VoxDev\Core\Domain\Entities\User;
use VoxDev\Core\Domain\Repositories\UserRepositoryInterface;
use VoxDev\Core\Domain\ValueObjects\UserId;

/**
 * Session User Repository
 *
 * Stores user data in Laravel sessions.
 */
class SessionUserRepository implements UserRepositoryInterface
{
    private const SESSION_KEY = 'vauth_user';

    public function findById(UserId $id): ?User
    {
        $userData = session(self::SESSION_KEY);

        if (! $userData || $userData['id'] != $id->getValue()) {
            return null;
        }

        return User::fromArray($userData);
    }

    public function save(User $user): void
    {
        session([self::SESSION_KEY => $user->toArray()]);
    }

    public function delete(UserId $id): void
    {
        $userData = session(self::SESSION_KEY);

        if ($userData && $userData['id'] == $id->getValue()) {
            session()->forget(self::SESSION_KEY);
        }
    }

    public function exists(UserId $id): bool
    {
        $userData = session(self::SESSION_KEY);

        return $userData && $userData['id'] == $id->getValue();
    }
}
