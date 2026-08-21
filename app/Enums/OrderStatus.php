<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Accepted   = 'accepted';
    case Rejected   = 'rejected';
    case OnDelivery = 'on_delivery';
    case Delivered  = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الانتظار',
            self::Accepted => 'مقبول',
            self::Rejected => 'مرفوض',
            self::OnDelivery => 'قيد التوصيل',
            self::Delivered => 'تم التوصيل',
        };
    }
}
