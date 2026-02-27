<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\GenerateRecoveryCodes;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\Support\OpenSslEncryptor;
use MrPunyapal\Php2fa\Tests\Stubs\TestUser;

beforeEach(function (): void {
    $this->encryptor = new OpenSslEncryptor('test-key');
    $this->service = new TwoFactorService(issuer: 'TestApp');
    $this->generateAction = new GenerateRecoveryCodes(
        encryptor: $this->encryptor,
    );
    $this->enableAction = new EnableTwoFactorAuthentication(
        service: $this->service,
        encryptor: $this->encryptor,
    );
});

it('generates new recovery codes', function (): void {
    $user = new TestUser;
    ($this->enableAction)($user);

    $codes = ($this->generateAction)($user);

    expect($codes)->toHaveCount(8)->each->toMatch('/^[a-zA-Z0-9]{10}-[a-zA-Z0-9]{10}$/');
});

it('replaces existing recovery codes', function (): void {
    $user = new TestUser;
    $setup = ($this->enableAction)($user);

    $newCodes = ($this->generateAction)($user);

    expect($newCodes)->not->toBe($setup->recoveryCodes);
});

it('stores encrypted codes on the user', function (): void {
    $user = new TestUser;
    ($this->enableAction)($user);

    $codes = ($this->generateAction)($user);

    $storedCodes = json_decode(
        (string) $this->encryptor->decrypt($user->getTwoFactorRecoveryCodes()),
        true,
    );

    expect($storedCodes)->toBe($codes);
});

it('generates custom count of codes', function (): void {
    $action = new GenerateRecoveryCodes(
        encryptor: $this->encryptor,
        count: 4,
    );
    $user = new TestUser;
    ($this->enableAction)($user);

    $codes = ($action)($user);

    expect($codes)->toHaveCount(4);
});

it('throws when two factor is not enabled', function (): void {
    $user = new TestUser;

    expect(fn () => ($this->generateAction)($user))
        ->toThrow(TwoFactorNotEnabledException::class);
});
