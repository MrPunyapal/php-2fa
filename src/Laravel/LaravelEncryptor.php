<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Laravel;

use Illuminate\Contracts\Encryption\StringEncrypter;
use Mrpunyapal\Php2fa\Contracts\Encryptor;

final readonly class LaravelEncryptor implements Encryptor
{
    public function __construct(
        private StringEncrypter $encrypter,
    ) {}

    public function encrypt(string $value): string
    {
        return $this->encrypter->encryptString($value);
    }

    public function decrypt(string $value): string
    {
        return $this->encrypter->decryptString($value);
    }
}
