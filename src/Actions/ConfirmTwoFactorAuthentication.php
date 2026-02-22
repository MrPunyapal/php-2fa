<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Actions;

use DateTimeImmutable;
use MrPunyapal\Php2fa\Contracts\Encryptor;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\Exceptions\InvalidOtpException;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;
use MrPunyapal\Php2fa\Services\TwoFactorService;

final readonly class ConfirmTwoFactorAuthentication
{
    public function __construct(
        private TwoFactorService $service,
        private Encryptor $encryptor,
    ) {}

    public function __invoke(TwoFactorUser $user, string $code): void
    {
        $encryptedSecret = $user->getTwoFactorSecret();

        if ($encryptedSecret === null) {
            throw TwoFactorNotEnabledException::create();
        }

        $secret = $this->encryptor->decrypt($encryptedSecret);

        if (! $this->service->verify($secret, $code)) {
            throw InvalidOtpException::create();
        }

        $user->setTwoFactorConfirmedAt(new DateTimeImmutable);
    }
}
