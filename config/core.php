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
    | Token Configuration
    |--------------------------------------------------------------------------
    |
    | Settings related to token handling and validation.
    |
    */
    'token_refresh_threshold' => env('VAUTH_TOKEN_REFRESH_THRESHOLD', 300), // 5 minutes in seconds
    'cookie_secure' => env('VAUTH_COOKIE_SECURE', false),
    'cookie_same_site' => env('VAUTH_COOKIE_SAME_SITE', 'lax'),
];
