<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopNotifications extends Model
{
    protected $fillable = [
        'shop_id', 'type', 'order_id', 'message', 'is_read'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

}
