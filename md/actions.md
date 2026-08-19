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