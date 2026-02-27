<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Laravel\Concerns;

use DateTimeImmutable;
use DateTimeInterface;

trait HasTwoFactorAuthentication
{
    private bool $savingTwoFactor = true;

    public function getTwoFactorSecret(): ?string
    {
        return $this->getAttribute('two_factor_secret');
    }

    public function setTwoFactorSecret(?string $secret): void
    {
        $this->setAttribute('two_factor_secret', $secret);
        $this->saveTwoFactorIfEnabled();
    }

    public function getTwoFactorRecoveryCodes(): ?string
    {
        return $this->getAttribute('two_factor_recovery_codes');
    }

    public function setTwoFactorRecoveryCodes(?string $codes): void
    {
        $this->setAttribute('two_factor_recovery_codes', $codes);
        $this->saveTwoFactorIfEnabled();
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
        $this->saveTwoFactorIfEnabled();
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->getTwoFactorSecret() !== null
            && $this->getTwoFactorConfirmedAt() !== null;
    }

    /**
     * Execute a callback with all two-factor setter saves deferred,
     * then persist once at the end. Reduces multiple DB writes to one.
     */
    public function withoutSaving(callable $callback): void
    {
        $this->savingTwoFactor = false;

        try {
            $callback($this);
        } finally {
            $this->savingTwoFactor = true;
            $this->save();
        }
    }

    private function saveTwoFactorIfEnabled(): void
    {
        if ($this->savingTwoFactor) {
            $this->save();
        }
    }
}
