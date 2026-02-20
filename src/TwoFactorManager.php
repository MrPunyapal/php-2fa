<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa;

use Mrpunyapal\Php2fa\Actions\ConfirmTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\DisableTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\GenerateRecoveryCodes;
use Mrpunyapal\Php2fa\Actions\VerifyTwoFactorCode;
use Mrpunyapal\Php2fa\Contracts\Encryptor;
use Mrpunyapal\Php2fa\Contracts\TwoFactorUser;
use Mrpunyapal\Php2fa\DataTransferObjects\TwoFactorSetup;
use Mrpunyapal\Php2fa\Enums\Algorithm;
use Mrpunyapal\Php2fa\Services\TwoFactorService;
use Mrpunyapal\Php2fa\Support\OpenSslEncryptor;

final readonly class TwoFactorManager
{
    public function __construct(
        private TwoFactorService $service,
        private Encryptor $encryptor,
        private int $recoveryCodeCount = 8,
    ) {}

    public static function create(
        string $issuer,
        string $encryptionKey,
        Algorithm $algorithm = Algorithm::Sha1,
        int $secretLength = 32,
        int $window = 1,
        int $recoveryCodeCount = 8,
    ): self {
        return new self(
            service: new TwoFactorService(
                algorithm: $algorithm,
                secretLength: $secretLength,
                window: $window,
                issuer: $issuer,
            ),
            encryptor: new OpenSslEncryptor($encryptionKey),
            recoveryCodeCount: $recoveryCodeCount,
        );
    }

    public function enable(TwoFactorUser $user, string $holder = ''): TwoFactorSetup
    {
        return (new EnableTwoFactorAuthentication(
            service: $this->service,
            encryptor: $this->encryptor,
            recoveryCodeCount: $this->recoveryCodeCount,
        ))($user, $holder);
    }

    public function disable(TwoFactorUser $user): void
    {
        (new DisableTwoFactorAuthentication)($user);
    }

    public function confirm(TwoFactorUser $user, string $code): void
    {
        (new ConfirmTwoFactorAuthentication(
            service: $this->service,
            encryptor: $this->encryptor,
        ))($user, $code);
    }

    public function verify(TwoFactorUser $user, string $code): bool
    {
        return (new VerifyTwoFactorCode(
            service: $this->service,
            encryptor: $this->encryptor,
        ))($user, $code);
    }

    /**
     * @return array<int, string>
     */
    public function regenerateRecoveryCodes(TwoFactorUser $user): array
    {
        return (new GenerateRecoveryCodes(
            encryptor: $this->encryptor,
            count: $this->recoveryCodeCount,
        ))($user);
    }
}
