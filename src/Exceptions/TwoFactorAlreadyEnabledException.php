<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Exceptions;

use RuntimeException;

final class TwoFactorAlreadyEnabledException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Two-factor authentication is already confirmed and enabled.');
    }
}
