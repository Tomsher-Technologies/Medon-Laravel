<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offers extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "link_type",
        "link_id",
        "image",
        "mobile_image",
        "offer_type",
        "offer_amount",
        "start_date",
        "end_date",
        "status",
        "buy_amount",
        "get_amount",
    ];

    protected $casts = [
        'end_date' => 'datetime:Y-m-d h:m:s',
        'start_date' => 'datetime:Y-m-d h:m:s',
    ];
}
