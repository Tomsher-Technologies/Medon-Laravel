<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Offers;
use App\Models\Product;
use App\Rules\DateRange;
use Illuminate\Http\Request;
use Cache;
use DB;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $offers = Offers::orderBy('id','desc')->paginate(15);
        return view('backend.offers.index', compact('offers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.offers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // echo '<pre>';
        // print_r($request->all());
        // die;
        $request->validate([
            "name" => 'required',
            "link_type" => 'required',
            "link_ref_id" => 'required',
            "image" => 'required',
            "mobile_image" => 'required',
            "offer_type" => 'required',
            // "mobile_image" => 'required',
            'percentage' => 'required_if:offer_type,percentage',
            'amount' => 'required_if:offer_type,amount_off',
            'buy_amount' => 'required_if:offer_type,buy_x_get_y',
            'get_amount' => 'required_if:offer_type,buy_x_get_y',
            'date_range' => ['required', new \App\Rules\DateRange]
        ]);

        $data_range = explode(' to ', $request->date_range);
       
        $offer = Offers::create([
            'name' => $request->name ?? '',
            'link_type' => $request->link_type ?? NULL,
            'category_id' => $request->main_category ?? NULL,
            'link_id' => json_encode($request->link_ref_id) ?? NULL,
            'offer_type' => $request->offer_type ?? NULL,
            'image' => $request->image ?? NULL,
            'mobile_image' => $request->mobile_image ?? NULL,
            'percentage' => $request->percentage ?? NULL,
            'offer_amount' => $request->amount ?? NULL,
            'start_date' => (isset($data_range[0])) ? date('Y-m-d H:i:s', strtotime($data_range[0])) : NULL,
            'end_date' => (isset($data_range[1])) ? date('Y-m-d H:i:s', strtotime($data_range[1])) : NULL,
            'status' => $request->status ?? NULL,
            'buy_amount' => $request->buy_amount ?? NULL,
            'get_amount' => $request->get_amount ?? NULL,
        ]);
       
        Cache::forget('app_offers');
        flash(translate('Offer created successfully'))->success();
        return redirect()->route('offers.index');
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
    public function edit($id)
    {
        $offer = Offers::findOrFail($id);
        return view('backend.offers.edit', compact('offer'));
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
        $request->validate([
            "name" => 'required',
            "link_type" => 'required',
            "link_ref_id" => 'required',
            // "image" => 'required',
            // "mobile_image" => 'required',
            "offer_type" => 'required',
            // "mobile_image" => 'required',
            'percentage' => 'required_if:offer_type,percentage',
            'amount' => 'required_if:offer_type,amount_off',
            'buy_amount' => 'required_if:offer_type,buy_x_get_y',
            'get_amount' => 'required_if:offer_type,buy_x_get_y',
            'date_range' => ['required', new \App\Rules\DateRange]
        ]);

        $offer = Offers::find($id);

        $amount = $percentage = $buy_amount = $get_amount = NULL;
        if($offer->offer_type !== $request->offer_type){
            if($request->offer_type == 'percentage'){
                $percentage = $request->percentage;
            }else if($request->offer_type == 'amount_off'){
                $amount = $request->amount;
            }else if($request->offer_type == 'buy_x_get_y'){
                $buy_amount = $request->buy_amount;
                $get_amount = $request->get_amount;
            }
        }else{
            $amount     = $offer->amount;
            $percentage = $offer->percentage;
            $buy_amount = $offer->buy_amount;
            $get_amount = $offer->get_amount;
        }

        $data_range = explode(' to ', $request->date_range);
      
        $offer->name            =  $request->name ?? NULL;
        $offer->category_id     =  $request->main_category ?? NULL;
        $offer->image           =  $request->image;
        $offer->mobile_image    =  $request->mobile_image;
        $offer->link_type       =  $request->link_type ?? NULL;
        $offer->link_id         =  $request->link_ref_id ?? NULL;
        $offer->offer_type      =  $request->offer_type ?? NULL;
        $offer->percentage      =  $percentage;
        $offer->offer_amount    =  $amount;
        $offer->start_date      =  (isset($data_range[0])) ? date('Y-m-d H:i:s', strtotime($data_range[0])) : NULL;
        $offer->end_date        =  (isset($data_range[1])) ? date('Y-m-d H:i:s', strtotime($data_range[1])) : NULL;
        $offer->status          =  $request->status ?? NULL;
        $offer->buy_amount      =  $buy_amount;
        $offer->get_amount      =  $get_amount;
        $offer->save();

        Cache::forget('app_offers');
        flash(translate('Offer details updated successfully'))->success();
        return redirect()->route('offers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $offer = Offers::findOrFail($id);
        $offer->delete();
        flash(translate('Successfully deleted'))->success();
        return back();
    }

    public function get_form(Request $request)
    {
        $oldArray = $brandsData = $selectedBrands = $catArrays = [];

        $offers = Offers::get();
        $oldCategories = $oldProducts = $oldBrands = [];
        if($offers){
            foreach($offers as $off){
                $link_type = $off->link_type;
                if($link_type == 'category'){
                    $oldBrands[] = json_decode($off->link_id);

                    $prods = Product::where('main_category', $off->category_id)->whereIn('brand_id', json_decode($off->link_id))
                                        ->pluck('id')->toArray();

                    $oldProducts[] = $prods;

                }elseif($link_type == 'product'){
                    $oldProducts[] = json_decode($off->link_id);
                }    
                
                if($off->category_id != null){
                    $catArrays[] = json_decode($off->link_id);
                }
            }
        }
        
        // $oldProducts = (!empty($oldProducts)) ? array_unique(array_merge(...$oldProducts)) : [];
        $catArrays = (!empty($catArrays)) ? array_unique(array_merge(...$catArrays)) : [];
        // // print_r($catArrays);
        // print_r($oldProducts);
        // die;
        $oldProductsEdit = [];
        $offerId = $request->has('offerId') ?  $request->offerId : null;
        if($offerId != null){
            $offerData = Offers::find($offerId);
            if ($request->link_type == $offerData->link_type) {
                if($offerData->link_type == 'category'){
                    $oldArray = $offerData->category_id;
                    $selectedBrands = json_decode($offerData->link_id);

                    $brands = [];

                    $nonBrands = array_diff($catArrays, $selectedBrands);
                    // print_r($selectedBrands);
                    // print_r($nonBrands);
                    // die;
                    $bIdsQuery = Product::where('main_category', $offerData->category_id);
                    if(!empty($nonBrands)){
                        $bIdsQuery->whereNotIn('brand_id', $nonBrands);
                    }
                    
                    $bIds = $bIdsQuery->groupBy('brand_id')->pluck('brand_id')->toArray();
                    if(!empty($bIds)){
                        $brandsData = Brand::select(['id', 'name'])->whereIn('id', $bIds)->get();
                    }
                }else{
                    $oldArray = json_decode($offerData->link_id);
                    $oldProductsEdit = json_decode($offerData->link_id);
                }
            }
        }

        // echo '<pre>';
        // print_r($oldProducts);
       
        $oldProducts = array_unique(array_merge(...$oldProducts));
        $oldProducts = array_diff($oldProducts,$oldProductsEdit);

        // print_r($oldProducts);
        // die;
        if ($request->link_type == "product") {
            $products = Product::select(['id', 'name'])
                                ->whereNotIn('id', $oldProducts)
                                ->get();
            return view('partials.offers.banner_form_product', compact('products', 'oldArray'));
        } elseif ($request->link_type == "category") {
            $categories = Category::where('parent_id', 0)
                                    ->with('childrenCategories')
                                    ->get();
            return view('partials.offers.banner_form_category', compact('categories', 'oldArray','brandsData','selectedBrands'));
        } 
    }

    public function get_brands(Request $request)
    {
        $main_category = $request->input("main_category");
        $oldBrands = [];
        $oldCats = Offers::where('category_id', $main_category)->get();
        if($oldCats){
            foreach($oldCats as $oc){
                $oldBrands[] = json_decode($oc->link_id);
            }
        }
        $oldBrands = array_unique(array_merge(...$oldBrands));

        $brands = [];
       
        $bIdsQuery = Product::where('main_category', $main_category);
        if(!empty($oldBrands)){
            $bIdsQuery->whereNotIn('brand_id', $oldBrands);
        }
        
        $bIds = $bIdsQuery->groupBy('brand_id')->pluck('brand_id')->toArray();
       
        if(!empty($bIds)){
            $brands = Brand::select(['id', 'name'])->whereIn('id', $bIds)->get();
        }

        $html = '';
        foreach($brands as $br){
            $html .= "<option value='".$br->id."'>".$br->name."</option>";
        }
        return $html;
    }
}