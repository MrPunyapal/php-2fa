<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Actions;

use Mrpunyapal\Php2fa\Contracts\TwoFactorUser;

final readonly class DisableTwoFactorAuthentication
{
    public function __invoke(TwoFactorUser $user): void
    {
        $user->setTwoFactorSecret(null);
        $user->setTwoFactorRecoveryCodes(null);
        $user->setTwoFactorConfirmedAt(null);
    }
}
