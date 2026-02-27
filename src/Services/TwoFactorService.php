<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Services;

use MrPunyapal\Php2fa\Enums\Algorithm;
use PragmaRX\Google2FA\Google2FA;

final readonly class TwoFactorService
{
    private Google2FA $engine;

    public function __construct(
        private Algorithm $algorithm = Algorithm::Sha1,
        private int $secretLength = 32,
        private int $window = 1,
        private string $issuer = '',
    ) {
        $this->engine = new Google2FA;
        $this->engine->setAlgorithm($this->algorithm->value);
        $this->engine->setWindow($this->window);
    }

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey($this->secretLength);
    }

    public function getQrCodeUrl(string $holder, string $secret): string
    {
        return $this->engine->getQRCodeUrl($this->issuer, $holder, $secret);
    }

    public function verify(string $secret, string $code): bool
    {
        return (bool) $this->engine->verifyKey($secret, $code);
    }
}
