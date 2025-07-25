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
    'oauth' => [
        'url' => env('VAUTH_URL', 'https://your-oauth-server.com'),
        'domain' => env('VAUTH_DOMAIN', 'your-domain.com'),
        'client_id' => env('VAUTH_CLIENT_ID'),
        'client_secret' => env('VAUTH_CLIENT_SECRET'),
        'redirect_uri' => env('VAUTH_REDIRECT_URI', 'https://your-app.com/auth/oauth/callback'),
        'scopes' => env('VAUTH_SCOPES', 'user:read'),
    ],

    // Legacy support - will be deprecated
    'url' => env('VAUTH_URL', 'https://your-oauth-server.com'),
    'domain' => env('VAUTH_DOMAIN', 'your-domain.com'),
    'client_id' => env('VAUTH_CLIENT_ID'),
    'client_secret' => env('VAUTH_CLIENT_SECRET'),
    'redirect_uri' => env('VAUTH_REDIRECT_URI', 'https://your-app.com/auth/oauth/callback'),
    'scopes' => env('VAUTH_SCOPES', 'user:read'),

    /*
    |--------------------------------------------------------------------------
    | Package Features
    |--------------------------------------------------------------------------
    |
    | Control which features are automatically enabled by the package.
    | Set to false to disable auto-registration and configure manually.
    |
    */
    'features' => [
        'auto_register_guard' => env('CORE_AUTO_REGISTER_GUARD', true),
        'auto_register_middleware' => env('CORE_AUTO_REGISTER_MIDDLEWARE', true),
        'auto_register_livewire' => env('CORE_AUTO_REGISTER_LIVEWIRE', true),
        'auto_register_routes' => env('CORE_AUTO_REGISTER_ROUTES', true),
        'auto_configure_filament' => env('CORE_AUTO_CONFIGURE_FILAMENT', true),
        'auto_configure_session' => env('CORE_AUTO_CONFIGURE_SESSION', true),
        'auto_register_events' => env('CORE_AUTO_REGISTER_EVENTS', true),
    ],

    // Legacy support - will be deprecated
    'auto_register_guard' => env('CORE_AUTO_REGISTER_GUARD', true),
    'auto_register_middleware' => env('CORE_AUTO_REGISTER_MIDDLEWARE', true),
    'auto_register_livewire' => env('CORE_AUTO_REGISTER_LIVEWIRE', true),
    'auto_register_routes' => env('CORE_AUTO_REGISTER_ROUTES', true),
    'auto_configure_filament' => env('CORE_AUTO_CONFIGURE_FILAMENT', true),
    'auto_configure_session' => env('CORE_AUTO_CONFIGURE_SESSION', true),
    'auto_register_events' => env('CORE_AUTO_REGISTER_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how OAuth routes are registered and protected.
    |
    */
    'routing' => [
        'prefix' => env('CORE_ROUTE_PREFIX', 'auth/oauth'),
        'middleware' => ['web'],
        'guard_name' => env('VAUTH_GUARD_NAME', 'core'),

        // Automatically protect these route patterns
        'protected_patterns' => [
            'admin/*',
            'dashboard/*',
            'profile/*',
            'settings/*',
        ],

        // Exclude these patterns from auto-protection
        'excluded_patterns' => [
            'auth/*',
            'login',
            'register',
            'public/*',
            'api/public/*',
        ],
    ],

    // Legacy support - will be deprecated
    'route_prefix' => env('CORE_ROUTE_PREFIX', 'auth/oauth'),
    'guard_name' => env('VAUTH_GUARD_NAME', 'core'),

    /*
    |--------------------------------------------------------------------------
    | URLs and Redirects
    |--------------------------------------------------------------------------
    |
    | Configure URLs used during the authentication flow.
    |
    */
    'urls' => [
        'login_url' => env('VAUTH_LOGIN_URL', '/auth/oauth/redirect'),
        'default_redirect_after_login' => env('VAUTH_DEFAULT_REDIRECT', '/dashboard'),
        'logout_redirect' => env('VAUTH_LOGOUT_REDIRECT', '/'),
    ],

    // Legacy support - will be deprecated
    'login_url' => env('VAUTH_LOGIN_URL', '/auth/oauth/redirect'),
    'default_redirect_after_login' => env('VAUTH_DEFAULT_REDIRECT', '/dashboard'),

    /*
    |--------------------------------------------------------------------------
    | Security & Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security settings for OAuth tokens and session handling.
    |
    */
    'security' => [
        'cookie_same_site' => env('VAUTH_COOKIE_SAME_SITE', 'lax'),
        'cookie_secure' => env('VAUTH_COOKIE_SECURE', true),
        'cookie_http_only' => env('VAUTH_COOKIE_HTTP_ONLY', true),
        'session_lifetime' => env('VAUTH_SESSION_LIFETIME', 720), // minutes
        'token_refresh_threshold' => 300, // seconds before expiry
    ],

    /*
    |--------------------------------------------------------------------------
    | Clean Architecture Configuration
    |--------------------------------------------------------------------------
    |
    | Configure clean architecture features and domain-driven design options.
    |
    */
    'clean_architecture' => [
        'enabled' => env('CORE_ENABLE_CLEAN_ARCHITECTURE', true),
        'use_domain_entities' => env('CORE_USE_DOMAIN_ENTITIES', true),
        'enable_use_cases' => env('CORE_ENABLE_USE_CASES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Integration
    |--------------------------------------------------------------------------
    |
    | Configure automatic Filament admin panel integration.
    |
    */
    'filament' => [
        'auto_configure' => env('CORE_AUTO_CONFIGURE_FILAMENT', true),
        'guard' => env('VAUTH_GUARD_NAME', 'core'),
        'disable_default_login' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Configuration
    |--------------------------------------------------------------------------
    |
    | Configure OAuth event handling and logging.
    |
    */
    'events' => [
        'log_authentication' => env('CORE_LOG_AUTH_EVENTS', true),
        'log_api_calls' => env('CORE_LOG_API_CALLS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API request settings and timeouts.
    |
    */
    'api' => [
        'timeout' => env('VAUTH_API_TIMEOUT', 30), // seconds
        'retry_attempts' => env('VAUTH_API_RETRY_ATTEMPTS', 3),
        'cache_duration' => env('VAUTH_API_CACHE_DURATION', 300), // seconds
    ],
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
