<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Enums\Algorithm;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;
use MrPunyapal\Php2fa\Tests\Stubs\TestUser;
use MrPunyapal\Php2fa\TwoFactorManager;
use PragmaRX\Google2FA\Google2FA;

describe('TwoFactorManager', function (): void {
    beforeEach(function (): void {
        $this->manager = TwoFactorManager::create(
            issuer: 'TestApp',
            encryptionKey: 'test-encryption-key',
        );
    });

    it('creates an instance via factory method', function (): void {
        expect($this->manager)->toBeInstanceOf(TwoFactorManager::class);
    });

    it('creates an instance with custom options', function (): void {
        $manager = TwoFactorManager::create(
            issuer: 'CustomApp',
            encryptionKey: 'custom-key',
            algorithm: Algorithm::Sha256,
            secretLength: 16,
            window: 2,
            recoveryCodeCount: 4,
        );

        expect($manager)->toBeInstanceOf(TwoFactorManager::class);
    });

    it('enables two factor authentication', function (): void {
        $user = new TestUser;

        $setup = $this->manager->enable($user, 'user@test.com');

        expect($setup->secret)->toBeString()
            ->and($setup->qrCodeUrl)->toContain('otpauth://')
            ->and($setup->recoveryCodes)->toHaveCount(8);
    });

    it('disables two factor authentication', function (): void {
        $user = new TestUser;
        $this->manager->enable($user);

        $this->manager->disable($user);

        expect($user->getTwoFactorSecret())->toBeNull()
            ->and($user->getTwoFactorRecoveryCodes())->toBeNull()
            ->and($user->getTwoFactorConfirmedAt())->toBeNull();
    });

    it('confirms two factor authentication', function (): void {
        $user = new TestUser;
        $setup = $this->manager->enable($user);

        $google2fa = new Google2FA;
        $validCode = $google2fa->getCurrentOtp($setup->secret);

        $this->manager->confirm($user, $validCode);

        expect($user->getTwoFactorConfirmedAt())->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('verifies a valid otp code', function (): void {
        $user = new TestUser;
        $setup = $this->manager->enable($user);

        $google2fa = new Google2FA;
        $validCode = $google2fa->getCurrentOtp($setup->secret);

        expect($this->manager->verify($user, $validCode))->toBeTrue();
    });

    it('verifies a recovery code', function (): void {
        $user = new TestUser;
        $setup = $this->manager->enable($user);

        expect($this->manager->verify($user, $setup->recoveryCodes[0]))->toBeTrue();
    });

    it('regenerates recovery codes', function (): void {
        $user = new TestUser;
        $setup = $this->manager->enable($user);

        $newCodes = $this->manager->regenerateRecoveryCodes($user);

        expect($newCodes)
            ->toHaveCount(8)
            ->not->toBe($setup->recoveryCodes);
    });

    it('throws when regenerating codes without two factor enabled', function (): void {
        $user = new TestUser;

        expect(fn () => $this->manager->regenerateRecoveryCodes($user))
            ->toThrow(TwoFactorNotEnabledException::class);
    });
});
