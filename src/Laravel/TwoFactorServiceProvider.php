<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Laravel;

use Illuminate\Contracts\Encryption\StringEncrypter;
use Illuminate\Foundation\Application;
use Mrpunyapal\Php2fa\Actions\ConfirmTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\DisableTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use Mrpunyapal\Php2fa\Actions\GenerateRecoveryCodes;
use Mrpunyapal\Php2fa\Actions\VerifyTwoFactorCode;
use Mrpunyapal\Php2fa\Contracts\Encryptor;
use Mrpunyapal\Php2fa\Enums\Algorithm;
use Mrpunyapal\Php2fa\Services\TwoFactorService;
use Mrpunyapal\Php2fa\TwoFactorManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class TwoFactorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('two-factor')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Encryptor::class, fn (Application $app): LaravelEncryptor => new LaravelEncryptor($app->make(StringEncrypter::class)));

        $this->app->singleton(TwoFactorService::class, function (Application $app): TwoFactorService {
            /** @var array{issuer: string, secret_length: int, window: int, algorithm: string, recovery_code_count: int, confirmable: bool} $config */
            $config = $app['config']->get('two-factor');

            return new TwoFactorService(
                algorithm: Algorithm::from($config['algorithm']),
                secretLength: $config['secret_length'],
                window: $config['window'],
                issuer: $config['issuer'],
            );
        });

        $this->app->singleton(EnableTwoFactorAuthentication::class, function (Application $app): EnableTwoFactorAuthentication {
            /** @var int $count */
            $count = $app['config']->get('two-factor.recovery_code_count');

            return new EnableTwoFactorAuthentication(
                service: $app->make(TwoFactorService::class),
                encryptor: $app->make(Encryptor::class),
                recoveryCodeCount: $count,
            );
        });

        $this->app->singleton(DisableTwoFactorAuthentication::class);

        $this->app->singleton(ConfirmTwoFactorAuthentication::class, fn (Application $app): ConfirmTwoFactorAuthentication => new ConfirmTwoFactorAuthentication(
            service: $app->make(TwoFactorService::class),
            encryptor: $app->make(Encryptor::class),
        ));

        $this->app->singleton(VerifyTwoFactorCode::class, fn (Application $app): VerifyTwoFactorCode => new VerifyTwoFactorCode(
            service: $app->make(TwoFactorService::class),
            encryptor: $app->make(Encryptor::class),
        ));

        $this->app->singleton(GenerateRecoveryCodes::class, function (Application $app): GenerateRecoveryCodes {
            /** @var int $count */
            $count = $app['config']->get('two-factor.recovery_code_count');

            return new GenerateRecoveryCodes(
                encryptor: $app->make(Encryptor::class),
                count: $count,
            );
        });

        $this->app->singleton(TwoFactorManager::class, function (Application $app): TwoFactorManager {
            /** @var int $count */
            $count = $app['config']->get('two-factor.recovery_code_count');

            return new TwoFactorManager(
                service: $app->make(TwoFactorService::class),
                encryptor: $app->make(Encryptor::class),
                recoveryCodeCount: $count,
            );
        });
    }
}
