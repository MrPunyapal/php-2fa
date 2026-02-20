<?php

declare(strict_types=1);

namespace Mrpunyapal\Php2fa\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Mrpunyapal\Php2fa\Contracts\TwoFactorUser;
use Mrpunyapal\Php2fa\Laravel\Concerns\HasTwoFactorAuthentication;

final class TestEloquentUser extends Model implements TwoFactorUser
{
    use HasTwoFactorAuthentication;

    protected $table = 'users';

    protected $guarded = [];
}
