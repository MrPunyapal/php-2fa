# Laravel Usage

The Laravel integration adds a service provider, a config file, and an Eloquent trait.

## Add the trait to your User model

```php
<?php

use Illuminate\Foundation\Auth\User as Authenticatable;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\Laravel\Concerns\HasTwoFactorAuthentication;

class User extends Authenticatable implements TwoFactorUser
{
    use HasTwoFactorAuthentication;
}
```

## Add the required columns

Add the three 2FA columns to the `users` table:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });
    }
};
```

## Inject actions into a controller

The actions are auto-resolvable by the container:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;

class TwoFactorController extends Controller
{
    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $setup = $enable($request->user(), $request->user()->email);

        return response()->json([
            'qr_code_url' => $setup->qrCodeUrl,
            'recovery_codes' => $setup->recoveryCodes,
        ]);
    }
}
```

## Batch saves with `withoutSaving()`

By default, each setter on the `HasTwoFactorAuthentication` trait persists immediately. To batch multiple field changes into a single DB write:

```php
$user->withoutSaving(function ($user) {
    $user->setTwoFactorSecret($encrypted);
    $user->setTwoFactorRecoveryCodes($codes);
    $user->setTwoFactorConfirmedAt(null);
});
// One save() call instead of three
```

## Complete examples

Full working Laravel examples — controller, middleware, routes, blade view, and migration — are in [`docs/examples/laravel/`](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/laravel).