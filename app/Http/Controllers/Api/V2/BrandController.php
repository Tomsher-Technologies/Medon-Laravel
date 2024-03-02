<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\BrandCollection;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use Cache;
use DB;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $category_slug = $request->has('category') ? $request->category : '';
        $brand_ids = [];
        if ($category_slug != '') {
            $category = Category::where('slug',$category_slug)->first()?->id;
           
            $childIds = [];
            $childIds[] = array($category);
            
            $childIds[] = getChildCategoryIds($category);

            if(!empty($childIds)){
                $childIds = array_merge(...$childIds);
                $childIds = array_unique($childIds);
            }
         
            $brand_ids = Product::select(DB::raw('GROUP_CONCAT(DISTINCT brand_id) as brand_ids'))->whereIn('category_id', $childIds)->first()?->brand_ids;
           
            if($brand_ids != ''){
                $brand_ids = explode(',',$brand_ids);
            }
        }

        $brand_query = Brand::query();
        if(!empty($brand_ids)){
            $brand_query->whereIn('id', $brand_ids);
        }
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
