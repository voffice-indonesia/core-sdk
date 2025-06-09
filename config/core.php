<?php

return [
    /*
    |--------------------------------------------------------------------------
    | vAuth Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for vAuth services.
    | It provides a conventional location for this type of information,
    | allowing packages to have a standard file to locate the various service credentials.
    |
    */
    'url'   => env('VAUTH_URL', 'https://your-vauth-server.com'),
    'domain'    => env('VAUTH_DOMAIN', 'your-vauth-domain.com'),
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
