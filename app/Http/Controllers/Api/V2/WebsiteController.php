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
use App\Models\Frontend\HomeSlider;
use App\Models\Subscriber;
use App\Http\Resources\V2\WebHomeCategoryCollection;
use App\Http\Resources\V2\WebHomeBrandCollection;
use App\Http\Resources\V2\WebHomeOffersCollection;
use App\Http\Resources\V2\WebHomeProductsCollection;
use Illuminate\Http\Request;
use Cache;

class WebsiteController extends Controller
{
    public function websiteHeader(){
        $data = [];
        $data['brands'] = Brand::select('id','name','logo','slug')->orderBy('name','asc')->get();
        $data['categories'] = Category::with(['childrenCategories'])->select('id','name','slug')->where('parent_id',0)->orderBy('name', 'ASC')->get();
            // dd($data);
            
        return response()->json($data, 200);

    } 

    public function websiteHome(){
        $data['slider'] = Cache::rememberForever('homeSlider', function () {
                        $slider = [];
                        $sliders = HomeSlider::whereStatus(1)->with(['mainImage'])->orderBy('sort_order')->get();
                        if ($sliders) {
                            foreach ($sliders as $slid) {
                                $slider[] = [
                                    'id' => $slid->id,
                                    'name' => $slid->name,
                                    'type' => $slid->link_type,
                                    'link' => $slid->getBannerLink(),
                                    'type_id' => $slid->link_ref_id,
                                    'sort_order' => $slid->sort_order,
                                    'status' => $slid->status,
                                    'image' => api_upload_asset($slid->image)
                                ];
                            }
                            return $slider;
                        }
        });

        $data['top_categories'] = Cache::rememberForever('top_categories', function () {
            $categories = get_setting('home_categories');
            if ($categories) {
                $details = Category::whereIn('id', json_decode($categories))
                    ->with(['icon'])
                    ->get();
                return new WebHomeCategoryCollection($details);
            }
        });

        $data['top_brands'] = Cache::rememberForever('top_brands', function () {
            $brands = get_setting('home_brands');
            if ($brands) {
                $details = Brand::whereIn('id', json_decode($brands))->get();
                return new WebHomeBrandCollection($details);
            }
        });

        $data['best_selling'] = Cache::remember('best_selling_products', 3600, function () {
            $product_ids = get_setting('best_selling');
            if ($product_ids) {
                $products =  Product::where('published', 1)->whereIn('id', json_decode($product_ids))->with('brand')->get();
                return new WebHomeProductsCollection($products);
            }
        });

        $data['offers'] = Cache::rememberForever('home_offers', function () {
            $offers = get_setting('home_offers');
            if ($offers) {
                $details = Offers::whereIn('id', json_decode($offers))->get();
                return new WebHomeOffersCollection($details);
            }
        });

        $home_banners = BusinessSetting::whereIn('type', array('home_banner_1', 'home_banner_2', 'home_banner_3'))->get()->keyBy('type');
        $banners = [];
        foreach($home_banners as $key => $hb){
            $banners[$key] = json_decode($hb->value);
        }

        // $data['banners'] = $banners;

        return response()->json(['success' => true,"message"=>"Success","data" => $data],200);
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
                        $tempProducts['image'] = app('url')->asset($prod->thumbnail_img);
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
                    $categories = $categoriesQuery->get();
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
        $all_banners = Banner::with(['mainImage'])->where('status', true)->get();

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
                        'link_type' => $c_banner->link_type ?? '',
                        'link_id' => $c_banner->link_type == 'external' ? $c_banner->link : $c_banner->link_ref_id,
                        'image' => storage_asset($c_banner->mainImage->file_name)
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
