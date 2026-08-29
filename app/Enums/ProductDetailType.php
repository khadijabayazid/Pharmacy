<?php

namespace App\Enums;

enum ProductDetailType: string
{
    case Warnings = 'warnings';
    case UsageMethod  = 'usage_method';  
    case Indications  = 'indications';   
    case SideEffects  = 'side_effects';

    public function label(): string
    {
        return match ($this) {
            self::Warnings => 'تحذيرات',
            self::UsageMethod => 'طريقة الاستخدام',
            self::Indications => 'الاستخدامات',
            self::SideEffects => 'الآثار الجانبية',
        };
    }
}
