<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Frontend\Banner;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Product;
use App\Models\Offers;
use App\Models\Faqs;
use App\Models\Contacts;
use Cache;
use Str;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.website_settings.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $page = new Page;
        $page->title = $request->title;
        if (Page::where('slug', Str::slug($request->slug))->first() == null) {
            $page->slug             = Str::slug($request->slug);
            $page->type             = "custom_page";
            $page->content          = $request->content;
            $page->meta_title       = $request->meta_title;
            $page->meta_description = $request->meta_description;
            $page->keywords         = $request->keywords;
            $page->meta_image       = $request->meta_image;
            $page->save();

            flash(translate('New page has been created successfully'))->success();
            return redirect()->route('website.pages');
        }

        flash(translate('Slug has been used already'))->warning();
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $page_name = $request->page;
        $page = Page::where('type', $id)->first();
        if ($page != null) {
            if ($page->type == 'home_page') {
                $banners = Banner::where('status', 1)->get();
                $current_banners = BusinessSetting::whereIn('type', array('home_banner_1','home_banner_2','home_banner_3','home_banner', 'home_ads_banner', 'home_large_banner'))->get()->keyBy('type');

                $categories = Cache::rememberForever('categories', function () {
                    return Category::where('parent_id', 0)->with('childrenCategories')->get();
                });

                $products = Product::select('id', 'name')->get();
                $brands = Brand::all();
                $offers = Offers::select('id', 'name')->where('end_date','>',now())->get();

                return view('backend.website_settings.pages.home_page_edit', compact('page', 'banners', 'current_banners', 'categories', 'brands', 'products','offers'));
            } elseif($page->type == 'terms_conditions' || $page->type == 'privacy_policy' || $page->type == 'return_refund' || $page->type == 'shipping_delivery'){
                return view('backend.website_settings.pages.edit', compact('page'));
            } elseif ($page->type == 'store_locator') {
                return view('backend.website_settings.pages.store_locator', compact('page'));
            }elseif ($page->type == 'faq') {
                $questions = Faqs::orderBy('sort_order','asc')->get();
                return view('backend.website_settings.pages.faq', compact('page','questions'));
            }elseif ($page->type == 'contact_us') {
                return view('backend.website_settings.pages.contact_us', compact('page'));
            }elseif ($page->type == 'prescriptions') {
                return view('backend.website_settings.pages.store_locator', compact('page'));
            }
            else {
                return view('backend.website_settings.pages.edit', compact('page'));
            }
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        // echo '<pre>';
        // print_r($request->all());
        // die;

        // preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug))

        if (Page::where('id', '!=', $id)->where('type', $request->type)->first() == null) {
            // if ($page->type == 'custom_page') {
            // $page->slug = Str::slug($request->slug);
            // }    

            $page->title                = $request->title;
            $page->content              = $request->has('content') ? $request->content : NULL;
            $page->sub_title            = $request->has('sub_title') ? $request->sub_title : NULL;
            $page->meta_title           = $request->meta_title;
            $page->meta_description     = $request->meta_description;
            $page->keywords             = $request->keywords;
            $page->meta_image           = $request->meta_image;
            $page->og_title             = $request->og_title;
            $page->og_description       = $request->og_description;
            $page->twitter_title        = $request->twitter_title;
            $page->twitter_description  = $request->twitter_description;

            $page->heading1             = $request->has('heading1') ? $request->heading1 : NULL;
            $page->heading2             = $request->has('heading2') ? $request->heading2 : NULL;
            $page->heading3             = $request->has('heading3') ? $request->heading3 : NULL;
            $page->heading4             = $request->has('heading4') ? $request->heading4 : NULL;
            $page->heading5             = $request->has('heading5') ? $request->heading5 : NULL;

            $page->save();

            if($request->type == 'faq'){
                Faqs::truncate();
                $data = [];
                foreach ($request->faq as $value) {
                    $data[] = array(
                        "question" => $value['question'] ?? NULL,
                        "answer"   => $value['answer'] ?? NULL,
                        "sort_order" =>  $value['sort_order'] ?? NULL,
                    );
                }
                if(!empty($data)){
                    Faqs::insert($data);
                }
            }
            flash(translate('Page has been updated successfully'))->success();
            return redirect()->route('website.pages');
        }

        flash(translate('Slug has been used already'))->warning();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        foreach ($page->page_translations as $key => $page_translation) {
            $page_translation->delete();
        }
        if (Page::destroy($id)) {
            flash(translate('Page has been deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }

    public function show_custom_page($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        if ($page != null) {
            load_seo_tags($page);

            if ($page->type == 'careers_page') {
                return view('frontend.career_page', compact('page'));
            }
            if ($page->type == 'contact_page') {
                return view('frontend.contact_us', compact('page'));
            }
            if ($page->type == 'request_quote') {
                return view('frontend.request_quote', compact('page'));
            }
            if ($page->type == 'about_us') {
                return view('frontend.about_us', compact('page'));
            }

            return view('frontend.custom_page', compact('page'));
        }
        abort(404);
    }

    public function mobile_custom_page($slug)
    {
        $page = Page::where('slug', $slug)->first();
        if ($page != null) {
            return view('frontend.m_custom_page', compact('page'));
        }
        abort(404);
    }

    public function enquiries(){
        $query = Contacts::latest();
        $contact = $query->paginate(20);

        return view('backend.contact', compact('contact'));
    }
}
