# Core SDK Livewire Integration Guide

This guide shows how to use and customize the Core SDK's Livewire components for OAuth authentication.

## Quick Setup

1. **Install the package:**
```bash
composer require voffice-indonesia/core-sdk
```

2. **Run setup:**
```bash
php artisan core:setup
```

3. **Configure your .env:**
```env
VAUTH_URL=https://your-oauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/vauth/callback
```

## Using Built-in Components

### 1. Login Page Component
```blade
{{-- Use anywhere in your app --}}
<livewire:core-auth-redirect />

{{-- Or in a layout --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <livewire:core-auth-redirect />
    </div>
@endsection
```

### 2. Authentication Status Component
```blade
{{-- In your navigation --}}
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <h1>{{ config('app.name') }}</h1>
            </div>
            <div class="flex items-center">
                <livewire:core-auth-status />
            </div>
        </div>
    </div>
</nav>
```

### 3. Callback Processing Component
```blade
{{-- For callback processing page --}}
<livewire:core-auth-callback />
```

## Publishing and Customization

### Publish Views
```bash
# Publish all views for customization
php artisan vendor:publish --tag=core-sdk-views

# Publish just page templates
php artisan vendor:publish --tag=core-sdk-pages

# Publish Livewire components
php artisan vendor:publish --tag=core-sdk-livewire
```

### Customize Views
After publishing, customize the views in:
- `resources/views/vendor/core/livewire/` (component views)
- `resources/views/auth/oauth-*.blade.php` (page templates)

### Customize Livewire Components
After publishing Livewire components to `app/Livewire/Core/`:

```php
<?php
// app/Livewire/Core/AuthRedirect.php

namespace App\Livewire\Core;

use Livewire\Component;
use VoxDev\Core\Core;

class AuthRedirect extends Component
{
    public $showLoading = false;
    public $errorMessage = null;
    public $customMessage = 'Welcome! Please sign in to continue.';

    // Add your custom methods
    public function redirectToOAuth()
    {
        $this->showLoading = true;

        try {
            // Add custom logic here
            $this->dispatch('analytics-track', event: 'oauth_redirect_attempt');

            $coreService = new Core();
            $redirectUrl = $coreService->redirectUrl(request());

            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to redirect. Please try again.';
            $this->showLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.core.auth-redirect'); // Your custom view
    }
}
```

## Advanced Usage

### Custom Login Flow
```php
<?php
// app/Http/Controllers/AuthController.php

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.custom-login', [
            'providers' => config('core.oauth_providers', ['default'])
        ]);
    }
}
```

```blade
{{-- resources/views/auth/custom-login.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold">Welcome Back</h1>
            <p class="text-gray-600">Choose your sign-in method</p>
        </div>

        {{-- Core SDK OAuth Component --}}
        <livewire:core-auth-redirect />

        {{-- Or custom implementation --}}
        <div class="mt-6 text-center">
            <a href="{{ route('vauth.redirect') }}"
               class="text-blue-600 hover:text-blue-500">
                Sign in with Company Account
            </a>
        </div>
    </div>
</div>
@endsection
```

### Custom Styling
```blade
{{-- Override component styling --}}
<div class="custom-auth-container">
    <livewire:core-auth-redirect />
</div>

<style>
.custom-auth-container {
    /* Your custom styles */
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
```

### Integration with Existing Authentication
```php
<?php
// In your existing auth middleware

class CustomAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        // Check Core SDK auth first
        if (auth()->guard('core')->check()) {
            return $next($request);
        }

        // Fall back to regular auth
        if (auth()->check()) {
            return $next($request);
        }

        // Redirect to OAuth login
        return redirect()->route('oauth.login');
    }
}
```

## Available Routes

After installation, these routes are available:

- `GET /oauth/login` - Livewire login page
- `GET /oauth/callback-ui` - Livewire callback processing
- `GET /oauth/dashboard` - Sample dashboard (protected)
- `GET /vauth/redirect` - Direct OAuth redirect
- `GET /vauth/callback` - OAuth callback handler
- `POST /vauth/logout` - Logout

## Event Handling

```javascript
// Listen for Livewire events
document.addEventListener('livewire:load', function () {
    Livewire.on('oauth-redirect-start', () => {
        console.log('OAuth redirect initiated');
    });

    Livewire.on('oauth-error', (data) => {
        console.error('OAuth error:', data.message);
    });
});
```

## Configuration Options

```php
<?php
// config/core.php

return [
    // UI Customization
    'ui' => [
        'theme' => 'default', // 'default', 'dark', 'minimal'
        'logo_url' => '/images/logo.png',
        'brand_name' => 'Your Company',
        'show_powered_by' => false,
    ],

    // Redirect behavior
    'redirect_after_login' => '/dashboard',
    'redirect_after_logout' => '/',

    // Security
    'csrf_protection' => true,
    'rate_limiting' => true,
];
```

This comprehensive setup allows you to use the Core SDK's OAuth functionality with full customization capabilities while maintaining the simplicity of the plug-and-play approach.
