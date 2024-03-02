<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

  protected $fillable = [
    'product_id',
    'user_id',
    'rating',
    'comment',
    'status',
    'viewed',
  ];

  protected $with = ['user'];

  public function user()
  {
    return $this->belongsTo(User::class)->select('id', 'name', 'email');
  }

  public function product()
  {
    return $this->belongsTo(Product::class,'product_id','id');
  }
}
