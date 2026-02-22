<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Support;

use MrPunyapal\Php2fa\Contracts\Encryptor;
use MrPunyapal\Php2fa\Exceptions\EncryptionException;

final readonly class OpenSslEncryptor implements Encryptor
{
    private const string CIPHER = 'aes-256-cbc';

    private string $key;

    public function __construct(string $key)
    {
        $this->key = hash('sha256', $key, true);
    }

    public function encrypt(string $value): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);

        $encrypted = openssl_encrypt($value, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) { // @codeCoverageIgnore
            throw EncryptionException::encryptionFailed(); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        $mac = hash_hmac('sha256', $iv.$encrypted, $this->key, true);

        return base64_encode($iv.$mac.$encrypted);
    }

    public function decrypt(string $value): string
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw EncryptionException::decryptionFailed();
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $macLength = 32;

        if (strlen($decoded) < $ivLength + $macLength) {
            throw EncryptionException::decryptionFailed();
        }

        $iv = substr($decoded, 0, $ivLength);
        $mac = substr($decoded, $ivLength, $macLength);
        $encrypted = substr($decoded, $ivLength + $macLength);

        $expectedMac = hash_hmac('sha256', $iv.$encrypted, $this->key, true);

        if (! hash_equals($expectedMac, $mac)) {
            throw EncryptionException::decryptionFailed();
        }

        $decrypted = openssl_decrypt($encrypted, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) { // @codeCoverageIgnore
            throw EncryptionException::decryptionFailed(); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        return $decrypted;
    }
}
