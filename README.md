# PHP 2FA

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/php-2fa.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/php-2fa)
[![Tests](https://github.com/mrpunyapal/php-2fa/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/mrpunyapal/php-2fa/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/mrpunyapal/php-2fa/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/mrpunyapal/php-2fa/actions/workflows/phpstan.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/mrpunyapal/php-2fa.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/php-2fa)

Framework-agnostic Two-Factor Authentication (TOTP) actions for PHP. Works with any authenticator app (Google Authenticator, Authy, etc.). Optional first-party Laravel support included.

Inspired by [Laravel Fortify](https://github.com/laravel/fortify) 🫶. 

Built on top of [`pragmarx/google2fa`](https://github.com/antonioribeiro/google2fa).

## Features

- Enable / Disable / Confirm 2FA
- Verify OTP codes
- Recovery code generation, verification, and regeneration
- Confirmable 2FA flow (user must verify a code before 2FA is active)
- Framework-agnostic core — use with any PHP application
- Optional Laravel integration with service provider, config, and Eloquent trait
- AES-256-CBC encryption out of the box (`OpenSslEncryptor`)
- Bring your own encryptor via the `Encryptor` contract

## Requirements

- PHP 8.3+
- OpenSSL extension

## Installation

```bash
composer require mrpunyapal/php-2fa
```

### Laravel

The service provider is auto-discovered. Publish the config:

```bash
php artisan vendor:publish --tag="two-factor-config"
```

## Quick Start (Vanilla PHP)

### 1. Implement `TwoFactorUser` on your user entity

```php
use Mrpunyapal\Php2fa\Contracts\TwoFactorUser;
use DateTimeImmutable;

class User implements TwoFactorUser
{
    private ?string $twoFactorSecret = null;
    private ?string $twoFactorRecoveryCodes = null;
    private ?DateTimeImmutable $twoFactorConfirmedAt = null;

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }

    public function setTwoFactorSecret(?string $secret): void
    {
        $this->twoFactorSecret = $secret;
        // persist to database
    }

    public function getTwoFactorRecoveryCodes(): ?string
    {
        return $this->twoFactorRecoveryCodes;
    }

    public function setTwoFactorRecoveryCodes(?string $codes): void
    {
        $this->twoFactorRecoveryCodes = $codes;
        // persist to database
    }

    public function getTwoFactorConfirmedAt(): ?DateTimeImmutable
    {
        return $this->twoFactorConfirmedAt;
    }

    public function setTwoFactorConfirmedAt(?DateTimeImmutable $confirmedAt): void
    {
        $this->twoFactorConfirmedAt = $confirmedAt;
        // persist to database
    }
}
```

### 2. Use `TwoFactorManager`

```php
use Mrpunyapal\Php2fa\TwoFactorManager;

$manager = TwoFactorManager::create(
    issuer: 'My App',
    encryptionKey: 'your-secret-encryption-key',
);

// Enable 2FA
$setup = $manager->enable($user, 'user@example.com');
// $setup->secret      — plain text secret (show once)
// $setup->qrCodeUrl   — otpauth:// URL (render as QR code)
// $setup->recoveryCodes — array of recovery codes (show once)

// Confirm 2FA (user enters code from authenticator app)
$manager->confirm($user, $otpCode);

// Verify OTP or recovery code during login
$valid = $manager->verify($user, $code);

// Regenerate recovery codes
$newCodes = $manager->regenerateRecoveryCodes($user);

// Disable 2FA
$manager->disable($user);
```

## Using Individual Actions

If you prefer dependency injection or want granular control:

```php
use Mrpunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\ConfirmTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\VerifyTwoFactorCode;
use Mrpunyapal\Php2fa\Actions\DisableTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\GenerateRecoveryCodes;
use Mrpunyapal\Php2fa\Services\TwoFactorService;
use Mrpunyapal\Php2fa\Support\OpenSslEncryptor;

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

## Laravel Usage

### Add the trait to your User model

```php
use Mrpunyapal\Php2fa\Contracts\TwoFactorUser;
use Mrpunyapal\Php2fa\Laravel\Concerns\HasTwoFactorAuthentication;

class User extends Authenticatable implements TwoFactorUser
{
    use HasTwoFactorAuthentication;
}
```

### Add the required columns

```php
Schema::table('users', function (Blueprint $table) {
    $table->text('two_factor_secret')->nullable();
    $table->text('two_factor_recovery_codes')->nullable();
    $table->timestamp('two_factor_confirmed_at')->nullable();
});
```

### Inject actions or manager

```php
use Mrpunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;

class TwoFactorController extends Controller
{
    public function store(
        Request $request,
        EnableTwoFactorAuthentication $enable,
    ) {
        $setup = $enable($request->user(), $request->user()->email);

        return response()->json([
            'qr_code_url' => $setup->qrCodeUrl,
            'recovery_codes' => $setup->recoveryCodes,
        ]);
    }
}
```

## Configuration

```php
// config/two-factor.php
return [
    'issuer' => env('TWO_FACTOR_ISSUER', config('app.name', 'My App')),
    'secret_length' => (int) env('TWO_FACTOR_SECRET_LENGTH', 32),
    'window' => (int) env('TWO_FACTOR_WINDOW', 1),
    'algorithm' => env('TWO_FACTOR_ALGORITHM', 'sha1'), // sha1, sha256, sha512
    'recovery_code_count' => (int) env('TWO_FACTOR_RECOVERY_CODE_COUNT', 8),
    'confirmable' => (bool) env('TWO_FACTOR_CONFIRMABLE', true),
];
```

## Custom Encryptor

Implement the `Encryptor` contract to use your own encryption strategy:

```php
use Mrpunyapal\Php2fa\Contracts\Encryptor;

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

Then pass it to the actions or bind it in Laravel's container.

## API Reference

### Actions

| Action | Purpose |
|---|---|
| `EnableTwoFactorAuthentication` | Generates secret + recovery codes, stores encrypted on user |
| `DisableTwoFactorAuthentication` | Clears all 2FA fields on user |
| `ConfirmTwoFactorAuthentication` | Verifies OTP code and sets confirmed timestamp |
| `VerifyTwoFactorCode` | Verifies OTP or recovery code, replaces used recovery codes |
| `GenerateRecoveryCodes` | Generates new set of recovery codes |

### Exceptions

| Exception | When |
|---|---|
| `InvalidOtpException` | OTP code verification fails during confirmation |
| `TwoFactorNotEnabledException` | Action requires 2FA to be enabled but it isn't |
| `TwoFactorAlreadyEnabledException` | 2FA is already confirmed and active |
| `EncryptionException` | Encryption or decryption operation fails |

## Testing

```bash
composer test
```

## Credits

- [Mr Punyapal](https://github.com/mrpunyapal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
