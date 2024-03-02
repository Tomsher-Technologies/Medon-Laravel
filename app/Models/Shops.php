<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{

  protected $fillable = [
    'name', 'address', 'phone', 'email', 'working_hours', 'delivery_pickup_latitude', 'delivery_pickup_longitude', 'status'
  ];

}
