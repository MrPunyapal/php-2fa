<?php

declare(strict_types=1);

/**
 * Setup Two-Factor Authentication for a plain PHP application.
 *
 * This example shows how to implement the TwoFactorUser interface on a PDO-backed
 * user entity, enable 2FA, and confirm it with a code from the authenticator app.
 */

use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\TwoFactorManager;

require __DIR__.'/../../../vendor/autoload.php';

// --- 1. Implement TwoFactorUser on your user entity ---

final class User implements TwoFactorUser
{
    private ?string $twoFactorSecret = null;

    private ?string $twoFactorRecoveryCodes = null;

    private ?DateTimeImmutable $twoFactorConfirmedAt = null;

    public function __construct(
        private readonly PDO $db,
        private readonly int $id,
    ) {}

    public static function find(PDO $db, int $id): self
    {
        $user = new self($db, $id);
        $user->load();

        return $user;
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }

    public function setTwoFactorSecret(?string $secret): void
    {
        $this->twoFactorSecret = $secret;
        $this->persist('two_factor_secret', $secret);
    }

    public function getTwoFactorRecoveryCodes(): ?string
    {
        return $this->twoFactorRecoveryCodes;
    }

    public function setTwoFactorRecoveryCodes(?string $codes): void
    {
        $this->twoFactorRecoveryCodes = $codes;
        $this->persist('two_factor_recovery_codes', $codes);
    }

    public function getTwoFactorConfirmedAt(): ?DateTimeImmutable
    {
        return $this->twoFactorConfirmedAt;
    }

    public function setTwoFactorConfirmedAt(?DateTimeImmutable $confirmedAt): void
    {
        $this->twoFactorConfirmedAt = $confirmedAt;
        $this->persist('two_factor_confirmed_at', $confirmedAt?->format('Y-m-d H:i:s'));
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->twoFactorSecret !== null
            && $this->twoFactorConfirmedAt !== null;
    }

    private function load(): void
    {
        $stmt = $this->db->prepare('SELECT two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at FROM users WHERE id = ?');
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->twoFactorSecret = $row['two_factor_secret'];
            $this->twoFactorRecoveryCodes = $row['two_factor_recovery_codes'];
            $this->twoFactorConfirmedAt = $row['two_factor_confirmed_at']
                ? new DateTimeImmutable($row['two_factor_confirmed_at'])
                : null;
        }
    }

    private function persist(string $column, ?string $value): void
    {
        $stmt = $this->db->prepare("UPDATE users SET {$column} = ? WHERE id = ?");
        $stmt->execute([$value, $this->id]);
    }
}

// --- 2. Initialize the manager ---

$manager = TwoFactorManager::create(
    issuer: 'My App',
    encryptionKey: getenv('TWO_FACTOR_KEY') ?: 'your-32-char-encryption-key-here',
);

// --- 3. Enable 2FA ---

$db = new PDO('sqlite:database.sqlite');
$user = User::find($db, userId: 1);

$setup = $manager->enable($user, 'user@example.com');

echo "Secret: {$setup->secret}\n";
echo "QR Code URL: {$setup->qrCodeUrl}\n";
echo "Recovery Codes:\n";
foreach ($setup->recoveryCodes as $code) {
    echo "  - {$code}\n";
}

// --- 4. Confirm 2FA (user enters code from authenticator app) ---

$code = readline('Enter the code from your authenticator app: ');
$manager->confirm($user, $code);

echo "Two-factor authentication confirmed!\n";
