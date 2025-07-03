<?php

namespace VoxDev\Core\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * OAuthCredentials Value Object
 *
 * Represents OAuth client credentials in the domain layer.
 */
class OAuthCredentials
{
    private string $clientId;

    private string $clientSecret;

    private string $redirectUri;

    private array $scopes;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        array $scopes = []
    ) {
        if (empty(trim($clientId))) {
            throw new InvalidArgumentException('Client ID cannot be empty');
        }

        if (empty(trim($clientSecret))) {
            throw new InvalidArgumentException('Client secret cannot be empty');
        }

        if (empty(trim($redirectUri))) {
            throw new InvalidArgumentException('Redirect URI cannot be empty');
        }

        if (! filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid redirect URI format');
        }

        $this->clientId = trim($clientId);
        $this->clientSecret = trim($clientSecret);
        $this->redirectUri = trim($redirectUri);
        $this->scopes = array_filter($scopes);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getScopesAsString(): string
    {
        return implode(' ', $this->scopes);
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }

    public function withScope(string $scope): self
    {
        if ($this->hasScope($scope)) {
            return $this;
        }

        $scopes = $this->scopes;
        $scopes[] = $scope;

        return new self(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $scopes
        );
    }

    public function withoutScope(string $scope): self
    {
        $scopes = array_filter($this->scopes, fn($s) => $s !== $scope);

        return new self(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            array_values($scopes)
        );
    }

    public function equals(OAuthCredentials $other): bool
    {
        return $this->clientId === $other->clientId &&
            $this->clientSecret === $other->clientSecret &&
            $this->redirectUri === $other->redirectUri &&
            $this->scopes === $other->scopes;
    }
}
