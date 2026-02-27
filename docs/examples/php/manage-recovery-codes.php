<?php

declare(strict_types=1);

/**
 * Recovery Code Management.
 *
 * This example demonstrates how to regenerate recovery codes,
 * display them securely to the user, and verify them during login.
 */

use MrPunyapal\Php2fa\TwoFactorManager;

require __DIR__.'/../../../vendor/autoload.php';

$manager = TwoFactorManager::create(
    issuer: 'My App',
    encryptionKey: getenv('TWO_FACTOR_KEY') ?: 'your-32-char-encryption-key-here',
);

// Assume $user is your authenticated user implementing TwoFactorUser

// --- Regenerate recovery codes ---

$newCodes = $manager->regenerateRecoveryCodes($user);

echo "Your new recovery codes (save them in a safe place):\n\n";

foreach ($newCodes as $index => $code) {
    printf("  %d. %s\n", $index + 1, $code);
}

echo "\nThese codes can each be used once to sign in if you lose access to your authenticator app.\n";

// --- Display as downloadable text ---

function generateRecoveryCodesFile(array $codes, string $appName): string
{
    $lines = [
        "{$appName} - Recovery Codes",
        str_repeat('-', 40),
        'Generated: '.date('Y-m-d H:i:s'),
        '',
        'Each code can only be used once.',
        'Store these in a secure location.',
        '',
    ];

    foreach ($codes as $index => $code) {
        $lines[] = sprintf('%d. %s', $index + 1, $code);
    }

    return implode("\n", $lines);
}

// Example: serve as a downloadable file
$content = generateRecoveryCodesFile($newCodes, 'My App');
// header('Content-Type: text/plain');
// header('Content-Disposition: attachment; filename="recovery-codes.txt"');
// echo $content;

// --- Verify a recovery code during login ---

$code = $_POST['code'] ?? readline('Enter a recovery code: ');

if ($manager->verify($user, $code)) {
    echo "Recovery code accepted. You are now logged in.\n";
    echo "Note: This recovery code has been consumed and replaced automatically.\n";
} else {
    echo "Invalid recovery code.\n";
}
