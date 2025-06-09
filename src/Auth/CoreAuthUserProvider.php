<?php

namespace VoxDev\Core\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class CoreAuthUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $userData = session('vauth_user');

        if (!$userData || !isset($userData['id']) || $userData['id'] != $identifier) {
            return null;
        }

        return new CoreAuthUser($userData);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        // Not implemented for OAuth
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Not implemented for OAuth
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // Check if user is authenticated via session
        $userData = session('vauth_user');

        if (!$userData) {
            return null;
        }

        return new CoreAuthUser($userData);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // Since authentication is handled by OAuth, we just check if session exists
        return session()->has('vauth_user');
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Not implemented for OAuth
    }

    /**
     * Get the current authenticated user from session
     */
    public function user(): ?CoreAuthUser
    {
        $userData = session('vauth_user');

        if (!$userData) {
            return null;
        }

        return new CoreAuthUser($userData);
    }
}
