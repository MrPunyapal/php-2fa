<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Tests\Stubs;

use DateTimeImmutable;
use Mrpunyapal\Php2fa\Contracts\TwoFactorUser;

final class TestUser implements TwoFactorUser
{
    private ?string $twoFactorSecret = null;

    private ?string $twoFactorRecoveryCodes = null;

    private ?DateTimeImmutable $twoFactorConfirmedAt = null;

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }

    public function setTwoFactorSecret(?string $secret): void
    {
        $this->twoFactorSecret = $secret;
    }

    public function getTwoFactorRecoveryCodes(): ?string
    {
        return $this->twoFactorRecoveryCodes;
    }

    public function setTwoFactorRecoveryCodes(?string $codes): void
    {
        $this->twoFactorRecoveryCodes = $codes;
    }

    public function getTwoFactorConfirmedAt(): ?DateTimeImmutable
    {
        return $this->twoFactorConfirmedAt;
    }

    public function setTwoFactorConfirmedAt(?DateTimeImmutable $confirmedAt): void
    {
        $this->twoFactorConfirmedAt = $confirmedAt;
    }
}
