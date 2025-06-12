<?php

namespace VoxDev\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CoreSetupCommand extends Command
{
    public $signature = 'core:setup {--force : Overwrite existing configuration}';

    public $description = 'Setup the core package by publishing files and showing instructions.';

    public function handle(): int
    {
        $this->info('🚀 Setting up Core SDK...');

        $this->publishConfig();
        $this->updateEnvFile();
        $this->showConfigurationInstructions();
        $this->showUsageInstructions();

        $this->info('✅ Core SDK setup completed!');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->info('📝 Publishing configuration file...');

        $this->callSilent('vendor:publish', [
            '--provider' => 'VoxDev\Core\CoreServiceProvider',
            '--tag' => 'core-sdk-config',
            '--force' => $this->option('force'),
        ]);

        $this->line('✓ Configuration file published to config/core.php');
    }

    protected function updateEnvFile(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->warn('⚠️  .env file not found. Please create one and add the configuration manually.');

            return;
        }

        $envContent = File::get($envPath);
        $envVars = [
            'VAUTH_URL' => 'https://your-oauth-server.com',
            'VAUTH_DOMAIN' => 'your-domain.com',
            'VAUTH_CLIENT_ID' => 'your-client-id',
            'VAUTH_CLIENT_SECRET' => 'your-client-secret',
            'VAUTH_REDIRECT_URI' => config('app.url', 'https://your-app.com').'/vauth/callback',
            'VAUTH_SCOPES' => 'user:read',
        ];

        $newVars = [];
        foreach ($envVars as $key => $defaultValue) {
            if (! str_contains($envContent, $key.'=')) {
                $newVars[] = "{$key}={$defaultValue}";
            }
        }

        if (! empty($newVars)) {
            $this->info('📝 Adding environment variables...');
            $envContent .= "\n\n# Core SDK Configuration\n".implode("\n", $newVars)."\n";
            File::put($envPath, $envContent);
            $this->line('✓ Environment variables added to .env file');
        } else {
            $this->line('✓ Environment variables already exist');
        }
    }

    protected function showConfigurationInstructions(): void
    {
        $this->newLine();
        $this->info('🔧 Configuration Required:');
        $this->line('Please update the following in your .env file:');
        $this->line('');
        $this->line('VAUTH_URL=https://your-oauth-server.com');
        $this->line('VAUTH_CLIENT_ID=your-client-id');
        $this->line('VAUTH_CLIENT_SECRET=your-client-secret');
        $this->line('VAUTH_REDIRECT_URI='.config('app.url', 'https://your-app.com').'/vauth/callback');
        $this->line('');
    }

    protected function showUsageInstructions(): void
    {
        $this->info('📖 Usage Instructions:');
        $this->line('');
        $this->info('✅ Everything is auto-configured! You can start using:');
        $this->line('');
        $this->line('1. Protect routes with the vauth middleware:');
        $this->line('   Route::middleware([\'vauth\'])->group(function () {');
        $this->line('       // Your protected routes here');
        $this->line('   });');
        $this->line('');
        $this->line('2. Use the auto-registered auth guard:');
        $this->line('   Auth::guard(\'core\')->user() // Get authenticated user');
        $this->line('   @auth(\'core\') ... @endauth  // In Blade templates');
        $this->line('');
        $this->line('3. For Filament integration, update your Panel provider:');
        $this->line('   ->authGuard(\'core\')');
        $this->line('');
        $this->line('🔗 Auto-registered routes (ready to use):');
        $this->line('   - GET /vauth/redirect     (OAuth login)');
        $this->line('   - GET /vauth/callback     (OAuth callback)');
        $this->line('   - POST /vauth/logout      (User logout)');
        $this->line('   - GET /oauth/login        (Livewire login page)');
        $this->line('   - GET /oauth/callback-ui  (Livewire callback page)');
        $this->line('   - GET /oauth/dashboard    (Sample dashboard)');
        $this->line('');
        $this->line('🎭 Auto-registered Livewire Components:');
        $this->line('   - <livewire:core-auth-redirect /> (Login page)');
        $this->line('   - <livewire:core-auth-callback /> (Callback processing)');
        $this->line('   - <livewire:core-auth-status />   (User menu/status)');
        $this->line('');
        $this->line('🎨 Customization Options:');
        $this->line('   php artisan vendor:publish --tag=core-sdk-views    # Publish all views');
        $this->line('   php artisan vendor:publish --tag=core-sdk-pages    # Publish page templates');
        $this->line('   php artisan vendor:publish --tag=core-sdk-livewire # Publish Livewire components');
        $this->line('   php artisan vendor:publish --tag=core-sdk-config   # Publish configuration');
        $this->line('');
        $this->info('🚀 Ready to go! No additional configuration needed.');
    }
}
