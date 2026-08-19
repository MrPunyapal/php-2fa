<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Docsmith\Docsmith;

$output = __DIR__.'/docs';

Docsmith::make()
    ->source(__DIR__.'/md')
    ->output($output)
    ->title('PHP 2FA')
    ->description('Framework-agnostic two-factor authentication (TOTP) for PHP with optional Laravel support.')
    ->siteUrl('https://mrpunyapal.github.io/php-2fa')
    ->repositoryUrl('https://github.com/mrpunyapal/php-2fa')
    ->editBranch('main')
    ->editPrefix('md')
    ->navigationOrder([
        'index.md',
        'installation.md',
        'quick-start.md',
        'actions.md',
        'laravel.md',
        'configuration.md',
        'custom-encryptor.md',
    ])
    ->ogGeneratedPerPage()
    ->build();

// The sitemap <lastmod> is derived from file mtimes, which differ between
// local builds and CI checkouts. Strip it so the generated site is
// deterministic and CI never produces a docs commit loop.
$sitemap = $output.'/sitemap.xml';

if (is_file($sitemap)) {
    $normalized = (string) preg_replace(
        '/\s*<lastmod>[^<]*<\/lastmod>/',
        '',
        (string) file_get_contents($sitemap),
    );

    if ($normalized !== '') {
        file_put_contents($sitemap, $normalized);
    }
}