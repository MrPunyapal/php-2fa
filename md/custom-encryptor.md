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