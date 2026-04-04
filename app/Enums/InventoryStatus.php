<?php

namespace App\Enums;

enum InventoryStatus: string
{
    case INSTOCK = 'INSTOCK';
    case OUTOFSTOCK = 'OUTOFSTOCK';
    case PENDING = 'PENDING';

    public static function values(): array
    {
        return array_map(fn($enum) => $enum->value, self::cases());
    }
}
