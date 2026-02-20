<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Contracts;

interface Encryptor
{
    public function encrypt(string $value): string;

    public function decrypt(string $value): string;
}
