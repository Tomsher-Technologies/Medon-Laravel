<?php

namespace App\Models\App;

use App\Models\Category;
use App\Models\Product;
use App\Models\Upload;
use Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'link_type',
        'link_ref',
        'link_ref_id',
        'link',
        'sort_order',
        'status',
    ];

    public function mainImage()
    {
        return $this->hasOne(Upload::class, 'id', 'image');
    }

    public static function boot()
    {
        static::creating(function ($model) {
            Cache::forget('appSlider');
        });

        static::updating(function ($model) {
            Cache::forget('appSlider');
        });

        static::deleting(function ($model) {
            Cache::forget('appSlider');
        });

        parent::boot();
    }
}
