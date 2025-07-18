<?php

namespace VoxDev\Core\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Http;
use VoxDev\Core\Events\UserLoggedOut;
use VoxDev\Core\Helpers\VAuthHelper;

class CoreAuthGuard implements Guard
{
    protected CoreAuthUserProvider $provider;

    protected Session $session;

    protected ?CoreAuthUser $user = null;

    public function __construct(CoreAuthUserProvider $provider, Session $session)
    {
        $this->provider = $provider;
        $this->session = $session;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userData = $this->session->get('vauth_user');

        if (! $userData) {
            return null;
        }

        return $this->user = new CoreAuthUser($userData);
    }

    public function id()
    {
        $user = $this->user();

        return $user ? $user->getAuthIdentifier() : null;
    }

    public function validate(array $credentials = []): bool
    {
        return $this->session->has('vauth_user');
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): Guard
    {
        $this->user = $user instanceof CoreAuthUser ? $user : null;

        return $this;
    }

    public function login(Authenticatable $user): void
    {
        $this->setUser($user);
    }

    public function logout(): void
    {
        $this->user = null;
        // Dispatch logout event
        event(new UserLoggedOut);

        // Clear authentication cookies
        VAuthHelper::clearAuthCookies();

        // Clear session data
        $this->session->forget('vauth_user');

        Http::withToken(VAuthHelper::getValidToken())
            ->post('/logout');
    }
}
