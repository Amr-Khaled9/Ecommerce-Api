<?php

namespace App\Models;

use App\Enum\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'to_status',
        'form_status',
        'notes'
    ];

    protected $casts =[
        'to_status'=> OrderStatus::class ,
        'from_status'=> OrderStatus::class ,
    ];

    public function order(): BelongsTo{
        return $this->belongsTo(Order::class , 'order_id');
    }
    public function user (): BelongsTo{
        return $this->belongsTo(USer::class , 'user_id');
    }
}
