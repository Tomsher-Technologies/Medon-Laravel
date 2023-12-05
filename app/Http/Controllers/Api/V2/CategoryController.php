<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Category;
use Cache;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        $parent_id = $request->parent_id ?? 0;
        $limit = $request->limit ?? 10;

        return new CategoryCollection(Category::where('parent_id', $parent_id)->paginate($limit));
    }

    public function featured()
    {
        return Cache::remember('app.featured_categories', 86400, function () {
            return new CategoryCollection(Category::where('featured', 1)->get());
        });
    }

    public function home()
    {
        return Cache::remember('app.home_categories', 86400, function () {
            return new CategoryCollection(Category::whereIn('id', json_decode(get_setting('home_categories')))->get());
        });
    }

    public function top()
    {
        return Cache::remember('app.top_categories', 86400, function () {
            return new CategoryCollection(Category::whereIn('id', json_decode(get_setting('home_categories')))->limit(20)->get());
        });
    }
}
