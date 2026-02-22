<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\VerifyTwoFactorCode;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\Support\OpenSslEncryptor;
use MrPunyapal\Php2fa\Tests\Stubs\TestUser;
use PragmaRX\Google2FA\Google2FA;

describe('VerifyTwoFactorCode', function (): void {
    beforeEach(function (): void {
        $this->encryptor = new OpenSslEncryptor('test-key');
        $this->service = new TwoFactorService(issuer: 'TestApp');
        $this->verifyAction = new VerifyTwoFactorCode(
            service: $this->service,
            encryptor: $this->encryptor,
        );
        $this->enableAction = new EnableTwoFactorAuthentication(
            service: $this->service,
            encryptor: $this->encryptor,
        );
    });

    it('verifies a valid otp code', function (): void {
        $user = new TestUser;
        $setup = ($this->enableAction)($user);

        $google2fa = new Google2FA;
        $validCode = $google2fa->getCurrentOtp($setup->secret);

        expect(($this->verifyAction)($user, $validCode))->toBeTrue();
    });

    it('rejects an invalid otp code with no valid recovery code', function (): void {
        $user = new TestUser;
        ($this->enableAction)($user);

        expect(($this->verifyAction)($user, '000000'))->toBeFalse();
    });

    it('verifies a valid recovery code', function (): void {
        $user = new TestUser;
        $setup = ($this->enableAction)($user);

        $recoveryCode = $setup->recoveryCodes[0];

        expect(($this->verifyAction)($user, $recoveryCode))->toBeTrue();
    });

    it('replaces a used recovery code', function (): void {
        $user = new TestUser;
        $setup = ($this->enableAction)($user);

        $recoveryCode = $setup->recoveryCodes[0];

        ($this->verifyAction)($user, $recoveryCode);

        $storedCodes = json_decode(
            (string) $this->encryptor->decrypt($user->getTwoFactorRecoveryCodes()),
            true,
        );

        expect($storedCodes)
            ->toHaveCount(8)
            ->not->toContain($recoveryCode);
    });

    it('rejects an already used recovery code', function (): void {
        $user = new TestUser;
        $setup = ($this->enableAction)($user);

        $recoveryCode = $setup->recoveryCodes[0];

        ($this->verifyAction)($user, $recoveryCode);
        expect(($this->verifyAction)($user, $recoveryCode))->toBeFalse();
    });

    it('throws when two factor is not enabled', function (): void {
        $user = new TestUser;

        expect(fn () => ($this->verifyAction)($user, '123456'))
            ->toThrow(TwoFactorNotEnabledException::class);
    });

    it('returns false when recovery codes are null', function (): void {
        $user = new TestUser;
        $setup = ($this->enableAction)($user);

        $user->setTwoFactorRecoveryCodes(null);

        expect(($this->verifyAction)($user, 'invalid-code'))->toBeFalse();
    });
});
