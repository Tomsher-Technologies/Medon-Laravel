<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Offers;
use App\Models\Product;
use App\Rules\DateRange;
use Illuminate\Http\Request;

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
            "image" => 'required',
            "mobile_image" => 'required',
            "offer_type" => 'required',
            "mobile_image" => 'required',
            "status" => 'required',
            'percentage' => 'required_if:offer_type,percentage',
            'amount' => 'required_if:offer_type,amount_off',
            'buy_amount' => 'required_if:offer_type,buy_x_get_y',
            'get_amount' => 'required_if:offer_type,buy_x_get_y',
            'date_range' => ['required', new \App\Rules\DateRange]
        ]);


        $data_range = explode(' to ', $request->date_range);
        dd($data_range);
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
        //
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
        //
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
        $old_data = $request->old_data ?? null;
        if ($request->link_type == "product") {
            $products = Product::select(['id', 'name'])->get();
            return view('partials.offers.banner_form_product', compact('products', 'old_data'));
        } elseif ($request->link_type == "category") {
            $categories = Category::where('parent_id', 0)
                ->with('childrenCategories')
                ->get();
            return view('partials.offers.banner_form_category', compact('categories', 'old_data'));
        } elseif ($request->link_type == "brand") {
            $brands = Brand::select(['id', 'name'])->get();
            return view('partials.offers.banner_form_brand', compact('old_data', 'brands'));
        } else {
            // return view('partials.offers.banner_form', compact('old_data'));
        }
    }
}
