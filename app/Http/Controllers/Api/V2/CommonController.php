<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SplashScreenCollection;
use App\Models\App\SplashScreens;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Offers;
use App\Models\Frontend\Banner;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use App\Http\Resources\V2\ProductMiniCollection;

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
                'status' => true,
                'message' => "You have been sucessfull subscribed to our newsletter",
            ], 200);
        }

        return response()->json([
            'status' => false,
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
            $categories =  Category::whereIn('id', json_decode($categories_id))->where('is_active', 1)->get();
        }

        $res_category = array();

        foreach ($categories as $category) {
            $temp = array();
            $temp['id'] = $category->id;
            $temp['name'] = $category->name;
            // if ($category->banner) {
            //     $temp['banner'] = api_asset($category->banner);
            // }
            $temp['banner'] = api_upload_asset($category->banner);
            $temp['image'] = api_upload_asset($category->icon);
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
            $brands =  Brand::with(['logoImage'])->whereIn('id', json_decode($brands_id))->get();
        }

        $res_category = array();

        foreach ($brands as $brand) {
            $temp = array();
            $temp['id'] = $brand->id;
            $temp['name'] = $brand->name;
            // if ($brand->logo) {
            //     $temp['logo'] = api_asset($brand->logo);
            // }

            $temp['logo'] = ($brand->logoImage?->file_name) ? storage_asset($brand->logoImage->file_name) : app('url')->asset('admin_assets/assets/img/placeholder.jpg');
            $res_category[] = $temp;
        }

        return response()->json([
            "result" => true,
            'brands' => $res_category
        ]);
    }

    public function homeOffers(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : '';
        $sectionOne = get_setting('app_offer_section_1');
        $sectionTwo = get_setting('app_offer_section_2');

        $today = date('Y-m-d H:i:s');
        $details = [];
        
        $sectionOne = ($sectionOne != NULL) ? json_decode($sectionOne) : [];
        $sectionTwo = ($sectionTwo != NULL) ? json_decode($sectionTwo) : [];
        
        $offersId = array_merge($sectionOne,$sectionTwo);
        
        if($offersId){
            $secOneOffers = Offers::whereIn('id', $offersId)->where('status',1)->get();
            foreach ($secOneOffers as $secOne) {
                if(strtotime($today) >= strtotime($secOne->start_date) && (strtotime($today) < strtotime($secOne->end_date))){
                    $temp = array();
                    $temp['id'] = $secOne->id;
                    $temp['name'] = $secOne->name;
                    $temp['type'] = $secOne->link_type;

                    if ($secOne->link_type == 'product') {
                        $result = array();
                        $product_query  = Product::whereIn('id', json_decode($secOne->link_id))->wherePublished(1);
                        if($limit != ''){
                            $product_query->skip(0)->take($limit);
                        }
                        $products = $product_query->get();

                        foreach ($products as $prod) {
                            $tempProducts = array();
                            $tempProducts['id'] = $prod->id;
                            $tempProducts['name'] = $prod->name;
                            $tempProducts['image'] = get_product_image($prod->thumbnail_img,'300');
                            $tempProducts['sku'] = $prod->sku;
                            $tempProducts['main_price'] = home_discounted_base_price_wo_currency($prod);
                            $result[] = $tempProducts;
                        }

                    }elseif ($secOne->link_type == 'brand') {
                        $brandQuery =  Brand::with(['logoImage'])->whereIn('id', json_decode($secOne->link_id));
                        if($limit != ''){
                            $brandQuery->skip(0)->take($limit);
                        }
                        $brands = $brandQuery->get();
                        
                        $result = array();

                        foreach ($brands as $brand) {
                            $tempBrands = array();
                            $tempBrands['id'] = $brand->id;
                            $tempBrands['name'] = $brand->name;
                            $tempBrands['image'] = storage_asset($brand->logoImage->file_name);
                            $result[] = $tempBrands;
                        }
                    }elseif ($secOne->link_type == 'category') {
                        $categoriesQuery =  Category::whereIn('id', json_decode($secOne->link_id));

                        if($limit != ''){
                            $categoriesQuery->skip(0)->take($limit);
                        }
                        $categories = $categoriesQuery->where('is_active', 1)->get();
                        $result = array();

                        foreach ($categories as $category) {
                            $tempCats = array();
                            $tempCats['id'] = $category->id;
                            $tempCats['name'] = $category->name;
                            $tempCats['image'] = api_upload_asset($category->icon);
                            $result[] = $tempCats;
                        }
                    }
                    $temp['data'] = $result;
                    if(in_array($secOne->id, $sectionOne) ){
                        $details['offer_section_1'][] = $temp;
                    }elseif (in_array($secOne->id, $sectionTwo)) {
                        $details['offer_section_2'][] = $temp;
                    }
                }
               
            }
        }

        return response()->json([
            "success" => true,
            "status" => 200,
            "data" => $details
        ]);
    }

    public function offerDetails(Request $request){
        $offerid = $request->offer_id;
        $limit = $request->has('limit') ? $request->limit : '';
        $offset = $request->has('offset') ? $request->offset : 0;
        if($offerid != ''){
            $Offer = Offers::where('status',1)->find($offerid);
            if(!$Offer){
                return response()->json(['success' => false,"message"=>"No Data Found!","data" => []],400);
            }else {
                $temp = array();
                $temp['id'] = $Offer->id;
                $temp['name'] = $Offer->name;
                $temp['type'] = $Offer->link_type;
    
                if ($Offer->link_type == 'product') {
                    $result = array();
                    $product_query  = Product::whereIn('id', json_decode($Offer->link_id))->wherePublished(1);
                    if($limit != ''){
                        $product_query->skip($offset)->take($limit);
                    }
                    $products = $product_query->get();

                    foreach ($products as $prod) {
                        $tempProducts = array();
                        $tempProducts['id'] = $prod->id;
                        $tempProducts['name'] = $prod->name;
                        $tempProducts['image'] = get_product_image($prod->thumbnail_img,'300');
                        $tempProducts['sku'] = $prod->sku;
                        $tempProducts['main_price'] = home_discounted_base_price_wo_currency($prod);
                        $tempProducts['min_qty'] = $prod->min_qty;
                        $tempProducts['slug'] = $prod->slug;
                        
                        $result[] = $tempProducts;
                    }
                }elseif ($Offer->link_type == 'brand') {
                    $brandQuery =  Brand::with(['logoImage'])->whereIn('id', json_decode($Offer->link_id));
                    if($limit != ''){
                        $brandQuery->skip($offset)->take($limit);
                    }
                    $brands = $brandQuery->get();
                    $result = array();
                    foreach ($brands as $brand) {
                        $tempBrands = array();
                        $tempBrands['id'] = $brand->id;
                        $tempBrands['name'] = $brand->name;
                        $tempBrands['image'] = storage_asset($brand->logoImage->file_name);
                        $result[] = $tempBrands;
                    }
                }elseif ($Offer->link_type == 'category') {
                    $categoriesQuery =  Category::whereIn('id', json_decode($Offer->link_id));
                    if($limit != ''){
                        $categoriesQuery->skip($offset)->take($limit);
                    }
                    $categories = $categoriesQuery->where('is_active', 1)->get();
                    $result = array();
                    foreach ($categories as $category) {
                        $tempCats = array();
                        $tempCats['id'] = $category->id;
                        $tempCats['name'] = $category->name;
                        $tempCats['image'] = api_upload_asset($category->icon);
                        $result[] = $tempCats;
                    }
                }
                $temp['list'] = $result;
                $temp['next_offset'] = $offset + $limit;
                return response()->json(['success' => true,"message"=>"Data fetched successfully!","data" => $temp],200);
            }
        }else{
            return response()->json(['success' => false,"message"=>"No Data Found!","data" => []],400);
        }
    }

    public function homeAdBanners()
    {
        $all_banners = Banner::with(['mobileImage'])->where('status', true)->get();

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
                    if(!empty($c_banner)){
                        if($c_banner->mobileImage){
                            $banners[$banner->type][] = array(
                                // 'image1' => $c_banner,
                                'link_type' => $c_banner->link_type ?? '',
                                'link_id' => $c_banner->link_type == 'external' ? $c_banner->link : $c_banner->link_ref_id,
                                'image' => ($c_banner->mobileImage) ? storage_asset($c_banner->mobileImage->file_name) : ''
                            );
                        }
                    }else{
                        $banners[$banner->type][] = null;
                    }
                    
                }
            }
        }

        return response()->json([
            "result" => true,
            "data" => $banners,
        ]);
    }

}
