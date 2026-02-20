<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Exceptions;

use RuntimeException;

final class EncryptionException extends RuntimeException
{
    public static function encryptionFailed(): self
    {
        return new self('Failed to encrypt the given value.');
    }

    public static function decryptionFailed(): self
    {
        return new self('Failed to decrypt the given value.');
    }
}
