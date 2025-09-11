<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPING = 'shipping'; // order sent to delivery
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    //     function معموله عشان ترجع الداتا ب الشكل ده  
    //     [
    //     OrderStatus::PENDING,
    //     OrderStatus::PAID,
    //     OrderStatus::PROCESSING,
    //     OrderStatus::SHIPPING,
    //     OrderStatus::DELIVERED,
    //     OrderStatus::CANCELLED,
    // ]
    //const عشان تعرف تنادي علي اي حاجه self  طبعا ب  
    //بسvalus  هوا هنا هيخرج 
}
