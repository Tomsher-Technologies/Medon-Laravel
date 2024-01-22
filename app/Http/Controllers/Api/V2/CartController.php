<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CartCollection;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user_id = '';
        if (auth('sanctum')->user()) {
            $user_id = auth('sanctum')->user()->id;
            if ($request->header('UserToken')) {
                Cart::where('temp_user_id', $request->header('UserToken'))
                    ->update(
                        [
                            'user_id' => $user_id,
                            'temp_user_id' => null
                        ]
                    );
            }
            $carts = Cart::where('user_id', $user_id)->orderBy('id','asc')->get();
            
            if(!empty($carts[0])){
                $carts->load(['product', 'product.stocks']);
            }
            
        } else {
            $temp_user_id = $request->header('UserToken');
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->orderBy('id','asc')->get() : [];
    
            if(!empty($carts[0])){
                $carts->load(['product', 'product.stocks']);
            }
        }

        $result = [];
        $sub_total = $discount = $shipping = $coupon_display = $coupon_discount = $offerIdCount = $total_coupon_discount = 0;
        $coupon_code = $coupon_applied = null;

        $overall_subtotal = $total_discount = $total_tax = $total_shipping = $cart_coupon_discount = 0;
        $cart_coupon_code = $cart_coupon_applied = NULL;
        
        if(!empty($carts[0])){

            $buyXgetYOfferProducts = getActiveBuyXgetYOfferProducts();

            // echo '<pre>';
            // print_r($buyXgetYOfferProducts);
            // die;
            $off = [];

            foreach($carts as $data){
                $priceData = getProductOfferPrice($data->product);
                
                $updateCart = Cart::find($data->id);
                $updateCart->price = $priceData['original_price'];
                $updateCart->offer_price = $priceData['discounted_price'];
                $updateCart->offer_id = ($priceData['offer_id'] >= 0) ? $priceData['offer_id'] : NULL;
                $updateCart->offer_tag = ($priceData['offer_id'] >= 0) ? $priceData['offer_tag'] : NULL;
                $updateCart->offer_discount = 0.00;
                $updateCart->tax = (($priceData['discounted_price'] * $updateCart->quantity)/100) * $updateCart->product->vat;
                $updateCart->discount = 0.00;
                $updateCart->coupon_code = NULL;
                $updateCart->coupon_applied = 0;
                $updateCart->save();

                if($priceData['offer_type'] == 'buy_x_get_y'){
                    $quantity = $data->quantity;
                    for($i=0; $i<$quantity; $i++){
                        $off[$priceData['offer_id']][] = [
                            'cart_id' => $data->id,
                            'product_id' => $data->product_id,
                            'price' => $priceData['original_price']
                        ];
                    }
                }
            }
            
            // echo 'Offer products  *******************************************************************';
            // // print_r($off);
            // echo 'Offer products Calculations *******************************************************************';
            foreach($off as $ofkey => $of){
                // echo '<br>X =>'. $xCount = $buyXgetYOfferProducts[$ofkey]['x'];
                // echo '<br>Y =>'. $yCount = $buyXgetYOfferProducts[$ofkey]['y'];
                // echo '<br>totalXY =>'. $totalXY = $xCount + $yCount;
                // echo '<br>totalProd =>'. $totalProd = count($of);
                // // echo '<br>freeItemsCount =>'. $freeItemsCount = floor($totalProd/$totalXY)*$buyXgetYOfferProducts[$ofkey]['y'];

                // echo '<br>freeItemsCount =>'. $freeItemsCount = calculateFreeItems($totalProd, $xCount, $yCount);
             
                $xCount = $buyXgetYOfferProducts[$ofkey]['x'];
                $yCount = $buyXgetYOfferProducts[$ofkey]['y'];
                $totalXY = $xCount + $yCount;
                $totalProd = count($of);
                $freeItemsCount = calculateFreeItems($totalProd, $xCount, $yCount);

                // print_r($of);
                // echo 'Negative  =========    ' .$negativeInt = '-'.$freeItemsCount;
                if($freeItemsCount > 0){
                    array_multisort(
                        array_map(static function ($element) {
                                return $element['price'];
                            },$of),SORT_DESC,$of);
                    $of = array_slice($of, '-'.$freeItemsCount);
                    // echo '<br>Discounted offer products *******************************************************************';
                    // print_r($of);
                    foreach($of as $cartOf){
                        $cartOffer = Cart::find($cartOf['cart_id']);
                        
                        $vatAmount = 0;
                        if($cartOffer->product){
                            $productVat = $cartOffer->product->vat;
                            if($productVat > 0){
                                $vatAmount = ($cartOffer['offer_price']/100)*$productVat;
                            }
                        }
                        $cartOffer->offer_discount += $cartOf['price'];
                        $cartOffer->tax -= ($cartOffer->tax > 0) ? $vatAmount : 0;
                        // echo '<br>Product Vat =  '.$cartOffer->product->vat;
                        // echo '<br>Product Vat Amount =  '.$vatAmount;
                        $cartOffer->save();
                    }
                }
            }
        
            $carts = $carts->fresh();
            $offerCartCount = $carts->whereNotNull('offer_id')->count();

            if($offerCartCount == 0){
                $coupon_code = $carts[0]->coupon_code;
                if ($coupon_code) {
                    $coupon = Coupon::whereCode($coupon_code)->first();
                    $can_use_coupon = false;
                    if ($coupon) {               
                        if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                            if($user_id != ''){
                                $coupon_used = CouponUsage::where('user_id', $user_id)->where('coupon_id', $coupon->id)->first();
                                if ($coupon->one_time_use && $coupon_used == null) {
                                    $can_use_coupon = true;
                                }
                            }
                        } else {
                            $can_use_coupon = false;
                        }
                    }
                    if ($can_use_coupon) {
                        $coupon_details = json_decode($coupon->details);
                        if ($coupon->type == 'cart_base') {
                            $subtotal = 0;
                            $tax = 0;
                            $shipping = 0;
                            foreach ($carts as $key => $cartItem) {
                                $subtotal += $cartItem['offer_price'] * $cartItem['quantity'];
                                $tax += $cartItem['tax'];
                                $shipping += $cartItem['shipping'] * $cartItem['quantity'];
                            }
                            $sum = $subtotal + $tax + $shipping;
    
                            if ($sum >= $coupon_details->min_buy) {
                                if ($coupon->discount_type == 'percent') {
                                    $coupon_discount = ($sum * $coupon->discount) / 100;
                                    if ($coupon_discount > $coupon_details->max_discount) {
                                        $coupon_discount = $coupon_details->max_discount;
                                    }
                                } elseif ($coupon->discount_type == 'amount') {
                                    $coupon_discount = $coupon->discount;
                                }
                                if($user_id != ''){
                                    Cart::where('user_id', $user_id)->update([
                                        'discount' => $coupon_discount / count($carts),
                                        'coupon_code' => $coupon_code,
                                        'coupon_applied' => 1
                                    ]);
                                } 
                            }
                        }elseif ($coupon->type == 'product_base') {
                            $coupon_discount = 0;
                            foreach ($carts as $key => $cartItem) {
                                foreach ($coupon_details as $key => $coupon_detail) {
                                    if ($coupon_detail->product_id == $cartItem['product_id']) {
                                        if ($coupon->discount_type == 'percent') {
                                            $coupon_discount += ($cartItem['offer_price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                        } elseif ($coupon->discount_type == 'amount') {
                                            $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                        }
                                    }
                                }
                            }
                            if($user_id != ''){
                                Cart::where('user_id', $user_id)->update([
                                    'discount' => $coupon_discount / count($carts),
                                    'coupon_code' => $coupon_code,
                                    'coupon_applied' => 1
                                ]);
                            }
                        }

                    }
                }
            }elseif($offerCartCount > 0 && $user_id != ''){
                Cart::where('user_id', $user_id)->update([
                    'discount' => 0.00,
                    'coupon_code' => "",
                    'coupon_applied' => 0
                ]);
                $coupon_code = '';
                $coupon_applied = 0;
                $total_coupon_discount = 0;
            }
            $carts = $carts->fresh();
            $newOfferCartCount = 0;

           
            foreach($carts as $datas){

                $overall_subtotal = $overall_subtotal + ($datas->price * $datas->quantity);

                $total_discount = $total_discount + (($datas->price * $datas->quantity) - ($datas->offer_price * $datas->quantity)) + $datas->offer_discount;
                $total_tax = $total_tax + $datas->tax;

                $result['products'][] = [
                    'id' => $datas->id,
                    'product' => [
                        'id' => $datas->product->id,
                        'name' => $datas->product->name,
                        'slug' => $datas->product->slug,
                        'sku' => $datas->product->sku,
                        'image' => get_product_image($datas->product->thumbnail_img,'300')
                    ],
                    'variation' => $datas->variation,
                    'stroked_price' => $datas->price ,
                    'main_price' => $datas->offer_price ,
                    'tax' => $datas->tax,
                    'offer_tag' => $datas->offer_tag,
                    'quantity' => (integer) $datas->quantity,
                    'date' => $datas->created_at->diffForHumans(),
                    'total' => $datas->offer_price * $datas->quantity
                ];
                $cart_coupon_code = $datas->coupon_code;
                $cart_coupon_applied = $datas->coupon_applied;
                if($datas->coupon_applied == 1){
                    $cart_coupon_discount += $datas->discount;
                }
                if($datas->offer_tag != ''){
                    $coupon_display++;
                }
            }

        }else{
            $result['products'] = [];
        }



        $cart_total = ($overall_subtotal + $total_tax) - ($total_discount + $cart_coupon_discount);

        $freeShippingStatus = get_setting('free_shipping_status');
        $freeShippingLimit = get_setting('free_shipping_min_amount');
        $defaultShippingCharge = get_setting('default_shipping_amount');
        $cartCount = count($carts);

        if($freeShippingStatus == 1){
            if($cart_total >= $freeShippingLimit){
                $total_shipping = 0;
                Cart::where('user_id', $user_id)->update([
                    'shipping_cost' => 0
                ]);
            }else{
                $total_shipping = $defaultShippingCharge;
                if($user_id != '' && $defaultShippingCharge > 0 && $cartCount != 0){
                    Cart::where('user_id', $user_id)->update([
                        'shipping_cost' => $defaultShippingCharge / $cartCount
                    ]);
                }
            }
        }else{
            $total_shipping = $defaultShippingCharge;
            if($user_id != '' && $defaultShippingCharge > 0 && $cartCount != 0){
                Cart::where('user_id', $user_id)->update([
                    'shipping_cost' => $defaultShippingCharge / $cartCount
                ]);
            }
        }

        $cart_total = ($overall_subtotal + $total_shipping + $total_tax) - ($total_discount + $cart_coupon_discount);

        $result['summary'] = [
            'sub_total' => $overall_subtotal,
            'discount' => $total_discount, // Discount is in percentage
            'shipping' => $total_shipping,
            'vat_percentage' => 0,
            'vat_amount' => $total_tax,
            'total' => $cart_total,
            'coupon_display' => ($coupon_display === 0) ? 1 : 0,
            'coupon_code' => $cart_coupon_code,
            'coupon_applied' => $cart_coupon_applied,
            'coupon_discount' => $cart_coupon_discount
        ];
        // echo '<pre>';
        // print_r($carts);
        // die;

        // return new CartCollection($carts);
        return response()->json(['status' => true,"message"=>"Success","data" => $result],200);
    }

    public function store(Request $request)
    {
        $product_slug = $request->has('product_slug') ? $request->product_slug : '';
        $product_slug = explode(',', $product_slug);
        $product_id = getProductIdsFromMultipleSlug($product_slug);
        $products = Product::findOrFail($product_id);

        $str = null;

        $user = getUser();
     
        $outStock = $added = 0;
        if($user['users_id'] != ''){
            if ($products) {
                foreach($products as $product){
                    $product->load('stocks');
                    if ($product->variant_product) {
    
                        $variations =  $request->variations;
    
                        foreach (json_decode($product->choice_options) as $key => $choice) {
                            if ($str != null) {
                                $str .= '-' . str_replace(' ', '', $variations['attribute_id_' . $choice->attribute_id]);
                            } else {
                                $str .= str_replace(' ', '', $variations['attribute_id_' . $choice->attribute_id]);
                            }
                        }
    
                        $product_stock = $product->stocks->where('variant', $str)->first();
    
                        if (($product_stock->qty < $request['quantity']) || ($product->hide_price)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'This item is out of stock!',
                                'cart_count' => $this->cartCount()
                            ], 200);
                        }
                    } else {
                        $product_stock = $product->stocks->first();
                        if (($product_stock->qty < $request['quantity']) || ($product->hide_price)) {
                            $outStock++;
                        }
                    }
    
                    $carts = Cart::where([
                        $user['users_id_type'] => $user['users_id'],
                        'product_id' => $product->id,
                        'variation' => $str,
                    ])->first();
    
                    $tax = 0;
                    if ($carts) {
                        if($product->vat != 0){
                            $new_quantity = $carts->quantity + $request->quantity;
                            $tax = (($carts->offer_price * $new_quantity)/100) * $product->vat;
                        }
                        $carts->quantity += $request->quantity;
                        $carts->tax  = $tax;
                        $carts->save();
                        $added++;
                    } else {
                        $price = $product_stock->price;
                        
    
                        $offerData = getProductOfferPrice($product);
                        if($product->vat != 0){
                            $tax = (($offerData['discounted_price'] * ($request['quantity'] ?? 1))/100) * $product->vat;
                        }
                        $data[$user['users_id_type']] =  $user['users_id'];
                        $data['product_id'] = $product->id;
                        $data['quantity'] = $request['quantity'] ?? 1;
                        $data['price'] = $offerData['original_price'];
                        $data['offer_price'] = $offerData['discounted_price'];
                        $data['offer_id'] = ($offerData['offer_id'] >= 0) ? $offerData['offer_id'] : NULL;
                        $data['variation'] = $str;
                        $data['tax'] = $tax;
                        $data['shipping_cost'] = 0;
                        $data['product_referral_code'] = null;
                        $data['cash_on_delivery'] = $product->cash_on_delivery;
                        $data['digital'] = $product->digital;
                        // print_r($data);
                        // die;
                        $added++;
    
                        Cart::create($data);
                    }
                }

                if($outStock == 0 && $added != 0){
                    $rtn_msg = "Cart updated successfully";
                }elseif($outStock == 0 && $added == 0){
                    $rtn_msg = "Items not added to cart";
                }elseif($outStock != 0 && $added == 0){
                    $rtn_msg = "Items are out of stock";
                }elseif($outStock != 0 && $added != 0){
                    $rtn_msg = "Cart updated successfully. Some of the items are out of stock";
                }

                return response()->json([
                    'success' => true,
                    'message' => $rtn_msg,
                    'cart_count' =>  $this->cartCount()
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => "Failed to add item to the cart",
                    'cart_count' => $this->cartCount()
                ], 200);
            }
        }
       
        return response()->json([
            'success' => false,
            'message' => "Failed to add item to the cart",
            'cart_count' => $this->cartCount()
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = getUser();
        $cart = Cart::where([
            $user['users_id_type'] => $user['users_id']
        ])->findOrFail($id);

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => "Cart removed",
            'cart_count' => $this->cartCount(),
        ]);
    }

    public function changeQuantity(Request $request)
    {
        $cart_id = $request->cart_id ?? '';
        $quantity = $request->quantity ?? '';
        $action = $request->action ?? '';
        $user = getUser();

        if($cart_id != '' && $quantity != '' && $action != '' && $user['users_id'] != ''){
            $cart = Cart::where([
                $user['users_id_type'] => $user['users_id']
            ])->with([
                'product',
                'product.stocks',
            ])->findOrFail($request->cart_id);
    
            $min_qty = $cart->product->min_qty;
            $max_qty = $cart->product->stocks->first()->qty;

            $product_vat = $cart->product->vat;
            $tax = 0;
            if($product_vat != 0){
                $tax = (($cart->offer_price * $quantity)/100) * $product_vat;
            }
            $cart->tax = $tax;

            if ($action == 'plus') {
                // Increase quantity of a product in the cart.
                if ( $quantity <= $max_qty) {
                    $cart->quantity = $quantity;
                    $cart->save();
                    return response()->json([
                        'status' => true,
                        'message' => "Cart updated",
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => "Maximum quantity reached",
                    ], 200);
                }
            }elseif($action == 'minus'){
                // Decrease quantity of a product in the cart. If it reaches zero then delete that row from the table.

                if($quantity < 1){
                    Cart::where('id',$cart->id)->delete();
                }else{
                    // Decrease quantity of a product in the cart.
                    $cart->quantity = $quantity;
                    $cart->save();
                }

                return response()->json([
                    'status' => true,
                    'message' => "Cart updated",
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Undefined action value",
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Missing data"
            ], 200);
        }
    }

    public function getCount(Request $request)
    {
        return response()->json([
            'success' => true,
            'cart_count' => $this->cartCount(),
        ]);
    }

    public function cartCount()
    {
        $user = getUser();

        return Cart::where([
            $user['users_id_type'] => $user['users_id']
        ])->count();
    }

    public function removeCartItem(Request $request){
        $cart_ids = $request->cart_ids ? explode(',', $request->cart_ids) : [];
        $user = getUser();

        if(!empty($cart_ids) && $user['users_id'] != ''){
            Cart::where([
                $user['users_id_type'] => $user['users_id']
            ])->whereIn('id',$cart_ids)->delete();

            return response()->json([
                'status' => true,
                'message' => "Cart items removed successfully"
            ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => "Cart item not found"
            ], 200);
        }
    }
}
