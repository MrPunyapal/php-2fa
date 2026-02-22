<?php

declare(strict_types=1);

use MrPunyapal\Php2fa\DataTransferObjects\TwoFactorSetup;

describe('TwoFactorSetup', function (): void {
    it('holds setup data', function (): void {
        $setup = new TwoFactorSetup(
            secret: 'JBSWY3DPEHPK3PXP',
            qrCodeUrl: 'otpauth://totp/TestApp:user@test.com?secret=JBSWY3DPEHPK3PXP',
            recoveryCodes: ['code1-code1', 'code2-code2'],
        );

        expect($setup->secret)->toBe('JBSWY3DPEHPK3PXP')
            ->and($setup->qrCodeUrl)->toContain('otpauth://totp/')
            ->and($setup->recoveryCodes)->toHaveCount(2);
    });
});
