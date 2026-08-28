<?php

namespace App\Enums;

enum StockStatus: string
{
    case InStock = 'in_stock';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'متوفر',
            self::LowStock => 'كمية قليلة',
            self::OutOfStock => 'غير متوفر',
        };
    }
    
}
