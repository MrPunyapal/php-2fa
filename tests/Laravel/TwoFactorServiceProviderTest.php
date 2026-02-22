<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Actions\ConfirmTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\DisableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\GenerateRecoveryCodes;
use MrPunyapal\Php2fa\Actions\VerifyTwoFactorCode;
use MrPunyapal\Php2fa\Contracts\Encryptor;
use MrPunyapal\Php2fa\Laravel\LaravelEncryptor;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\TwoFactorManager;

describe('TwoFactorServiceProvider', function (): void {
    it('binds encryptor contract', function (): void {
        expect(app(Encryptor::class))->toBeInstanceOf(LaravelEncryptor::class);
    });

    it('binds two factor service as singleton', function (): void {
        $first = app(TwoFactorService::class);
        $second = app(TwoFactorService::class);

        expect($first)->toBe($second);
    });

    it('binds enable action', function (): void {
        expect(app(EnableTwoFactorAuthentication::class))
            ->toBeInstanceOf(EnableTwoFactorAuthentication::class);
    });

    it('binds disable action', function (): void {
        expect(app(DisableTwoFactorAuthentication::class))
            ->toBeInstanceOf(DisableTwoFactorAuthentication::class);
    });

    it('binds confirm action', function (): void {
        expect(app(ConfirmTwoFactorAuthentication::class))
            ->toBeInstanceOf(ConfirmTwoFactorAuthentication::class);
    });

    it('binds verify action', function (): void {
        expect(app(VerifyTwoFactorCode::class))
            ->toBeInstanceOf(VerifyTwoFactorCode::class);
    });

    it('binds generate recovery codes action', function (): void {
        expect(app(GenerateRecoveryCodes::class))
            ->toBeInstanceOf(GenerateRecoveryCodes::class);
    });

    it('binds two factor manager', function (): void {
        expect(app(TwoFactorManager::class))
            ->toBeInstanceOf(TwoFactorManager::class);
    });

    it('uses config values for service', function (): void {
        config()->set('two-factor.issuer', 'CustomApp');

        $service = app()->make(TwoFactorService::class);

        expect($service)->toBeInstanceOf(TwoFactorService::class);
    });
});
