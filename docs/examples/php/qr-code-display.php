<?php

declare(strict_types=1);

/**
 * QR Code Display Helpers.
 *
 * The `TwoFactorSetup::$qrCodeUrl` returns an `otpauth://` URI.
 * This example shows multiple approaches to render it as a scannable QR code.
 */

// --- Option 1: Google Charts API (no dependencies, uses external service) ---

function qrCodeViaGoogleCharts(string $otpauthUrl, int $size = 200): string
{
    $encoded = urlencode($otpauthUrl);

    return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encoded}&choe=UTF-8";
}

// Usage:
// $imgUrl = qrCodeViaGoogleCharts($setup->qrCodeUrl);
// echo "<img src=\"{$imgUrl}\" alt=\"Scan with authenticator app\" />";

// --- Option 2: chillerlan/php-qrcode (self-hosted, no external calls) ---
// composer require chillerlan/php-qrcode

function qrCodeViaChillerlan(string $otpauthUrl): string
{
    // use chillerlan\QRCode\{QRCode, QROptions};

    // $options = new QROptions([
    //     'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    //     'scale' => 5,
    // ]);

    // return (new QRCode($options))->render($otpauthUrl);
    // Returns a base64 data URI: data:image/png;base64,...

    return ''; // Placeholder — uncomment above after installing the package
}

// Usage:
// $dataUri = qrCodeViaChillerlan($setup->qrCodeUrl);
// echo "<img src=\"{$dataUri}\" alt=\"Scan with authenticator app\" />";

// --- Option 3: endroid/qr-code (popular, flexible) ---
// composer require endroid/qr-code

function qrCodeViaEndroid(string $otpauthUrl): string
{
    // use Endroid\QrCode\Builder\Builder;
    // use Endroid\QrCode\Writer\PngWriter;

    // $result = Builder::create()
    //     ->writer(new PngWriter())
    //     ->data($otpauthUrl)
    //     ->size(200)
    //     ->build();

    // return $result->getDataUri();

    return ''; // Placeholder — uncomment above after installing the package
}

// --- Option 4: Simple HTML page with inline QR code ---

function renderQrCodePage(string $otpauthUrl, string $secret): string
{
    $imgUrl = qrCodeViaGoogleCharts($otpauthUrl);

    return <<<HTML
    <!DOCTYPE html>
    <html>
    <head><title>Set Up Two-Factor Authentication</title></head>
    <body>
        <h1>Set Up Two-Factor Authentication</h1>
        <p>Scan this QR code with your authenticator app:</p>
        <img src="{$imgUrl}" alt="QR Code" />
        <p>Or enter this secret manually: <code>{$secret}</code></p>

        <form method="POST" action="/confirm-2fa">
            <label for="code">Enter the 6-digit code from your app:</label>
            <input type="text" name="code" id="code" inputmode="numeric" autocomplete="one-time-code" />
            <button type="submit">Verify & Enable</button>
        </form>
    </body>
    </html>
    HTML;
}
