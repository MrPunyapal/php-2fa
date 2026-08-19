# Configuration

# Configuration

The Laravel integration ships a config file at `config/two-factor.php`.

Publish it with:

```bash
php artisan vendor:publish --tag="two-factor-config"
```

## Contents

```php
<?php

// config/two-factor.php
return [
    'issuer' => env('TWO_FACTOR_ISSUER', config('app.name', 'My App')),
    'secret_length' => (int) env('TWO_FACTOR_SECRET_LENGTH', 32),
    'window' => (int) env('TWO_FACTOR_WINDOW', 1),
    'algorithm' => env('TWO_FACTOR_ALGORITHM', 'sha1'), // sha1, sha256, sha512
    'recovery_code_count' => (int) env('TWO_FACTOR_RECOVERY_CODE_COUNT', 8),
];
```

## Options

| Key | Default | Description |
| --- | --- | --- |
| `issuer` | `app.name` | The issuer shown in the authenticator app (e.g. "My App") |
| `secret_length` | `32` | Length of the generated TOTP secret |
| `window` | `1` | Allowed clock-drift tolerance in TOTP verification |
| `algorithm` | `sha1` | HMAC algorithm — `sha1`, `sha256`, or `sha512` |
| `recovery_code_count` | `8` | Number of recovery codes generated per set |

Each option can also be set through its environment variable (`TWO_FACTOR_ISSUER`, `TWO_FACTOR_SECRET_LENGTH`, ...) without touching the config file.

---

# Custom Encryptor

# Custom Encryptor

By default the package encrypts secrets and recovery codes with AES-256-CBC via `MrPunyapal\Php2fa\Support\OpenSslEncryptor`. You can plug in any encryption strategy by implementing the `Encryptor` contract.

## Implement the contract

```php
<?php

use MrPunyapal\Php2fa\Contracts\Encryptor;

class MyEncryptor implements Encryptor
{
    public function encrypt(string $value): string
    {
        // your encryption logic
    }

    public function decrypt(string $value): string
    {
        // your decryption logic
    }
}
```

## Pass it to the actions

```php
use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Services\TwoFactorService;

$service = new TwoFactorService(issuer: 'My App');
$enable = new EnableTwoFactorAuthentication($service, new MyEncryptor);

$setup = $enable($user, 'user@example.com');
```

## Bind it in the Laravel container

```php
$this->app->bind(\MrPunyapal\Php2fa\Contracts\Encryptor::class, MyEncryptor::class);
```

> See [`docs/examples/php/custom-encryptor.php`](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/php/custom-encryptor.php) for a complete Sodium (libsodium) implementation.

---

# Individual Actions

# Individual Actions

If you prefer dependency injection or granular control, the package ships standalone actions plus a small service layer.

## Using the actions directly

```php
<?php

use MrPunyapal\Php2fa\Actions\ConfirmTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\DisableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\GenerateRecoveryCodes;
use MrPunyapal\Php2fa\Actions\VerifyTwoFactorCode;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\Support\OpenSslEncryptor;

$service = new TwoFactorService(issuer: 'My App');
$encryptor = new OpenSslEncryptor('your-secret-key');

$enable = new EnableTwoFactorAuthentication($service, $encryptor);
$setup = $enable($user, 'user@example.com');

$confirm = new ConfirmTwoFactorAuthentication($service, $encryptor);
$confirm($user, $otpCode);

$verify = new VerifyTwoFactorCode($service, $encryptor);
$isValid = $verify($user, $code);

$regenerate = new GenerateRecoveryCodes($encryptor);
$codes = $regenerate($user);

$disable = new DisableTwoFactorAuthentication();
$disable($user);
```

## API reference

### Actions

| Action | Purpose |
| --- | --- |
| `EnableTwoFactorAuthentication` | Generates secret + recovery codes, stores encrypted data on the user |
| `DisableTwoFactorAuthentication` | Clears all 2FA fields on the user |
| `ConfirmTwoFactorAuthentication` | Verifies the OTP code and sets the confirmed timestamp |
| `VerifyTwoFactorCode` | Verifies an OTP or recovery code, replacing used recovery codes |
| `GenerateRecoveryCodes` | Generates a fresh set of recovery codes |

### Exceptions

| Exception | When |
| --- | --- |
| `InvalidOtpException` | OTP code verification fails during confirmation |
| `TwoFactorNotEnabledException` | An action requires 2FA to be enabled, but it isn't |
| `EncryptionException` | Encryption or decryption operation fails |

## Services and support

- `TwoFactorService` — low-level TOTP operations (issuer-aware).
- `OpenSslEncryptor` — AES-256-CBC encryption out of the box. See [Custom encryptor](custom-encryptor/) to bring your own.

---

# Installation

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

---

# Laravel Usage

# Laravel Usage

The Laravel integration adds a service provider, a config file, and an Eloquent trait.

## Add the trait to your User model

```php
<?php

use Illuminate\Foundation\Auth\User as Authenticatable;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\Laravel\Concerns\HasTwoFactorAuthentication;

class User extends Authenticatable implements TwoFactorUser
{
    use HasTwoFactorAuthentication;
}
```

## Add the required columns

Add the three 2FA columns to the `users` table:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });
    }
};
```

## Inject actions into a controller

The actions are auto-resolvable by the container:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;

class TwoFactorController extends Controller
{
    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $setup = $enable($request->user(), $request->user()->email);

        return response()->json([
            'qr_code_url' => $setup->qrCodeUrl,
            'recovery_codes' => $setup->recoveryCodes,
        ]);
    }
}
```

## Batch saves with `withoutSaving()`

By default, each setter on the `HasTwoFactorAuthentication` trait persists immediately. To batch multiple field changes into a single DB write:

```php
$user->withoutSaving(function ($user) {
    $user->setTwoFactorSecret($encrypted);
    $user->setTwoFactorRecoveryCodes($codes);
    $user->setTwoFactorConfirmedAt(null);
});
// One save() call instead of three
```

## Complete examples

Full working Laravel examples — controller, middleware, routes, blade view, and migration — are in [`docs/examples/laravel/`](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/laravel).

---

# PHP 2FA

# PHP 2FA

Framework-agnostic Two-Factor Authentication (TOTP) actions for PHP. Works with any authenticator app (Google Authenticator, Authy, etc.), with optional first-party Laravel support.

Inspired by [Laravel Fortify](https://github.com/laravel/fortify) and built on top of [`pragmarx/google2fa`](https://github.com/antonioribeiro/google2fa).

## Features

- Enable / disable / confirm 2FA
- Verify OTP codes
- Recovery code generation, verification, and regeneration
- Enable → confirm flow (a user must verify a code before 2FA is active)
- Framework-agnostic core — use it with any PHP application
- Optional Laravel integration with a service provider, config, and Eloquent trait
- AES-256-CBC encryption out of the box (`OpenSslEncryptor`)
- Bring your own encryptor via the `Encryptor` contract

## Requirements

- PHP 8.3+
- OpenSSL extension

## Getting started

- [Installation](installation/) — add the package to your project
- [Quick start (vanilla PHP)](quick-start/) — implement `TwoFactorUser` and use `TwoFactorManager`
- [Individual actions](actions/) — granular control with dependency injection
- [Laravel usage](laravel/) — trait, migration, and controller wiring
- [Configuration](configuration/) — config reference
- [Custom encryptor](custom-encryptor/) — plug in your own encryption strategy

> Full working examples are available in [`docs/examples/`](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples) — both [PHP](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/php) and [Laravel](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/laravel) variants.

---

# Quick Start (Vanilla PHP)

# Quick Start (Vanilla PHP)

The core is framework-agnostic — you need a `TwoFactorUser` entity and a `TwoFactorManager`.

## 1. Implement `TwoFactorUser` on your user entity

```php
<?php

use DateTimeImmutable;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;

class User implements TwoFactorUser
{
    private ?string $twoFactorSecret = null;
    private ?string $twoFactorRecoveryCodes = null;
    private ?DateTimeImmutable $twoFactorConfirmedAt = null;

    public function getTwoFactorSecret(): ?string { return $this->twoFactorSecret; }
    public function setTwoFactorSecret(?string $secret): void { $this->twoFactorSecret = $secret; }
    public function getTwoFactorRecoveryCodes(): ?string { return $this->twoFactorRecoveryCodes; }
    public function setTwoFactorRecoveryCodes(?string $codes): void { $this->twoFactorRecoveryCodes = $codes; }
    public function getTwoFactorConfirmedAt(): ?DateTimeImmutable { return $this->twoFactorConfirmedAt; }
    public function setTwoFactorConfirmedAt(?DateTimeImmutable $confirmedAt): void { $this->twoFactorConfirmedAt = $confirmedAt; }
}
```

> See [`docs/examples/php/setup-two-factor.php`](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/php/setup-two-factor.php) for a full PDO-backed implementation.

## 2. Use `TwoFactorManager`

```php
<?php

use MrPunyapal\Php2fa\TwoFactorManager;

$manager = TwoFactorManager::create(
    issuer: 'My App',
    encryptionKey: 'your-secret-encryption-key',
);

// Enable 2FA
$setup = $manager->enable($user, 'user@example.com');
// $setup->secret        — plain text secret (show once)
// $setup->qrCodeUrl     — otpauth:// URL (render as QR code)
// $setup->recoveryCodes — array of recovery codes (show once)

// Confirm 2FA (user enters a code from their authenticator app)
$manager->confirm($user, $otpCode);

// Verify OTP or recovery code during login
$valid = $manager->verify($user, $code);

// Regenerate recovery codes
$newCodes = $manager->regenerateRecoveryCodes($user);

// Disable 2FA
$manager->disable($user);
```

## The enable → confirm flow

2FA is not active the moment it is enabled. `enable()` generates and stores the secret; the user must then prove they own it by entering a code from their authenticator app with `confirm()`. Only then is `twoFactorConfirmedAt` set and verification enforced.

If you prefer granular control or dependency injection, see [Individual actions](actions/).
