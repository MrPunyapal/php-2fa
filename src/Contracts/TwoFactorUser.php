<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Contracts;

use DateTimeImmutable;

interface TwoFactorUser
{
    public function getTwoFactorSecret(): ?string;

    public function setTwoFactorSecret(?string $secret): void;

    public function getTwoFactorRecoveryCodes(): ?string;

    public function setTwoFactorRecoveryCodes(?string $codes): void;

    public function getTwoFactorConfirmedAt(): ?DateTimeImmutable;

    public function setTwoFactorConfirmedAt(?DateTimeImmutable $confirmedAt): void;
}
