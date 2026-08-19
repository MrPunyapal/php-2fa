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