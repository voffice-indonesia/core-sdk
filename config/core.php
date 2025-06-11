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
    'redirect_uri' => env('VAUTH_REDIRECT_URI', 'https://your-app.com/auth/callback'),
    'scopes' => env('VAUTH_SCOPES', 'user:read orders:create'),
    'login_url' => env('VAUTH_LOGIN_URL', config('app.url') . '/vauth/redirect'),


    /**
     *
     * SDK Config
     *
     */
    'guard_name'    => 'core'
];
