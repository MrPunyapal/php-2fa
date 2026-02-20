<?php

declare(strict_types=1);

use Mrpunyapal\Php2fa\Support\RecoveryCode;

describe('RecoveryCode', function (): void {
    it('generates a code in the correct format', function (): void {
        $code = RecoveryCode::generate();

        expect($code)
            ->toBeString()
            ->toMatch('/^[a-zA-Z0-9]{10}-[a-zA-Z0-9]{10}$/');
    });

    it('generates unique codes', function (): void {
        $codes = array_map(
            RecoveryCode::generate(...),
            range(1, 100),
        );

        expect(array_unique($codes))->toHaveCount(100);
    });

    it('generates the requested number of codes', function (): void {
        $codes = RecoveryCode::generateCodes(5);

        expect($codes)->toHaveCount(5);
    });

    it('generates 8 codes by default', function (): void {
        $codes = RecoveryCode::generateCodes();

        expect($codes)->toHaveCount(8);
    });

    it('generates codes that all match the expected format', function (): void {
        $codes = RecoveryCode::generateCodes(10);

        foreach ($codes as $code) {
            expect($code)->toMatch('/^[a-zA-Z0-9]{10}-[a-zA-Z0-9]{10}$/');
        }
    });
});
