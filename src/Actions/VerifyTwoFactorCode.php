<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Actions;

use MrPunyapal\Php2fa\Contracts\Encryptor;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\Support\RecoveryCode;

final readonly class VerifyTwoFactorCode
{
    public function __construct(
        private TwoFactorService $service,
        private Encryptor $encryptor,
    ) {}

    public function __invoke(TwoFactorUser $user, string $code): bool
    {
        $encryptedSecret = $user->getTwoFactorSecret();

        if ($encryptedSecret === null) {
            throw TwoFactorNotEnabledException::create();
        }

        $secret = $this->encryptor->decrypt($encryptedSecret);

        if ($this->service->verify($secret, $code)) {
            return true;
        }

        return $this->verifyRecoveryCode($user, $code);
    }

    private function verifyRecoveryCode(TwoFactorUser $user, string $code): bool
    {
        $encryptedCodes = $user->getTwoFactorRecoveryCodes();

        if ($encryptedCodes === null) {
            return false;
        }

        /** @var array<int, string> $codes */
        $codes = json_decode($this->encryptor->decrypt($encryptedCodes), true, 512, JSON_THROW_ON_ERROR);

        $index = array_search($code, $codes, true);

        if ($index === false) {
            return false;
        }

        $codes[$index] = RecoveryCode::generate();

        $user->setTwoFactorRecoveryCodes(
            $this->encryptor->encrypt(json_encode(array_values($codes), JSON_THROW_ON_ERROR)),
        );

        return true;
    }
}
