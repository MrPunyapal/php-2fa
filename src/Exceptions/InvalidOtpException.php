<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Exceptions;

use RuntimeException;

final class InvalidOtpException extends RuntimeException
{
    public static function create(): self
    {
        return new self('The provided one-time password is invalid.');
    }
}
