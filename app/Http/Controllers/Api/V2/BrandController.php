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
        $limit = $request->has('limit') ? $request->limit : '';
        $query = ($limit != '') ? $brand_query->where('is_active', 1)->orderBy('name','ASC')->paginate($limit) : $brand_query->where('is_active', 1)->orderBy('name','ASC')->get();
        return new BrandCollection($query);
    }

    public function top()
    {
        $brands = Cache::rememberForever('home_brands', function () {
            $brand_ids = get_setting('top10_brands');
            if ($brand_ids) {
                return Brand::whereIn('id', json_decode($brand_ids))->where('is_active', 1)->with('logoImage')->get();
            }
        });
        return new BrandCollection($brands);
    }
}
