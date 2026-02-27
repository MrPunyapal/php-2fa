<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Exceptions\EncryptionException;
use MrPunyapal\Php2fa\Support\OpenSslEncryptor;

it('encrypts and decrypts a value', function (): void {
    $encryptor = new OpenSslEncryptor('test-secret-key');
    $original = 'my-secret-value';

    $encrypted = $encryptor->encrypt($original);
    $decrypted = $encryptor->decrypt($encrypted);

    expect($decrypted)->toBe($original);
});

it('produces different ciphertext for the same input', function (): void {
    $encryptor = new OpenSslEncryptor('test-secret-key');
    $value = 'same-value';

    $first = $encryptor->encrypt($value);
    $second = $encryptor->encrypt($value);

    expect($first)->not->toBe($second);
});

it('fails to decrypt with a different key', function (): void {
    $encryptor1 = new OpenSslEncryptor('key-one');
    $encryptor2 = new OpenSslEncryptor('key-two');

    $encrypted = $encryptor1->encrypt('secret');

    expect(fn (): string => $encryptor2->decrypt($encrypted))
        ->toThrow(EncryptionException::class, 'Failed to decrypt the given value.');
});

it('fails to decrypt invalid base64', function (): void {
    $encryptor = new OpenSslEncryptor('test-key');

    expect(fn (): string => $encryptor->decrypt('not-valid-base64!!!'))
        ->toThrow(EncryptionException::class);
});

it('fails to decrypt tampered ciphertext', function (): void {
    $encryptor = new OpenSslEncryptor('test-key');
    $encrypted = $encryptor->encrypt('original');

    $decoded = base64_decode($encrypted, true);
    $tampered = base64_encode($decoded.'tampered');

    expect(fn (): string => $encryptor->decrypt($tampered))
        ->toThrow(EncryptionException::class);
});

it('fails to decrypt data that is too short', function (): void {
    $encryptor = new OpenSslEncryptor('test-key');

    $tooShort = base64_encode('short');

    expect(fn (): string => $encryptor->decrypt($tooShort))
        ->toThrow(EncryptionException::class);
});

it('handles empty string encryption', function (): void {
    $encryptor = new OpenSslEncryptor('test-key');

    $encrypted = $encryptor->encrypt('');
    $decrypted = $encryptor->decrypt($encrypted);

    expect($decrypted)->toBeEmpty();
});

it('handles long string encryption', function (): void {
    $encryptor = new OpenSslEncryptor('test-key');
    $longString = str_repeat('a', 10000);

    $encrypted = $encryptor->encrypt($longString);
    $decrypted = $encryptor->decrypt($encrypted);

    expect($decrypted)->toBe($longString);
});
