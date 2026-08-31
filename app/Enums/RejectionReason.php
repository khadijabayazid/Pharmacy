<?php

namespace App\Enums;

enum RejectionReason: string
{
    case PrescriptionMismatch = 'prescription_mismatch';
    case PrescriptionUnclear = 'prescription_unclear';

    public function label(): string
    {
        return match ($this){
            self::PrescriptionMismatch => 'الوصفة الطبية غير متطابقة',
            self::PrescriptionUnclear => 'الوصفة الطبية غير واضحة',
        };
    }   
    
}
