<?php

namespace VoxDev\Core\Application\UseCases;

use VoxDev\Core\Application\DTOs\TokenRefreshRequest;
use VoxDev\Core\Application\DTOs\TokenRefreshResponse;
use VoxDev\Core\Domain\Repositories\TokenRepositoryInterface;
use VoxDev\Core\Domain\Repositories\UserRepositoryInterface;
use VoxDev\Core\Domain\Services\OAuthServiceInterface;
use VoxDev\Core\Domain\ValueObjects\AccessToken;
use VoxDev\Core\Domain\ValueObjects\RefreshToken;

/**
 * Refresh Token Use Case
 *
 * Handles token refresh operations.
 */
class RefreshUserToken
{
    public function __construct(
        private OAuthServiceInterface $oAuthService,
        private UserRepositoryInterface $userRepository,
        private TokenRepositoryInterface $tokenRepository
    ) {}

    public function execute(TokenRefreshRequest $request): TokenRefreshResponse
    {
        try {
            $userId = $request->getUserId();

            // Check if user exists
            if (! $this->userRepository->exists($userId)) {
                return TokenRefreshResponse::failure('User not found');
            }

            // Get current refresh token
            $refreshToken = $this->tokenRepository->getRefreshToken($userId);

            if (! $refreshToken) {
                return TokenRefreshResponse::failure('No refresh token available');
            }

            // Refresh tokens
            $tokenData = $this->oAuthService->refreshAccessToken(
                $request->getCredentials(),
                $refreshToken
            );

            // Create new token value objects
            $newAccessToken = AccessToken::fromTokenData($tokenData);
            $newRefreshToken = isset($tokenData['refresh_token'])
                ? RefreshToken::fromValue($tokenData['refresh_token'])
                : $refreshToken; // Use existing refresh token if new one not provided

            // Store updated tokens
            $this->tokenRepository->storeTokens($userId, $newAccessToken, $newRefreshToken);

            return TokenRefreshResponse::success($newAccessToken);
        } catch (\Exception $e) {
            return TokenRefreshResponse::failure($e->getMessage());
        }
    }
}
