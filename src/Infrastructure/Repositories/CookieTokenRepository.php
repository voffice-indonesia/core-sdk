<?php

namespace VoxDev\Core\Infrastructure\Repositories;

use VoxDev\Core\Domain\Repositories\TokenRepositoryInterface;
use VoxDev\Core\Domain\ValueObjects\AccessToken;
use VoxDev\Core\Domain\ValueObjects\RefreshToken;
use VoxDev\Core\Domain\ValueObjects\UserId;

/**
 * Cookie Token Repository
 *
 * Stores OAuth tokens in secure HTTP cookies.
 */
class CookieTokenRepository implements TokenRepositoryInterface
{
    private const ACCESS_TOKEN_COOKIE = 'vauth_access_token';

    private const REFRESH_TOKEN_COOKIE = 'vauth_refresh_token';

    private const TOKEN_EXPIRES_COOKIE = 'vauth_token_expires';

    public function storeTokens(UserId $userId, AccessToken $accessToken, ?RefreshToken $refreshToken = null): void
    {
        $this->setSecureCookie(self::ACCESS_TOKEN_COOKIE, $accessToken->getToken(), $accessToken->getExpiresAt());
        $this->setSecureCookie(self::TOKEN_EXPIRES_COOKIE, (string) $accessToken->getExpiresAt(), $accessToken->getExpiresAt());

        if ($refreshToken) {
            $this->setSecureCookie(self::REFRESH_TOKEN_COOKIE, $refreshToken->getToken(), time() + (30 * 24 * 60 * 60)); // 30 days
        }
    }

    public function getAccessToken(UserId $userId): ?AccessToken
    {
        $token = $this->getSecureCookie(self::ACCESS_TOKEN_COOKIE);
        $expiresAt = (int) $this->getSecureCookie(self::TOKEN_EXPIRES_COOKIE);

        if (! $token || ! $expiresAt) {
            return null;
        }

        return new AccessToken($token, $expiresAt);
    }

    public function getRefreshToken(UserId $userId): ?RefreshToken
    {
        $token = $this->getSecureCookie(self::REFRESH_TOKEN_COOKIE);

        if (! $token) {
            return null;
        }

        return new RefreshToken($token);
    }

    public function clearTokens(UserId $userId): void
    {
        $this->clearSecureCookie(self::ACCESS_TOKEN_COOKIE);
        $this->clearSecureCookie(self::REFRESH_TOKEN_COOKIE);
        $this->clearSecureCookie(self::TOKEN_EXPIRES_COOKIE);
    }

    public function hasValidTokens(UserId $userId): bool
    {
        $accessToken = $this->getAccessToken($userId);

        return $accessToken && ! $accessToken->isExpired();
    }

    private function setSecureCookie(string $name, string $value, int $expiresAt): void
    {
        $secure = config('core.cookie_secure', app()->environment('production'));
        $sameSite = config('core.cookie_same_site', 'lax');

        setcookie(
            $name,
            $value,
            [
                'expires' => $expiresAt,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => $sameSite,
            ]
        );
    }

    private function getSecureCookie(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    private function clearSecureCookie(string $name): void
    {
        $this->setSecureCookie($name, '', time() - 3600);
    }
}
