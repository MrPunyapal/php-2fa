<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\Enums\Algorithm;

it('has three cases', function (): void {
    expect(Algorithm::cases())->toHaveCount(3);
});

it('has correct values', function (Algorithm $algorithm, string $expected): void {
    expect($algorithm->value)->toBe($expected);
})->with([
    'sha1' => [Algorithm::Sha1, 'sha1'],
    'sha256' => [Algorithm::Sha256, 'sha256'],
    'sha512' => [Algorithm::Sha512, 'sha512'],
]);

it('can be created from string', function (): void {
    expect(Algorithm::from('sha256'))->toBe(Algorithm::Sha256);
});
