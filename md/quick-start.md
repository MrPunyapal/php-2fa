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