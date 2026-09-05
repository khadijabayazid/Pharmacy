<?php

namespace App\Enums;

enum RejectionReason: string
{
    case PrescriptionMismatch = 'prescription_mismatch';
    case PrescriptionUnclear = 'pharmacy_closed';

    public function label(): string
    {
        return match ($this){
            self::PrescriptionMismatch => 'الوصفة الطبية غير متطابقة',
            self::PrescriptionUnclear => 'الصيدلية مغلقة للصيانة',
        };
    }   
    
}
