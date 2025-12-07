# Development

This section covers installation, configuration, and development practices for the Package Subscription package.

## Table of Contents

1. [Getting Started](./01-getting-started.md) - Installation and initial setup
2. [Configuration](./02-configuration.md) - Package configuration options
3. [Testing](./03-testing.md) - Testing patterns and commands

## Quick Start

```bash
# Install the package
composer require cleaniquecoders/package-subscription

# Publish and run migrations
php artisan vendor:publish --tag="package-subscription-migrations"
php artisan migrate

# Publish configuration (optional)
php artisan vendor:publish --tag="package-subscription-config"
```

## Requirements

- PHP 8.3+
- Laravel 11.x or 12.x

## Development Commands

| Command | Description |
|---------|-------------|
| `composer test` | Run all tests |
| `composer test-coverage` | Run tests with coverage |
| `composer analyse` | Run PHPStan static analysis |
| `composer format` | Fix code style with Pint |

## Next Steps

- Follow the [Getting Started](./01-getting-started.md) guide for detailed installation
- Configure the package using the [Configuration](./02-configuration.md) guide
- Learn about [Testing](./03-testing.md) your subscription logic
