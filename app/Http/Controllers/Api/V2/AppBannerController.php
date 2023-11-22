<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\AppBannerCollection;
use App\Models\App\AppBanner;
use Cache;

class AppBannerController extends Controller
{

    public function index()
    {
        Cache::forget('appSlider');
        $sliders = Cache::rememberForever('appSlider', function () {
            $banners = AppBanner::whereStatus(1)->with(['mainImage'])->orderBy('sort_order')->get();
            if ($banners) {
                return $banners;
            }
        });

        if ($sliders) {
            return new AppBannerCollection($sliders);
        }

        return response()->json([
            'success' => false,
            'message' => "No Banners Found",
        ], 404);
    }
}
