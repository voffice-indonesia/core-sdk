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
        $this->line('1. Protect routes with the vauth middleware:');
        $this->line('   Route::middleware([\'vauth\'])->group(function () {');
        $this->line('       // Your protected routes here');
        $this->line('   });');
        $this->line('');
        $this->line('2. For Filament integration, update your Panel provider:');
        $this->line('   ->authGuard(\'core\')');
        $this->line('');
        $this->line('3. Available routes:');
        $this->line('   - GET /vauth/redirect (redirects to OAuth server)');
        $this->line('   - GET /vauth/callback (handles OAuth callback)');
        $this->line('   - POST /vauth/logout (logs out user)');
        $this->line('');
        $this->line('4. Livewire Components:');
        $this->line('   - <livewire:core-auth-redirect /> (Login page component)');
        $this->line('   - <livewire:core-auth-callback /> (Callback processing component)');
        $this->line('   - <livewire:core-auth-status /> (User menu/login button component)');
        $this->line('');
        $this->line('5. Publishing Options:');
        $this->line('   php artisan vendor:publish --tag=core-sdk-views    # Publish all views');
        $this->line('   php artisan vendor:publish --tag=core-sdk-pages    # Publish page templates');
        $this->line('   php artisan vendor:publish --tag=core-sdk-livewire # Publish Livewire components');
        $this->line('');
    }
}
