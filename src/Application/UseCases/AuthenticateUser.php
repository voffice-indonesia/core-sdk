<?php

namespace VoxDev\Core\Application\UseCases;

use VoxDev\Core\Application\DTOs\AuthenticationRequest;
use VoxDev\Core\Application\DTOs\AuthenticationResponse;
use VoxDev\Core\Domain\Entities\User;
use VoxDev\Core\Domain\Repositories\TokenRepositoryInterface;
use VoxDev\Core\Domain\Repositories\UserRepositoryInterface;
use VoxDev\Core\Domain\Services\OAuthServiceInterface;
use VoxDev\Core\Domain\ValueObjects\AccessToken;
use VoxDev\Core\Domain\ValueObjects\RefreshToken;

/**
 * Authenticate User Use Case
 *
 * Handles the complete OAuth authentication flow.
 */
class AuthenticateUser
{
    public function __construct(
        private OAuthServiceInterface $oAuthService,
        private UserRepositoryInterface $userRepository,
        private TokenRepositoryInterface $tokenRepository
    ) {}

    public function execute(AuthenticationRequest $request): AuthenticationResponse
    {
        try {
            // Exchange authorization code for tokens
            $tokenData = $this->oAuthService->exchangeCodeForTokens(
                $request->getCredentials(),
                $request->getCode()
            );

            // Create token value objects
            $accessToken = AccessToken::fromTokenData($tokenData);
            $refreshToken = isset($tokenData['refresh_token'])
                ? RefreshToken::fromValue($tokenData['refresh_token'])
                : null;

            // Get user information
            $userInfo = $this->oAuthService->getUserInfo($accessToken);

            // Create user entity
            $user = User::fromArray($userInfo);

            // Store user and tokens
            $this->userRepository->save($user);
            $this->tokenRepository->storeTokens($user->getId(), $accessToken, $refreshToken);

            return AuthenticationResponse::success($user, $accessToken);
        } catch (\Exception $e) {
            return AuthenticationResponse::failure($e->getMessage());
        }
    }
}
