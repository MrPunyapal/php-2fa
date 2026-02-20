<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Exceptions;

use RuntimeException;

final class TwoFactorNotEnabledException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Two-factor authentication is not enabled.');
    }
}
