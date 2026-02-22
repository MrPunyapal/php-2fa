<?php

declare(strict_types=1);

arch('source files use strict types')
    ->expect('MrPunyapal\Php2fa')
    ->toUseStrictTypes();

arch('actions are invokable')
    ->expect('MrPunyapal\Php2fa\Actions')
    ->toBeReadonly()
    ->toHaveMethod('__invoke');

arch('contracts are interfaces')
    ->expect('MrPunyapal\Php2fa\Contracts')
    ->toBeInterfaces();

arch('enums are enums')
    ->expect('MrPunyapal\Php2fa\Enums')
    ->toBeEnums();

arch('exceptions extend RuntimeException')
    ->expect('MrPunyapal\Php2fa\Exceptions')
    ->toExtend(RuntimeException::class);

arch('dtos are readonly')
    ->expect('MrPunyapal\Php2fa\DataTransferObjects')
    ->toBeReadonly();

arch('no debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();
