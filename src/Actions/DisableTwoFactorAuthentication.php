<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Actions;

use MrPunyapal\Php2fa\Contracts\TwoFactorUser;

final readonly class DisableTwoFactorAuthentication
{
    public function __invoke(TwoFactorUser $user): void
    {
        $user->setTwoFactorSecret(null);
        $user->setTwoFactorRecoveryCodes(null);
        $user->setTwoFactorConfirmedAt(null);
    }
}
