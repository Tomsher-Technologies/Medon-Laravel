<?php

namespace App\Models\App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplashScreens extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'sort_order',
        'status',
    ];
}
