<?php

namespace App\Http\Controllers\Admin\App;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Frontend\Banner;
use Cache;
use Illuminate\Http\Request;

class AppHomeController extends Controller
{
    public function index()
    {
        $categories = Cache::rememberForever('categories', function () {
            return Category::where('parent_id', 0)->with('childrenCategories')->get();
        });

        $brands = Cache::rememberForever('brands', function () {
            return Brand::get();
        });

        $banners = Banner::where('status', 1)->get();

        return view('backend.app.index', compact('categories', 'brands', 'banners'));
    }


    public function updateBanners(Request $request)
    {
        dd($request);
    }
}
