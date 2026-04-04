<?php

namespace App\Enums;

enum ProductCategories: string
{
    case ACCESORIES     = 'ACCESORIES';
    case WATCHES        = 'WATCHES';
    case ELECTRONICS    = 'ELECTRONICS';

    public static function values(): array
    {
        return array_map(fn($enum) => $enum->value, self::cases());
    }
}
