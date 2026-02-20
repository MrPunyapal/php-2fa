<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Laravel\Concerns;

use DateTimeImmutable;
use DateTimeInterface;

trait HasTwoFactorAuthentication
{
    public function getTwoFactorSecret(): ?string
    {
        return $this->getAttribute('two_factor_secret');
    }

    public function setTwoFactorSecret(?string $secret): void
    {
        $this->setAttribute('two_factor_secret', $secret);
        $this->save();
    }

    public function getTwoFactorRecoveryCodes(): ?string
    {
        return $this->getAttribute('two_factor_recovery_codes');
    }

    public function setTwoFactorRecoveryCodes(?string $codes): void
    {
        $this->setAttribute('two_factor_recovery_codes', $codes);
        $this->save();
    }

    public function getTwoFactorConfirmedAt(): ?DateTimeImmutable
    {
        $value = $this->getAttribute('two_factor_confirmed_at');

        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value);
    }

    public function setTwoFactorConfirmedAt(?DateTimeImmutable $confirmedAt): void
    {
        $this->setAttribute(
            'two_factor_confirmed_at',
            $confirmedAt?->format('Y-m-d H:i:s'),
        );
        $this->save();
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->getTwoFactorSecret() !== null
            && $this->getTwoFactorConfirmedAt() !== null;
    }
}
