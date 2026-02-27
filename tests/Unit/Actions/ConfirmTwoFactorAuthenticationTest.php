<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Actions\ConfirmTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Exceptions\InvalidOtpException;
use MrPunyapal\Php2fa\Exceptions\TwoFactorNotEnabledException;
use MrPunyapal\Php2fa\Services\TwoFactorService;
use MrPunyapal\Php2fa\Support\OpenSslEncryptor;
use MrPunyapal\Php2fa\Tests\Stubs\TestUser;
use PragmaRX\Google2FA\Google2FA;

beforeEach(function (): void {
    $this->encryptor = new OpenSslEncryptor('test-key');
    $this->service = new TwoFactorService(issuer: 'TestApp');
    $this->confirmAction = new ConfirmTwoFactorAuthentication(
        service: $this->service,
        encryptor: $this->encryptor,
    );
    $this->enableAction = new EnableTwoFactorAuthentication(
        service: $this->service,
        encryptor: $this->encryptor,
    );
});

it('confirms two factor with a valid code', function (): void {
    $user = new TestUser;
    $setup = ($this->enableAction)($user);

    $google2fa = new Google2FA;
    $validCode = $google2fa->getCurrentOtp($setup->secret);

    ($this->confirmAction)($user, $validCode);

    expect($user->getTwoFactorConfirmedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('throws on invalid code', function (): void {
    $user = new TestUser;
    ($this->enableAction)($user);

    expect(fn () => ($this->confirmAction)($user, '000000'))
        ->toThrow(InvalidOtpException::class);
});

it('throws when two factor is not enabled', function (): void {
    $user = new TestUser;

    expect(fn () => ($this->confirmAction)($user, '123456'))
        ->toThrow(TwoFactorNotEnabledException::class);
});
