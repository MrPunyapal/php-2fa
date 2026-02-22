<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Exceptions\EncryptionException;
use MrPunyapal\Php2fa\Exceptions\InvalidOtpException;
use MrPunyapal\Php2fa\Exceptions\TwoFactorAlreadyEnabledException;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;

describe('Exceptions', function (): void {
    it('creates InvalidOtpException', function (): void {
        $exception = InvalidOtpException::create();

        expect($exception)
            ->toBeInstanceOf(InvalidOtpException::class)
            ->getMessage()->toBe('The provided one-time password is invalid.');
    });

    it('creates TwoFactorNotEnabledException', function (): void {
        $exception = TwoFactorNotEnabledException::create();

        expect($exception)
            ->toBeInstanceOf(TwoFactorNotEnabledException::class)
            ->getMessage()->toBe('Two-factor authentication is not enabled.');
    });

    it('creates TwoFactorAlreadyEnabledException', function (): void {
        $exception = TwoFactorAlreadyEnabledException::create();

        expect($exception)
            ->toBeInstanceOf(TwoFactorAlreadyEnabledException::class)
            ->getMessage()->toBe('Two-factor authentication is already confirmed and enabled.');
    });

    it('creates EncryptionException for encryption failure', function (): void {
        $exception = EncryptionException::encryptionFailed();

        expect($exception)
            ->toBeInstanceOf(EncryptionException::class)
            ->getMessage()->toBe('Failed to encrypt the given value.');
    });

    it('creates EncryptionException for decryption failure', function (): void {
        $exception = EncryptionException::decryptionFailed();

        expect($exception)
            ->toBeInstanceOf(EncryptionException::class)
            ->getMessage()->toBe('Failed to decrypt the given value.');
    });
});
