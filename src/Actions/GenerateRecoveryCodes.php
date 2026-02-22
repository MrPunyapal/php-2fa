<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Actions;

use MrPunyapal\Php2fa\Contracts\Encryptor;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;
use MrPunyapal\Php2fa\Support\RecoveryCode;

final readonly class GenerateRecoveryCodes
{
    public function __construct(
        private Encryptor $encryptor,
        private int $count = 8,
    ) {}

    /**
     * @return array<int, string>
     */
    public function __invoke(TwoFactorUser $user): array
    {
        if ($user->getTwoFactorSecret() === null) {
            throw TwoFactorNotEnabledException::create();
        }

        $codes = RecoveryCode::generateCodes($this->count);

        $user->setTwoFactorRecoveryCodes(
            $this->encryptor->encrypt(json_encode($codes, JSON_THROW_ON_ERROR)),
        );

        return $codes;
    }
}
