<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\BannerCollection;
use App\Models\Frontend\HomeSlider;
use Cache;

class BannerController extends Controller
{

    public function index()
    {
        $sliders = Cache::rememberForever('homeSlider', function () {
            $banners = HomeSlider::whereStatus(1)->with(['mainImage', 'mobileImage'])->orderBy('sort_order')->get();
            if ($banners) {
                foreach ($banners as $banner) {
                    $banner->a_link = $banner->getALink();
                }
                return $banners;
            }
        });

        if ($sliders) {
            return new BannerCollection($sliders);
        }

        return response()->json([
            'success' => false,
            'message' => "No Banners Found",
        ], 404);
    }
}
