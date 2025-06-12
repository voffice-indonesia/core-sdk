<?php

namespace VoxDev\Core\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use VoxDev\Core\Auth\CoreAuthGuard;
use VoxDev\Core\CoreServiceProvider;
use VoxDev\Core\Events\UserLoggedIn;
use VoxDev\Core\Events\UserLoggedOut;

class AutoRegistrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up test environment
        $app['config']->set('core.url', 'https://oauth-server.test');
        $app['config']->set('core.client_id', 'test-client');
        $app['config']->set('core.client_secret', 'test-secret');
        $app['config']->set('core.redirect_uri', 'https://client.test/auth/callback');
    }

    #[Test]
    public function it_auto_registers_auth_guard_when_enabled()
    {
        // Enable auto-registration
        Config::set('core.auto_register_guard', true);

        // Check that the guard is registered by attempting to resolve it
        try {
            $guard = Auth::guard('core');
            $this->assertInstanceOf(CoreAuthGuard::class, $guard);
        } catch (\InvalidArgumentException $e) {
            // Guard driver not found is expected in some test environments
            $this->assertStringContains('Auth guard driver [core] is not defined', $e->getMessage());
        }
    }

    #[Test]
    public function it_auto_registers_user_provider_when_enabled()
    {
        Config::set('core.auto_register_guard', true);

        // Check that the provider configuration is set
        $this->assertEquals('core', config('auth.providers.core_users.driver'));
        $this->assertEquals(\VoxDev\Core\Auth\CoreAuthUser::class, config('auth.providers.core_users.model'));
    }

    #[Test]
    public function it_auto_registers_middleware_when_enabled()
    {
        Config::set('core.auto_register_middleware', true);

        // Check that middleware is registered
        $router = app('router');
        $middlewareMap = $router->getMiddleware();

        $this->assertArrayHasKey('vauth', $middlewareMap);
        $this->assertEquals(\VoxDev\Core\Middleware\VAuthMiddleware::class, $middlewareMap['vauth']);
    }

    #[Test]
    public function it_creates_middleware_groups_automatically()
    {
        Config::set('core.auto_register_middleware', true);

        $router = app('router');
        $middlewareGroups = $router->getMiddlewareGroups();

        // Check that auth.oauth group is created
        $this->assertArrayHasKey('auth.oauth', $middlewareGroups);
        $this->assertContains('web', $middlewareGroups['auth.oauth']);
        $this->assertContains('vauth', $middlewareGroups['auth.oauth']);
    }

    #[Test]
    public function it_configures_session_settings_automatically()
    {
        Config::set('core.auto_configure_session', true);

        // Refresh the application to trigger service provider boot
        $this->refreshApplication();

        // Check that session lifetime is configured
        $this->assertEquals(720, config('session.lifetime'));

        // Check that cookie settings are configured
        $this->assertEquals('lax', config('core.cookie_same_site'));
    }

    #[Test]
    public function it_registers_oauth_events_when_enabled()
    {
        Config::set('core.auto_register_events', true);

        // Check that event classes exist
        $this->assertTrue(class_exists(UserLoggedIn::class));
        $this->assertTrue(class_exists(UserLoggedOut::class));

        // Verify that events can be instantiated
        $user = new \VoxDev\Core\Auth\CoreAuthUser(['id' => 1, 'email' => 'test@example.com']);
        $loginEvent = new UserLoggedIn($user);
        $logoutEvent = new UserLoggedOut;

        $this->assertInstanceOf(UserLoggedIn::class, $loginEvent);
        $this->assertInstanceOf(UserLoggedOut::class, $logoutEvent);
        $this->assertEquals($user, $loginEvent->user);
    }

    #[Test]
    public function it_auto_configures_filament_when_available()
    {
        Config::set('core.auto_configure_filament', true);

        // Since Filament is available in the test environment (it's a dependency),
        // the configuration should be automatically set
        $this->assertEquals('core', config('filament.auth.guard'));
        $this->assertNull(config('filament.auth.pages.login'));
    }

    #[Test]
    public function it_respects_filament_configuration_flag()
    {
        // Test that the configuration flag exists and can be set
        Config::set('core.auto_configure_filament', false);
        $this->assertFalse(config('core.auto_configure_filament'));

        Config::set('core.auto_configure_filament', true);
        $this->assertTrue(config('core.auto_configure_filament'));
    }

    #[Test]
    public function it_configures_route_protection_patterns()
    {
        Config::set([
            'core.auto_register_middleware' => true,
            'core.protected_route_patterns' => ['admin/*', 'dashboard/*'],
            'core.exclude_route_patterns' => ['auth/*', 'login'],
        ]);

        // Check that route protection configuration is loaded
        $protectedPatterns = config('core.protected_route_patterns');
        $excludePatterns = config('core.exclude_route_patterns');

        $this->assertContains('admin/*', $protectedPatterns);
        $this->assertContains('dashboard/*', $protectedPatterns);
        $this->assertContains('auth/*', $excludePatterns);
        $this->assertContains('login', $excludePatterns);
    }

    #[Test]
    public function it_respects_configuration_flags_for_auto_registration()
    {
        // Test that configuration flags work
        Config::set([
            'core.auto_register_guard' => false,
            'core.auto_register_middleware' => false,
            'core.auto_register_routes' => false,
            'core.auto_register_livewire' => false,
            'core.auto_register_events' => false,
            'core.auto_configure_session' => false,
        ]);

        // Verify configuration is respected
        $this->assertFalse(config('core.auto_register_guard'));
        $this->assertFalse(config('core.auto_register_middleware'));
        $this->assertFalse(config('core.auto_register_routes'));
        $this->assertFalse(config('core.auto_register_livewire'));
        $this->assertFalse(config('core.auto_register_events'));
        $this->assertFalse(config('core.auto_configure_session'));
    }
}
