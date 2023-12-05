<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SplashScreenCollection;
use App\Models\App\SplashScreens;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Frontend\Banner;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function newsletter(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Please enter your email',
            'email.email' => 'Please enter a valid email'
        ]);

        $sub =  Subscriber::updateOrCreate([
            'email' => $request->email
        ]);

        if ($sub->wasRecentlyCreated) {
            return response()->json([
                'result' => true,
                'status' => 1,
                'message' => "You have been sucessfull subscribed to our newsletter",
            ], 200);
        }

        return response()->json([
            'result' => true,
            'status' => 0,
            'message' => "You are aleardy subscribed to our newsletter",
        ], 200);
    }

    public function footer()
    {
        return response()->json([
            'result' => true,
            'app_links' => array([
                'play_store' => array([
                    'link' => get_setting('play_store_link'),
                    'image' => api_asset(get_setting('play_store_image')),
                ]),
                'app_store' => array([
                    'link' => get_setting('app_store_link'),
                    'image' => api_asset(get_setting('app_store_image')),
                ]),
            ]),
            'social_links' => array([
                'facebook' => get_setting('facebook_link'),
                'twitter' => get_setting('twitter_link'),
                'instagram' => get_setting('instagram_link'),
                'youtube' => get_setting('youtube_link'),
                'linkedin' => get_setting('linkedin_link'),
            ]),
            'copyright_text' => get_setting('frontend_copyright_text'),
            'contact_phone' => get_setting('contact_phone'),
            'contact_email' => get_setting('contact_email'),
            'contact_address' => get_setting('contact_address'),
        ]);
    }

    public function splash_screen()
    {
        $screens = SplashScreens::where('status', 1)->orderBy('sort_order')->get();

        return new SplashScreenCollection($screens);
    }

    public function homeTopCategory()
    {
        $categories_id = get_setting('app_top_categories');

        if ($categories_id) {
            $categories =  Category::whereIn('id', json_decode($categories_id))->get();
        }

        $res_category = array();

        foreach ($categories as $category) {
            $temp = array();
            $temp['id'] = $category->id;
            $temp['name'] = $category->name;
            if ($category->banner) {
                $temp['banner'] = api_asset($category->banner);
            }
            $res_category[] = $temp;
        }

        return response()->json([
            "result" => true,
            'categories' => $res_category
        ]);
    }
    public function homeTopBrand()
    {
        $brands_id = get_setting('app_top_brands');

        if ($brands_id) {
            $brands =  Brand::whereIn('id', json_decode($brands_id))->get();
        }

        $res_category = array();

        foreach ($brands as $brand) {
            $temp = array();
            $temp['id'] = $brand->id;
            $temp['name'] = $brand->name;
            if ($brand->logo) {
                $temp['logo'] = api_asset($brand->logo);
            }
            $res_category[] = $temp;
        }

        return response()->json([
            "result" => true,
            'brands' => $res_category
        ]);
    }

    public function homeAdBanners()
    {
        $all_banners = Banner::where('status', true)->get();

        $banner_id = BusinessSetting::whereIn('type', [
            'app_banner_1',
            'app_banner_2',
            'app_banner_3',
            'app_banner_4',
            'app_banner_5',
            'app_banner_6',
        ])->get();

        $banners = array();

        foreach ($banner_id as $banner) {
            $ids = json_decode($banner->value);
            if ($ids) {
                foreach ($ids as $id) {
                    $c_banner = $all_banners->where('id', $id)->first();
                    $banners[$banner->type][] = array(
                        // 'image1' => $c_banner,
                        'link_type' => $c_banner->link_type,
                        'link_id' => $c_banner->link_type == 'external' ? $c_banner->link : $c_banner->link_ref_id,
                        'image' => api_asset($c_banner->image)
                    );
                }
            }
        }

        return response()->json([
            "result" => true,
            "data" => $banners,
        ]);
    }
}
