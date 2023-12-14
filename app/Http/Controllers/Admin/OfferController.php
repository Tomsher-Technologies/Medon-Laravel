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

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $offers = Offers::paginate(15);
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
        $request->validate([
            "name" => 'required',
            "link_type" => 'required',
            "link_ref_id" => 'required',
            // "image" => 'required',
            // "mobile_image" => 'required',
            "offer_type" => 'required',
            // "mobile_image" => 'required',
            "status" => 'required',
            'percentage' => 'required_if:offer_type,percentage',
            'amount' => 'required_if:offer_type,amount_off',
            'buy_amount' => 'required_if:offer_type,buy_x_get_y',
            'get_amount' => 'required_if:offer_type,buy_x_get_y',
            'date_range' => ['required', new \App\Rules\DateRange]
        ]);

        // echo '<pre>';
        // print_r($request->all());
        // die;

        $data_range = explode(' to ', $request->date_range);
       
        $offer = Offers::create([
            'name' => $request->name ?? '',
            'link_type' => $request->link_type ?? NULL,
            'link_id' => json_encode($request->link_ref_id) ?? NULL,
            'offer_type' => $request->offer_type ?? NULL,
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
            "status" => 'required',
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
        //
    }

    public function get_form(Request $request)
    {
        $oldArray = [];
        $offerId = $request->has('offerId') ?  $request->offerId : null;
        if($offerId != null){
            $offerData = Offers::find($offerId);
            if ($request->link_type == $offerData->link_type) {
                $oldArray = json_decode($offerData->link_id);
            }
        }
        
        if ($request->link_type == "product") {
            $products = Product::select(['id', 'name'])->get();
            return view('partials.offers.banner_form_product', compact('products', 'oldArray'));
        } elseif ($request->link_type == "category") {
            $categories = Category::where('parent_id', 0)
                ->with('childrenCategories')
                ->get();
            return view('partials.offers.banner_form_category', compact('categories', 'oldArray'));
        } elseif ($request->link_type == "brand") {
            $brands = Brand::select(['id', 'name'])->get();
           
            return view('partials.offers.banner_form_brand', compact('oldArray', 'brands'));
        } else {
            // return view('partials.offers.banner_form', compact('old_data'));
        }
    }
}
