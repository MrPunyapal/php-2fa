<?php

declare(strict_types=1);

/**
 * Custom Encryptor using Sodium (libsodium).
 *
 * This example implements the Encryptor contract using PHP's built-in
 * Sodium extension for authenticated encryption (XChaCha20-Poly1305).
 */

use MrPunyapal\Php2fa\Contracts\Encryptor;
use MrPunyapal\Php2fa\Exceptions\EncryptionException;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\TwoFactorManager;

require __DIR__.'/../../../vendor/autoload.php';

final readonly class SodiumEncryptor implements Encryptor
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = sodium_crypto_generichash($key, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    public function encrypt(string $value): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $ciphertext = sodium_crypto_secretbox($value, $nonce, $this->key);

        if ($ciphertext === false) {
            throw EncryptionException::encryptionFailed(); // @codeCoverageIgnore
        }

        return base64_encode($nonce.$ciphertext);
    }

    public function decrypt(string $value): string
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw EncryptionException::decryptionFailed();
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);

        if ($plaintext === false) {
            throw EncryptionException::decryptionFailed();
        }

        return $plaintext;
    }
}

// --- Usage with TwoFactorManager ---

$encryptor = new SodiumEncryptor('your-secret-encryption-key');
$service = new TwoFactorService(issuer: 'My App');

$manager = new TwoFactorManager(
    service: $service,
    encryptor: $encryptor,
);

// Now use $manager as usual — all secrets are encrypted with Sodium instead of OpenSSL.

// --- Usage in Laravel ---

// In a service provider, bind your custom encryptor:
//
// $this->app->singleton(Encryptor::class, function ($app) {
//     return new SodiumEncryptor(config('app.key'));
// });
