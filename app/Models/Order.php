<?php

namespace App\Models;

use Dflydev\DotAccessData\Data;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'shipping_name',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zipcode',
        'shipping_country',
        'shipping_phone',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'payment_method',
        'payment_status',
        'transaction_id',
        'paid_at',
        'order_number',
        'notes',
        'transaction_id',
        'paid_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Define the relationship with the OrderItem model
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public static function generateOrderNumber()
    {
        $year =date('Y');
        $month =date('m');
        $day =date('d');
        $randomNumber = strtoupper(substr(uniqid(),-6));
        return 'FCB-'.$year.'-'.$month.'-'.$day.'-'.$randomNumber;
    }
}
