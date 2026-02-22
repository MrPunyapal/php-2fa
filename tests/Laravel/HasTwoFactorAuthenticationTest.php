<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use MrPunyapal\Php2fa\Tests\Stubs\TestEloquentUser;

beforeEach(function (): void {
    Schema::create('users', function ($table): void {
        $table->id();
        $table->text('two_factor_secret')->nullable();
        $table->text('two_factor_recovery_codes')->nullable();
        $table->timestamp('two_factor_confirmed_at')->nullable();
        $table->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('users');
});

describe('HasTwoFactorAuthentication', function (): void {
    it('gets and sets two factor secret', function (): void {
        $user = TestEloquentUser::create();

        expect($user->getTwoFactorSecret())->toBeNull();

        $user->setTwoFactorSecret('encrypted-secret');

        expect($user->fresh()->getTwoFactorSecret())->toBe('encrypted-secret');
    });

    it('sets secret to null', function (): void {
        $user = TestEloquentUser::create(['two_factor_secret' => 'secret']);

        $user->setTwoFactorSecret(null);

        expect($user->fresh()->getTwoFactorSecret())->toBeNull();
    });

    it('gets and sets two factor recovery codes', function (): void {
        $user = TestEloquentUser::create();

        expect($user->getTwoFactorRecoveryCodes())->toBeNull();

        $user->setTwoFactorRecoveryCodes('encrypted-codes');

        expect($user->fresh()->getTwoFactorRecoveryCodes())->toBe('encrypted-codes');
    });

    it('sets recovery codes to null', function (): void {
        $user = TestEloquentUser::create(['two_factor_recovery_codes' => 'codes']);

        $user->setTwoFactorRecoveryCodes(null);

        expect($user->fresh()->getTwoFactorRecoveryCodes())->toBeNull();
    });

    it('gets and sets two factor confirmed at', function (): void {
        $user = TestEloquentUser::create();

        expect($user->getTwoFactorConfirmedAt())->toBeNull();

        $now = new DateTimeImmutable('2026-01-15 10:30:00');
        $user->setTwoFactorConfirmedAt($now);

        $refreshed = $user->fresh();
        $confirmedAt = $refreshed->getTwoFactorConfirmedAt();

        expect($confirmedAt)
            ->toBeInstanceOf(DateTimeImmutable::class)
            ->and($confirmedAt->format('Y-m-d H:i:s'))->toBe('2026-01-15 10:30:00');
    });

    it('sets confirmed at to null', function (): void {
        $user = TestEloquentUser::create(['two_factor_confirmed_at' => now()]);

        $user->setTwoFactorConfirmedAt(null);

        expect($user->fresh()->getTwoFactorConfirmedAt())->toBeNull();
    });

    it('reports two factor as enabled when secret and confirmed at exist', function (): void {
        $user = TestEloquentUser::create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
        ]);

        expect($user->hasEnabledTwoFactorAuthentication())->toBeTrue();
    });

    it('reports two factor as not enabled when secret is missing', function (): void {
        $user = TestEloquentUser::create([
            'two_factor_confirmed_at' => now(),
        ]);

        expect($user->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });

    it('reports two factor as not enabled when confirmed at is missing', function (): void {
        $user = TestEloquentUser::create([
            'two_factor_secret' => 'secret',
        ]);

        expect($user->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });

    it('reports two factor as not enabled when both are missing', function (): void {
        $user = TestEloquentUser::create();

        expect($user->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });
});
