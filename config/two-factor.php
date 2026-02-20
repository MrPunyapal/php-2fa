<?php

declare(strict_types=1);

return [

    'issuer' => env('TWO_FACTOR_ISSUER', config('app.name', 'My App')),

    'secret_length' => (int) env('TWO_FACTOR_SECRET_LENGTH', 32),

    'window' => (int) env('TWO_FACTOR_WINDOW', 1),

    'algorithm' => env('TWO_FACTOR_ALGORITHM', 'sha1'),

    'recovery_code_count' => (int) env('TWO_FACTOR_RECOVERY_CODE_COUNT', 8),

    'confirmable' => (bool) env('TWO_FACTOR_CONFIRMABLE', true),

];
