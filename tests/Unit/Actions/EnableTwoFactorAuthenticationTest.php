<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\DataTransferObjects\TwoFactorSetup;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\Support\OpenSslEncryptor;
use MrPunyapal\Php2fa\Tests\Stubs\TestUser;

beforeEach(function (): void {
    $this->encryptor = new OpenSslEncryptor('test-key');
    $this->service = new TwoFactorService(issuer: 'TestApp');
    $this->action = new EnableTwoFactorAuthentication(
        service: $this->service,
        encryptor: $this->encryptor,
    );
});

it('enables two factor authentication for a user', function (): void {
    $user = new TestUser;

    $setup = ($this->action)($user, 'user@test.com');

    expect($setup)
        ->toBeInstanceOf(TwoFactorSetup::class)
        ->and($setup->secret)->toBeString()->toHaveLength(32)
        ->and($setup->qrCodeUrl)->toContain('otpauth://totp/')
        ->and($setup->recoveryCodes)->toHaveCount(8);
});

it('stores encrypted secret on the user', function (): void {
    $user = new TestUser;

    $setup = ($this->action)($user);

    $storedSecret = $user->getTwoFactorSecret();
    expect($storedSecret)->not->toBeNull()->not->toBe($setup->secret);

    $decrypted = $this->encryptor->decrypt($storedSecret);
    expect($decrypted)->toBe($setup->secret);
});

it('stores encrypted recovery codes on the user', function (): void {
    $user = new TestUser;

    $setup = ($this->action)($user);

    $storedCodes = $user->getTwoFactorRecoveryCodes();
    expect($storedCodes)->not->toBeNull();

    $decrypted = json_decode((string) $this->encryptor->decrypt($storedCodes), true);
    expect($decrypted)->toBe($setup->recoveryCodes);
});

it('resets confirmed at to null', function (): void {
    $user = new TestUser;
    $user->setTwoFactorConfirmedAt(\Carbon\CarbonImmutable::now());

    ($this->action)($user);

    expect($user->getTwoFactorConfirmedAt())->toBeNull();
});

it('uses custom recovery code count', function (): void {
    $action = new EnableTwoFactorAuthentication(
        service: $this->service,
        encryptor: $this->encryptor,
        recoveryCodeCount: 4,
    );
    $user = new TestUser;

    $setup = ($action)($user);

    expect($setup->recoveryCodes)->toHaveCount(4);
});

it('generates qr code url with holder', function (): void {
    $user = new TestUser;

    $setup = ($this->action)($user, 'user@example.com');

    expect($setup->qrCodeUrl)->toContain('user%40example.com');
});

it('can re-enable two factor on an already enabled user', function (): void {
    $user = new TestUser;

    $firstSetup = ($this->action)($user);
    $secondSetup = ($this->action)($user);

    expect($secondSetup->secret)->not->toBe($firstSetup->secret);
});
