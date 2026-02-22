<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Actions;

use MrPunyapal\Php2fa\Contracts\Encryptor;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\DataTransferObjects\TwoFactorSetup;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\Support\RecoveryCode;

final readonly class EnableTwoFactorAuthentication
{
    public function __construct(
        private TwoFactorService $service,
        private Encryptor $encryptor,
        private int $recoveryCodeCount = 8,
    ) {}

    public function __invoke(TwoFactorUser $user, string $holder = ''): TwoFactorSetup
    {
        $secret = $this->service->generateSecretKey();
        $recoveryCodes = RecoveryCode::generateCodes($this->recoveryCodeCount);

        $user->setTwoFactorSecret($this->encryptor->encrypt($secret));
        $user->setTwoFactorRecoveryCodes($this->encryptor->encrypt(json_encode($recoveryCodes, JSON_THROW_ON_ERROR)));
        $user->setTwoFactorConfirmedAt(null);

        return new TwoFactorSetup(
            secret: $secret,
            qrCodeUrl: $this->service->getQrCodeUrl($holder, $secret),
            recoveryCodes: $recoveryCodes,
        );
    }
}
