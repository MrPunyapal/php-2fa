# Installation

## Requirements

- PHP 8.3+
- OpenSSL extension

## Install the package

```bash
composer require mrpunyapal/php-2fa
```

## Laravel

The service provider is auto-discovered. Publish the config:

```bash
php artisan vendor:publish --tag="two-factor-config"
```

This copies the package config to `config/two-factor.php`. It is optional — the package works with its defaults without publishing.

## Next step

- Vanilla PHP: jump to the [Quick start](quick-start/).
- Laravel: jump to [Laravel usage](laravel/).