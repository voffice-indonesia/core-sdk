# Filament Integration Guide

Learn how to integrate the Core SDK with Filament admin panels for seamless OAuth authentication.

## 🎯 Overview

The Core SDK automatically configures Filament panels to use OAuth authentication, replacing the default email/password login with a single OAuth redirect.

## 🚀 Quick Setup

### 1. Enable Auto-Configuration

```env
# Enable automatic Filament configuration
CORE_AUTO_CONFIGURE_FILAMENT=true
VAUTH_GUARD_NAME=admin
```

### 2. Configure Panel Provider

The package automatically configures your Filament panel, but you can customize it:

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(false) // Disable default login - OAuth will handle it
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                'vauth', // Use OAuth middleware instead of 'auth'
            ])
            ->authGuard('core') // Use Core SDK auth guard
            ->authMiddleware([
                'vauth', // OAuth authentication
            ]);
    }
}
```

## 🔧 Manual Configuration

If auto-configuration doesn't work, configure manually:

### 1. Update Panel Provider

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->login(false) // Disable default login
        ->authGuard('core') // Use OAuth guard
        ->middleware([
            // ... other middleware
            'vauth', // Add OAuth middleware
        ])
        ->authMiddleware([
            'vauth', // Use OAuth for authentication
        ]);
}
```

### 2. Create Custom Login Page

```php
<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;

class Login extends Page
{
    protected static string $view = 'filament.pages.auth.login';

    public function mount(): void
    {
        // Redirect to OAuth instead of showing login form
        redirect()->route('core.auth.redirect');
    }
}
```

### 3. Register Custom Login Page

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->login(\App\Filament\Pages\Auth\Login::class)
        // ... other configuration
}
```

## 🎨 Custom Login View

Create a custom login view that matches your design:

```blade
{{-- resources/views/filament/pages/auth/login.blade.php --}}
<x-filament-panels::layout.base :livewire="false">
    <div class="fi-simple-layout flex min-h-screen flex-col items-center">
        <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
            <main class="fi-simple-main mx-auto w-full max-w-md px-6 py-12">
                <div class="fi-simple-header mb-12 text-center">
                    <h1 class="fi-simple-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Sign in to {{ config('app.name') }}
                    </h1>
                    <p class="fi-simple-header-subheading mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Use your company account to access the admin panel
                    </p>
                </div>

                <div class="fi-simple-content">
                    <a href="{{ route('core.auth.redirect') }}"
                       class="fi-btn fi-btn-size-md fi-btn-color-primary w-full">
                        <span class="fi-btn-label">
                            Continue with OAuth
                        </span>
                    </a>
                </div>
            </main>
        </div>
    </div>
</x-filament-panels::layout.base>
```

## 🛡️ Role-Based Access

Implement role-based access control with Filament:

### 1. Create Policy

```php
<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class FilamentPolicy
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        // Check if user can access Filament
        return in_array('admin', $user->roles ?? []);
    }
}
```

### 2. Register Policy

```php
// In AuthServiceProvider
protected $policies = [
    'App\Models\User' => 'App\Policies\FilamentPolicy',
];
```

### 3. Use in Panel Provider

```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->authMiddleware([
            'vauth',
            'can:viewAny,App\Models\User', // Apply policy
        ]);
}
```

## 🔄 User Model Integration

### 1. Update User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use VoxDev\Core\Traits\HasCoreAuth;

class User extends Authenticatable
{
    use HasCoreAuth;

    // Filament requires these methods
    public function canAccessPanel(Panel $panel): bool
    {
        // Get user data from OAuth
        $coreUser = $this->getCoreUser();

        if (!$coreUser) {
            return false;
        }

        // Check if user has admin role
        return in_array('admin', $coreUser->roles ?? []);
    }

    public function getFilamentName(): string
    {
        $coreUser = $this->getCoreUser();
        return $coreUser->name ?? 'Unknown User';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $coreUser = $this->getCoreUser();
        return $coreUser->avatar_url ?? null;
    }
}
```

### 2. Create User Resource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use VoxDev\Core\Facades\VAuth;

class UserResource extends Resource
{
    protected static ?string $model = null; // We'll use API data

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => collect(VAuth::getUsers())) // Use OAuth API
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
```

## 📊 Dashboard Widgets

Create widgets that use OAuth API data:

### 1. User Stats Widget

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use VoxDev\Core\Facades\VAuth;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $users = VAuth::getUsers();
        $locations = VAuth::getLocations();

        return [
            Stat::make('Total Users', count($users))
                ->description('Active users in the system')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Locations', count($locations))
                ->description('Available locations')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),

            Stat::make('Admin Users', $this->countAdminUsers($users))
                ->description('Users with admin role')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
        ];
    }

    private function countAdminUsers(array $users): int
    {
        return collect($users)->filter(function ($user) {
            return in_array('admin', $user['roles'] ?? []);
        })->count();
    }
}
```

### 2. Activity Chart Widget

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use VoxDev\Core\Facades\VAuth;

class ActivityChartWidget extends ChartWidget
{
    protected static ?string $heading = 'User Activity';

    protected function getData(): array
    {
        // Get activity data from OAuth API
        $activity = VAuth::makeApiRequest('GET', '/api/user-activity');

        return [
            'datasets' => [
                [
                    'label' => 'Active Users',
                    'data' => $activity['data'] ?? [],
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $activity['labels'] ?? [],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
```

## 🎨 Customizing Authentication Flow

### 1. Custom Login Logic

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CustomLogin extends Page
{
    protected static string $view = 'filament.pages.custom-login';

    public function mount(): void
    {
        // Check if user is already authenticated
        if (Auth::guard('core')->check()) {
            redirect()->intended('/admin');
            return;
        }

        // Custom login logic here
        $this->redirectToOAuth();
    }

    protected function redirectToOAuth(): void
    {
        // Add custom parameters to OAuth redirect
        $authUrl = route('core.auth.redirect', [
            'intended' => '/admin',
            'source' => 'filament',
        ]);

        redirect($authUrl);
    }
}
```

### 2. Post-Login Processing

```php
<?php

namespace App\Listeners;

use VoxDev\Core\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;

class FilamentLoginListener
{
    public function handle(UserLoggedIn $event): void
    {
        $user = $event->user;

        // Log Filament access
        if (request()->is('admin/*')) {
            Log::info('User accessed Filament panel', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'timestamp' => now(),
            ]);
        }

        // Sync user data with local database if needed
        $this->syncUserData($user);
    }

    private function syncUserData($user): void
    {
        // Sync OAuth user data to local database
        \App\Models\User::updateOrCreate(
            ['oauth_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles ?? [],
                'last_login' => now(),
            ]
        );
    }
}
```

## 🔧 Multi-Panel Configuration

Configure multiple Filament panels with different access levels:

### 1. Admin Panel

```php
// AdminPanelProvider
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->authGuard('core')
        ->authMiddleware(['vauth', 'role:admin']);
}
```

### 2. User Panel

```php
// UserPanelProvider
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('user')
        ->path('dashboard')
        ->authGuard('core')
        ->authMiddleware(['vauth']);
}
```

### 3. Role Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        $user = Auth::guard('core')->user();

        if (!$user || !in_array($role, $user->roles ?? [])) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
}
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Login loops in Filament
```php
// Ensure login is disabled
public function panel(Panel $panel): Panel
{
    return $panel->login(false);
}
```

#### 2. User not authenticated
```php
// Check guard configuration
public function panel(Panel $panel): Panel
{
    return $panel->authGuard('core'); // Must match OAuth guard
}
```

#### 3. Access denied errors
```php
// Implement canAccessPanel method
public function canAccessPanel(Panel $panel): bool
{
    return $this->getCoreUserAttribute('role') === 'admin';
}
```

## 🔗 Next Steps

- [Basic Usage](basic-usage.md) - Core package usage
- [Middleware Guide](middleware.md) - Route protection
- [VAuth Service](vauth-service.md) - API integration
- [Troubleshooting](../troubleshooting.md) - Common issues
