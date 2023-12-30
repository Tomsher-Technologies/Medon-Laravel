<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{

  protected $fillable = [
    'order_id','order_details_id', 'product_id', 'user_id', 'reason', 'admin_approval', 'offer_price', 'quantity', 'refund_amount', 'refund_status', 'refund_type'
  ];

  protected $with = ['user','order_details','order'];

  public function user()
  {
    return $this->belongsTo(User::class,'user_id','id')->select('id', 'name', 'email');
  }

  public function product()
  {
    return $this->belongsTo(Product::class,'product_id','id');
  }

  public function order()
  {
    return $this->belongsTo(Order::class,'order_id','id');
  }

  public function order_details()
  {
    return $this->belongsTo(OrderDetail::class,'order_details_id','id');
  }

}
