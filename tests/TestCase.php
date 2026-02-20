<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Tests;

use Mrpunyapal\Php2fa\Laravel\TwoFactorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TwoFactorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('two-factor.issuer', 'TestApp');
        config()->set('two-factor.secret_length', 32);
        config()->set('two-factor.window', 1);
        config()->set('two-factor.algorithm', 'sha1');
        config()->set('two-factor.recovery_code_count', 8);
        config()->set('two-factor.confirmable', true);
    }
}
