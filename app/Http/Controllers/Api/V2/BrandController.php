<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\BrandCollection;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use Cache;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brand_query = Brand::query();
        $limit = $request->limit ?? 10;
        return new BrandCollection($brand_query->paginate($limit));
    }

    public function top()
    {
        $brands = Cache::rememberForever('home_brands', function () {
            $brand_ids = get_setting('top10_brands');
            if ($brand_ids) {
                return Brand::whereIn('id', json_decode($brand_ids))->with('logoImage')->get();
            }
        });
        return new BrandCollection($brands);
    }
}
