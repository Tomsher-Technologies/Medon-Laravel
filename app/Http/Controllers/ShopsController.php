<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Http\Request;
use Hash;
use Validator;

class ShopsController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        if ($request->has('search')) {
            $sort_search = $request->search;
        }

        $query = Shops::select("*");
        if($sort_search){  
            $query->Where(function ($query) use ($sort_search) {
                    $query->orWhere('name', 'LIKE', "%$sort_search%")
                    ->orWhere('address', 'LIKE', "%$sort_search%")
                    ->orWhere('phone', 'LIKE', "%$sort_search%")
                    ->orWhere('email', 'LIKE', "%$sort_search%");
            });                    
        }
                        
        $query->orderBy('id','DESC')->get();

        $shops = $query->paginate(20);
        return view('backend.shop.index', compact('shops', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.shop.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'lat' => 'required',
            'long' => 'required'
        ]);
 
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $shop = Shops::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'working_hours' => $request->working_hours,
            'delivery_pickup_latitude' => $request->lat,
            'delivery_pickup_longitude' => $request->long,
            'status' => 1,
        ]);
        
        flash(translate('Shop has been created successfully'))->success();
        return redirect()->route('admin.shops.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function edit($shop)
    {
        $shops = Shops::where('id','=',$shop)->get();
               
        return view('backend.shop.edit', compact('shops'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'lat' => 'required',
            'long' => 'required'
        ]);
 
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $shop = Shops::find($id);
        $shop->name = $request->name;
        $shop->address = $request->address;
        $shop->phone = $request->phone;
        $shop->email = $request->email;
        $shop->working_hours = $request->working_hours;
        $shop->delivery_pickup_latitude = $request->lat;
        $shop->delivery_pickup_longitude = $request->long;
        $shop->status = ($request->has('status')) ? 1 :0;
      
        if($shop->save()) {
            flash(translate('Shop details has been updated successfully'))->success();
        }else{
            flash(translate('Something went wrong'))->error();
        }
        return redirect()->route('admin.shops.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
    {
        // $shop->delete();
        // flash(translate('Shop has been successfully deleted'))->success();
        // return redirect()->route('admin.shops.index');
    }

    public function bulk_shop_delete(Request $request)
    {
        // $users = [];
        // if ($request->id) {
        //     foreach ($request->id as $shop_id) {
        //         Shop::destroy($shop_id);
        //         $shopusers = ShopUsers::where('shop_id',$shop_id)->get();
        //         $user_id = $shopusers[0]->user_id;
        //         ShopUsers::where('shop_id',$shop_id)->delete();
        //         User::where('id',$user_id)->delete();
        //     }
        // }

        // return 1;
    }
    public function delete(Request $request)
    {
        // $shop_id = $request->id;
        // Shop::destroy($shop_id);
        // $shopusers = ShopUsers::where('shop_id',$shop_id)->get();
        // $user_id = $shopusers[0]->user_id;
        // ShopUsers::where('shop_id',$shop_id)->delete();
        // User::where('id',$user_id)->delete();
        // return 1;
    }
}
