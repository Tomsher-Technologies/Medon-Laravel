<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryBoys extends Model
{
    protected $fillable = [
        'order_id', 'delivery_boy_id', 'status', 'delivery_note', 'delivery_image', 'delivery_date'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id','id');
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class,'delivery_boy_id','id');
    }
}
