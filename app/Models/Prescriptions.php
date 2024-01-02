<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescriptions extends Model
{
    protected $fillable = [
        'user_id', 'emirates_id_front', 'emirates_id_back', 'prescription', 'comment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

}
