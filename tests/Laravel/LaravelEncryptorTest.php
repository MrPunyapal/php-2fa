<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Laravel\LaravelEncryptor;

describe('LaravelEncryptor', function (): void {
    it('encrypts a value using laravel encrypter', function (): void {
        $encryptor = new LaravelEncryptor(app('encrypter'));

        $encrypted = $encryptor->encrypt('test-value');

        expect($encrypted)->toBeString()->not->toBe('test-value');
    });

    it('decrypts a value using laravel encrypter', function (): void {
        $encryptor = new LaravelEncryptor(app('encrypter'));

        $encrypted = $encryptor->encrypt('test-value');
        $decrypted = $encryptor->decrypt($encrypted);

        expect($decrypted)->toBe('test-value');
    });
});
