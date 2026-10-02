<?php

namespace App\Support;

final class Fmt
{
    /** Nombre au format français (espace insécable pour les milliers). */
    public static function n(float|int|string|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', "\u{00A0}");
    }

    /** Quantité : sans décimales si la valeur est entière. */
    public static function q(float|int|string|null $value): string
    {
        $value = (float) $value;

        return self::n($value, fmod($value, 1.0) == 0.0 ? 0 : 2);
    }
}
