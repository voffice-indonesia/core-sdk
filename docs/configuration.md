# Configuration Reference

Complete configuration reference for the Core SDK package.

## 📁 Configuration Files

### Environment Variables (.env)

The package uses environment variables for most configuration:

```env
# ===================================
# OAUTH SERVER CONFIGURATION
# ===================================

# The base URL of your Laravel Passport OAuth server
VAUTH_URL=https://your-oauth-server.com

# OAuth client credentials (from Passport server)
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret

# Where users are redirected after OAuth authorization
VAUTH_REDIRECT_URI=https://your-app.com/auth/oauth/callback

# OAuth scopes to request (space or comma separated)
VAUTH_SCOPES=user:read

# ===================================
# PACKAGE BEHAVIOR
# ===================================

# Auto-register authentication guard
CORE_AUTO_REGISTER_GUARD=true

# Auto-register middleware aliases
CORE_AUTO_REGISTER_MIDDLEWARE=true

# Auto-register Livewire components
CORE_AUTO_REGISTER_LIVEWIRE=true

# Auto-register OAuth routes
CORE_AUTO_REGISTER_ROUTES=true

# Auto-configure Filament panels
CORE_AUTO_CONFIGURE_FILAMENT=true

# Auto-configure session settings
CORE_AUTO_CONFIGURE_SESSION=true

# Auto-register OAuth events
CORE_AUTO_REGISTER_EVENTS=true

# ===================================
# ROUTING CONFIGURATION
# ===================================

# Route prefix for OAuth endpoints
CORE_ROUTE_PREFIX=auth/oauth

# Guard name for authentication
VAUTH_GUARD_NAME=core

# Default redirect after successful login
VAUTH_DEFAULT_REDIRECT=/dashboard

# Login URL for redirecting unauthenticated users
VAUTH_LOGIN_URL=/auth/oauth/redirect

# ===================================
# SECURITY & SESSION
# ===================================

# Cookie SameSite policy (strict, lax, none)
VAUTH_COOKIE_SAME_SITE=lax

# Session lifetime in minutes
VAUTH_SESSION_LIFETIME=720

# Cookie security settings
VAUTH_COOKIE_SECURE=true
VAUTH_COOKIE_HTTP_ONLY=true

# Domain for cookies (leave empty for current domain)
VAUTH_DOMAIN=

# ===================================
# ADVANCED FEATURES
# ===================================

# Enable clean architecture features
CORE_ENABLE_CLEAN_ARCHITECTURE=true

# Use domain entities in clean architecture
CORE_USE_DOMAIN_ENTITIES=true

# Enable use cases pattern
CORE_ENABLE_USE_CASES=true
```

### Package Configuration (config/core.php)

For advanced customization, publish the configuration file:

```bash
php artisan vendor:publish --tag=core-sdk-config
```

This creates `config/core.php` with detailed options:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Server Configuration
    |--------------------------------------------------------------------------
    */
    'oauth' => [
        'url' => env('VAUTH_URL'),
        'domain' => env('VAUTH_DOMAIN'),
        'client_id' => env('VAUTH_CLIENT_ID'),
        'client_secret' => env('VAUTH_CLIENT_SECRET'),
        'redirect_uri' => env('VAUTH_REDIRECT_URI'),
        'scopes' => env('VAUTH_SCOPES', 'user:read'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Package Features
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | URLs and Redirects
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'login_url' => env('VAUTH_LOGIN_URL', '/auth/oauth/redirect'),
        'default_redirect_after_login' => env('VAUTH_DEFAULT_REDIRECT', '/dashboard'),
        'logout_redirect' => env('VAUTH_LOGOUT_REDIRECT', '/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Session Configuration
    |--------------------------------------------------------------------------
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
    */
    'events' => [
        'log_authentication' => env('CORE_LOG_AUTH_EVENTS', true),
        'log_api_calls' => env('CORE_LOG_API_CALLS', false),
    ],
];
```

## 🔧 Configuration Scenarios

### Basic Setup

For most applications, you only need these environment variables:

```env
VAUTH_URL=https://your-oauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/auth/oauth/callback
```

### Multi-Environment Setup

#### Development
```env
VAUTH_URL=http://localhost:8000
VAUTH_CLIENT_ID=dev-client-id
VAUTH_CLIENT_SECRET=dev-client-secret
VAUTH_REDIRECT_URI=http://localhost:3000/auth/oauth/callback
VAUTH_COOKIE_SECURE=false
```

#### Production
```env
VAUTH_URL=https://oauth.yourcompany.com
VAUTH_CLIENT_ID=prod-client-id
VAUTH_CLIENT_SECRET=prod-client-secret
VAUTH_REDIRECT_URI=https://app.yourcompany.com/auth/oauth/callback
VAUTH_COOKIE_SECURE=true
VAUTH_COOKIE_SAME_SITE=strict
```

### Advanced Security Setup

```env
# Strict security settings
VAUTH_COOKIE_SECURE=true
VAUTH_COOKIE_SAME_SITE=strict
VAUTH_COOKIE_HTTP_ONLY=true
VAUTH_SESSION_LIFETIME=480  # 8 hours
CORE_LOG_AUTH_EVENTS=true
```

### Custom Route Configuration

```env
# Custom routing
CORE_ROUTE_PREFIX=oauth
VAUTH_LOGIN_URL=/oauth/redirect
VAUTH_DEFAULT_REDIRECT=/admin/dashboard
```

### Filament Integration

```env
# For Filament admin panels
CORE_AUTO_CONFIGURE_FILAMENT=true
VAUTH_GUARD_NAME=admin
VAUTH_DEFAULT_REDIRECT=/admin
```

## 🔐 Authentication Guard Configuration

The package automatically configures an authentication guard, but you can customize it:

### In config/auth.php

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    // Core SDK guard
    'core' => [
        'driver' => 'core',
        'provider' => 'core_users',
    ],

    // Custom guard name
    'oauth' => [
        'driver' => 'core',
        'provider' => 'oauth_users',
    ],
],

'providers' => [
    'core_users' => [
        'driver' => 'core',
        'model' => VoxDev\Core\Auth\CoreAuthUser::class,
    ],

    'oauth_users' => [
        'driver' => 'core',
        'model' => VoxDev\Core\Auth\CoreAuthUser::class,
    ],
],
```

## 🛣️ Route Protection Patterns

Configure automatic route protection in `config/core.php`:

```php
'routing' => [
    // Routes matching these patterns will be automatically protected
    'protected_patterns' => [
        'admin/*',           // All admin routes
        'dashboard/*',       // All dashboard routes
        'profile/*',         // User profile routes
        'settings/*',        // Settings pages
        'api/private/*',     // Private API routes
    ],

    // Routes matching these patterns will NOT be protected
    'excluded_patterns' => [
        'auth/*',           // Authentication routes
        'login',            // Login page
        'register',         // Registration
        'public/*',         // Public pages
        'api/public/*',     // Public API
        'health-check',     // Health check endpoint
    ],
],
```

## ⚡ Performance Configuration

### Session Optimization

```env
# Optimize session for OAuth flows
SESSION_DRIVER=redis  # Use Redis for better performance
SESSION_LIFETIME=720  # Match VAUTH_SESSION_LIFETIME
```

### Caching

```env
# Enable caching for better performance
CACHE_DRIVER=redis
VAUTH_CACHE_TOKENS=true  # Cache valid tokens
VAUTH_CACHE_DURATION=3600  # Cache duration in seconds
```

## 🔍 Debugging Configuration

### Enable Debug Logging

```env
# Enable detailed logging
LOG_LEVEL=debug
CORE_LOG_AUTH_EVENTS=true
CORE_LOG_API_CALLS=true
VAUTH_DEBUG_MODE=true
```

### Testing Configuration

```env
# For testing environments
VAUTH_URL=http://localhost:8080
VAUTH_CLIENT_ID=test-client
VAUTH_CLIENT_SECRET=test-secret
VAUTH_COOKIE_SECURE=false
APP_ENV=testing
```

## 🔄 Configuration Validation

Validate your configuration:

```bash
# Check configuration
php artisan core:config:check

# Test OAuth connection
php artisan core:test:connection

# Validate environment
php artisan core:validate
```

## Next Steps

- [Basic Usage Guide](usage/basic-usage.md)
- [Middleware Configuration](usage/middleware.md)
- [VAuth Service Configuration](usage/vauth-service.md)
