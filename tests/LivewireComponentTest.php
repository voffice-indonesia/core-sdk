<?php

use VoxDev\Core\Livewire\AuthCallback;
use VoxDev\Core\Livewire\AuthRedirect;
use VoxDev\Core\Livewire\AuthStatus;

test('auth redirect component can be instantiated', function () {
    $component = new AuthRedirect;

    expect($component)->toBeInstanceOf(AuthRedirect::class);
    expect($component->showLoading)->toBeFalse();
    expect($component->errorMessage)->toBeNull();
});

test('auth status component can be instantiated', function () {
    $component = new AuthStatus;

    expect($component)->toBeInstanceOf(AuthStatus::class);
    expect($component->isAuthenticated)->toBeFalse();
    expect($component->showUserMenu)->toBeFalse();
});

test('auth callback component can be instantiated', function () {
    $component = new AuthCallback;

    expect($component)->toBeInstanceOf(AuthCallback::class);
    expect($component->processing)->toBeTrue();
    expect($component->success)->toBeFalse();
});

test('livewire components have correct views', function () {
    $authRedirect = new AuthRedirect;
    $authStatus = new AuthStatus;
    $authCallback = new AuthCallback;

    expect($authRedirect->render()->getName())->toBe('core::livewire.auth-redirect');
    expect($authStatus->render()->getName())->toBe('core::livewire.auth-status');
    expect($authCallback->render()->getName())->toBe('core::livewire.auth-callback');
});
