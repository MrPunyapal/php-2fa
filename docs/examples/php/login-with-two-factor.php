<?php

declare(strict_types=1);

/**
 * Session-based login flow with Two-Factor Authentication.
 *
 * This example shows a typical login flow:
 * 1. User submits username/password
 * 2. If 2FA is enabled, redirect to OTP verification
 * 3. Verify OTP or recovery code before granting access
 */

use MrPunyapal\Php2fa\TwoFactorManager;

require __DIR__.'/../../../vendor/autoload.php';

session_start();

$manager = TwoFactorManager::create(
    issuer: 'My App',
    encryptionKey: getenv('TWO_FACTOR_KEY') ?: 'your-32-char-encryption-key-here',
);

// --- Step 1: Handle password authentication ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    // Your normal password verification logic here
    $user = authenticateWithPassword($_POST['username'], $_POST['password']);

    if ($user === null) {
        echo 'Invalid credentials.';
        exit;
    }

    // Check if 2FA is enabled
    if ($user->hasEnabledTwoFactorAuthentication()) {
        $_SESSION['2fa_user_id'] = $user->getId();
        header('Location: /two-factor-challenge');
        exit;
    }

    // No 2FA — grant access directly
    $_SESSION['authenticated_user_id'] = $user->getId();
    header('Location: /dashboard');
    exit;
}

// --- Step 2: Handle 2FA challenge ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'], $_SESSION['2fa_user_id'])) {
    $user = findUserById($_SESSION['2fa_user_id']);

    if ($manager->verify($user, $_POST['code'])) {
        unset($_SESSION['2fa_user_id']);
        $_SESSION['authenticated_user_id'] = $user->getId();
        header('Location: /dashboard');
        exit;
    }

    echo 'Invalid code. Please try again.';
    exit;
}

// --- Step 3: Show the appropriate form ---

if (isset($_SESSION['2fa_user_id'])) {
    ?>
    <h2>Two-Factor Verification</h2>
    <form method="POST">
        <label for="code">Enter OTP or Recovery Code:</label>
        <input type="text" name="code" id="code" autofocus autocomplete="one-time-code" inputmode="numeric" />
        <button type="submit">Verify</button>
    </form>
    <?php
} else {
    ?>
    <h2>Login</h2>
    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" />
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" />
        <button type="submit">Login</button>
    </form>
    <?php
}

// --- Helper stubs (replace with your actual implementation) ---

function authenticateWithPassword(string $username, string $password): ?object
{
    // Your password authentication logic here
    return null;
}

function findUserById(int $id): object
{
    // Your user lookup logic here
    throw new RuntimeException('Not implemented');
}
