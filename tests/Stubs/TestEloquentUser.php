<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\Php2fa\Contracts\TwoFactorUser;
use MrPunyapal\Php2fa\Laravel\Concerns\HasTwoFactorAuthentication;

final class TestEloquentUser extends Model implements TwoFactorUser
{
    use HasTwoFactorAuthentication;

    protected $table = 'users';

    protected $guarded = [];
}
