# Core SDK - Laravel Passport OAuth Integration

A powerful, plug-and-play Laravel package for seamless OAuth integration with Laravel Passport servers. Built with **Clean Architecture** principles for enterprise-grade applications.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/voffice-indonesia/core-sdk.svg?style=flat-square)](https://packagist.org/packages/voffice-indonesia/core-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/voffice-indonesia/core-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/voffice-indonesia/core-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/voffice-indonesia/core-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/voffice-indonesia/core-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/voffice-indonesia/core-sdk.svg?style=flat-square)](https://packagist.org/packages/voffice-indonesia/core-sdk)

## ✨ Key Features

- 🚀 **Plug & Play**: Zero configuration, works out of the box
- 🔐 **Enterprise Security**: OAuth2 + PKCE with automatic token refresh
- 🏗️ **Clean Architecture**: SOLID principles, testable, maintainable
- 🎨 **Modern UI**: Livewire 3.0 components with Tailwind CSS
- ⚡ **Laravel Integration**: Custom guards, Filament compatible, middleware
- 🧪 **Fully Tested**: 33+ tests with comprehensive coverage

## 🚀 Quick Start

### 1. Install

```bash
composer require voffice-indonesia/core-sdk
```

### 2. Setup

```bash
php artisan core:setup
```

### 3. Configure Environment

```env
VAUTH_URL=https://your-oauth-server.com
VAUTH_CLIENT_ID=your-client-id
VAUTH_CLIENT_SECRET=your-client-secret
VAUTH_REDIRECT_URI=https://your-app.com/auth/oauth/callback
```

### 4. Protect Routes

```php
Route::middleware(['vauth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### 5. Use VAuth Service

```php
use VoxDev\Core\Facades\VAuth;

// Get users from OAuth server
$users = VAuth::getUsers();

// Get locations
$locations = VAuth::getLocations();

// Check authentication
$isAuthenticated = VAuth::hasValidToken();
```

**🎉 That's it! Your app now has OAuth authentication!**

## 📚 Documentation

📖 **[Complete Documentation](docs/README.md)**

### Getting Started
- [📦 Installation Guide](docs/installation.md) - Step-by-step setup
- [⚙️ Configuration](docs/configuration.md) - Complete configuration reference
- [🚀 Basic Usage](docs/usage/basic-usage.md) - Essential features and patterns

### Usage Guides
- [🛡️ Middleware](docs/usage/middleware.md) - Route protection and authentication
- [📡 VAuth Service](docs/usage/vauth-service.md) - API integration and data fetching
- [🎨 Livewire Components](docs/usage/livewire-components.md) - Reactive UI components
- [🔧 Filament Integration](docs/usage/filament-integration.md) - Admin panel integration

### Architecture & Advanced
- [🏗️ Clean Architecture](docs/architecture/clean-architecture.md) - Architecture principles
- [📁 Package Structure](docs/architecture/structure.md) - Code organization
- [🔌 Extending the Package](docs/architecture/extending.md) - Customization guide

### Examples & Reference
- [💻 Code Examples](docs/examples/) - Real-world implementations
- [📋 API Reference](docs/api/) - Complete API documentation
- [🚨 Troubleshooting](docs/troubleshooting.md) - Common issues and solutions

## 🎯 Use Cases

This package is perfect for:

- **Internal company applications** that need centralized authentication
- **Microservices architecture** with shared authentication service
- **Multi-tenant applications** with OAuth-based user management
- **Enterprise applications** requiring clean, maintainable code
- **Rapid prototyping** with plug-and-play OAuth integration

## 🛠️ Requirements

- **PHP**: 8.2+
- **Laravel**: 10.x, 11.x, 12.x
- **Laravel Passport OAuth Server**: Running and accessible

## 🔧 Advanced Features

### Clean Architecture

Built with clean architecture principles:
- Domain-driven design
- Dependency inversion
- SOLID principles
- Fully testable

### Auto-Configuration

Intelligent defaults that just work:
- Auth guards auto-registered
- Middleware auto-configured
- Routes auto-loaded
- Livewire components auto-registered

### Enterprise Security

Production-ready security features:
- OAuth2 with PKCE
- Automatic token refresh
- Secure cookie handling
- Session optimization

## 🧪 Testing

```bash
composer test
```

The package includes comprehensive tests:
- 33+ test cases
- Feature and unit tests
- Architecture tests
- Clean code validation

## 🔄 Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## 🤝 Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🙏 Credits

- [VOffice Indonesia](https://github.com/voffice-indonesia)
- [All Contributors](../../contributors)

---

<p align="center">
Made with ❤️ by <a href="https://github.com/voffice-indonesia">VOffice Indonesia</a>
</p>
