<?php

declare(strict_types=1);

namespace MrPunyapal\Php2fa\Support;

final readonly class RecoveryCode
{
    public static function generate(): string
    {
        return self::randomString(10).'-'.self::randomString(10);
    }

    /**
     * @return array<int, string>
     */
    public static function generateCodes(int $count = 8): array
    {
        return array_map(
            self::generate(...),
            range(1, $count),
        );
    }

    private static function randomString(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $result = '';

        $bytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[ord($bytes[$i]) % $charactersLength];
        }

        return $result;
    }
}
