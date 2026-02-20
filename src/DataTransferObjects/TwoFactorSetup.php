<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\DataTransferObjects;

final readonly class TwoFactorSetup
{
    /**
     * @param  array<int, string>  $recoveryCodes
     */
    public function __construct(
        public string $secret,
        public string $qrCodeUrl,
        public array $recoveryCodes,
    ) {}
}
