<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Enums\Algorithm;
use MrPunyapal\Php2fa\Services\TwoFactorService;

describe('TwoFactorService', function (): void {
    it('generates a secret key with the configured length', function (): void {
        $service = new TwoFactorService(secretLength: 16);

        $secret = $service->generateSecretKey();

        expect($secret)
            ->toBeString()
            ->toHaveLength(16);
    });

    it('generates a secret key with default length', function (): void {
        $service = new TwoFactorService;

        $secret = $service->generateSecretKey();

        expect($secret)
            ->toBeString()
            ->toHaveLength(32);
    });

    it('generates a qr code url', function (): void {
        $service = new TwoFactorService(issuer: 'TestApp');
        $secret = $service->generateSecretKey();

        $url = $service->getQrCodeUrl('user@test.com', $secret);

        expect($url)
            ->toContain('otpauth://totp/')
            ->toContain('TestApp')
            ->toContain('user%40test.com')
            ->toContain($secret);
    });

    it('verifies a valid otp code', function (): void {
        $service = new TwoFactorService;
        $secret = $service->generateSecretKey();

        $google2fa = new PragmaRX\Google2FA\Google2FA;
        $validCode = $google2fa->getCurrentOtp($secret);

        expect($service->verify($secret, $validCode))->toBeTrue();
    });

    it('rejects an invalid otp code', function (): void {
        $service = new TwoFactorService;
        $secret = $service->generateSecretKey();

        expect($service->verify($secret, '000000'))->toBeFalse();
    });

    it('supports different algorithms', function (Algorithm $algorithm): void {
        $service = new TwoFactorService(algorithm: $algorithm);

        $secret = $service->generateSecretKey();

        expect($secret)->toBeString()->toHaveLength(32);
    })->with([
        'sha1' => Algorithm::Sha1,
        'sha256' => Algorithm::Sha256,
        'sha512' => Algorithm::Sha512,
    ]);
});
