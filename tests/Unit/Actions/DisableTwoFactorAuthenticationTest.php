<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Actions\DisableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Tests\Stubs\TestUser;

it('clears all two factor fields', function (): void {
    $user = new TestUser;
    $user->setTwoFactorSecret('encrypted-secret');
    $user->setTwoFactorRecoveryCodes('encrypted-codes');
    $user->setTwoFactorConfirmedAt(new DateTimeImmutable);

    $action = new DisableTwoFactorAuthentication;
    ($action)($user);

    expect($user->getTwoFactorSecret())->toBeNull()
        ->and($user->getTwoFactorRecoveryCodes())->toBeNull()
        ->and($user->getTwoFactorConfirmedAt())->toBeNull();
});

it('works on a user with no two factor enabled', function (): void {
    $user = new TestUser;

    $action = new DisableTwoFactorAuthentication;
    ($action)($user);

    expect($user->getTwoFactorSecret())->toBeNull();
});
