<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to your Laravel Passport OAuth server.
    | These settings define how your application authenticates with the
    | OAuth server and handles user authentication.
    |
    */
    'url' => env('VAUTH_URL', 'https://your-oauth-server.com'),
    'domain' => env('VAUTH_DOMAIN', 'your-domain.com'),
    'client_id' => env('VAUTH_CLIENT_ID'),
    'client_secret' => env('VAUTH_CLIENT_SECRET'),
    'redirect_uri' => env('VAUTH_REDIRECT_URI', 'https://your-app.com/vauth/callback'),
    'scopes' => env('VAUTH_SCOPES', 'user:read'),

    /*
    |--------------------------------------------------------------------------
    | Application URLs
    |--------------------------------------------------------------------------
    |
    | URLs used by the SDK for redirecting users during the authentication flow.
    |
    */
    'login_url' => env('VAUTH_LOGIN_URL', '/vauth/redirect'),
    'default_redirect_after_login' => env('VAUTH_DEFAULT_REDIRECT', '/dashboard'),

    /*
    |--------------------------------------------------------------------------
    | SDK Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to how the SDK operates within your application.
    |
    */
    'guard_name' => env('VAUTH_GUARD_NAME', 'core'),
    'route_prefix' => env('VAUTH_ROUTE_PREFIX', 'auth/oauth'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Flow Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the OAuth flow type and security settings.
    |
    */
    'use_pkce' => env('VAUTH_USE_PKCE', true), // Set to true for Laravel Passport with PKCE requirement
    'token_refresh_threshold' => env('VAUTH_TOKEN_REFRESH_THRESHOLD', 300), // 5 minutes in seconds
    'cookie_secure' => env('VAUTH_COOKIE_SECURE', false),
    'cookie_same_site' => env('VAUTH_COOKIE_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Auto-Registration Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the automatic registration and configuration
    | of authentication components for plug-and-play functionality.
    |
    */
    'auto_register_guard' => env('VAUTH_AUTO_REGISTER_GUARD', true),
    'auto_register_middleware' => env('VAUTH_AUTO_REGISTER_MIDDLEWARE', true),
    'auto_register_routes' => env('VAUTH_AUTO_REGISTER_ROUTES', true),
    'auto_register_livewire' => env('VAUTH_AUTO_REGISTER_LIVEWIRE', true),
    'auto_register_events' => env('VAUTH_AUTO_REGISTER_EVENTS', true),
    'auto_configure_filament' => env('VAUTH_AUTO_CONFIGURE_FILAMENT', true),
    'auto_configure_session' => env('VAUTH_AUTO_CONFIGURE_SESSION', true),

    /*
    |--------------------------------------------------------------------------
    | Route Protection
    |--------------------------------------------------------------------------
    |
    | Configure which routes should be automatically protected with OAuth.
    |
    */
    'protected_route_patterns' => [
        'admin/*',
        'dashboard/*',
        'profile/*',
    ],
    'exclude_route_patterns' => [
        'auth/*',
        'login',
        'register',
        'vauth/*',
    ],
];
