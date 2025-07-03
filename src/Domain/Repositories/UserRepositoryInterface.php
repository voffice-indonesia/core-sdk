<?php

namespace VoxDev\Core\Domain\Repositories;

use VoxDev\Core\Domain\Entities\User;
use VoxDev\Core\Domain\ValueObjects\UserId;

/**
 * User Repository Interface
 *
 * Defines the contract for user persistence in the domain layer.
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by their ID
     */
    public function findById(UserId $id): ?User;

    /**
     * Store or update a user
     */
    public function save(User $user): void;

    /**
     * Remove a user
     */
    public function delete(UserId $id): void;

    /**
     * Check if a user exists
     */
    public function exists(UserId $id): bool;
}
