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
            
            $offerCartCount = $carts->whereNotNull('offer_id')->count();
            if(!empty($carts[0])){
                $carts->load(['product', 'product.stocks']);
            }
            
        } else {
            $temp_user_id = $request->header('UserToken');
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->orderBy('id','asc')->get() : [];
            if(!empty($carts)){
                $offerCartCount = $carts->whereNotNull('offer_id')->count();
            }else{
                $offerCartCount= 0;
            }
           
            if(!empty($carts[0])){
                $carts->load(['product', 'product.stocks']);
            }
        }
       
        // $buyXgetYOfferProducts = getActiveBuyXgetYOfferProducts();

        $result = [];
        $sub_total = $discount = $shipping = $coupon_display = $coupon_discount = $offerIdCount = $total_coupon_discount = 0;
        $coupon_code = $coupon_applied = null;
        
        if(!empty($carts[0])){
            
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
                                $tax += $cartItem['tax'] * $cartItem['quantity'];
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
            }
            $carts = $carts->fresh();
            $newOfferCartCount = 0;
            foreach($carts as $data){
                $priceData = getProductOfferPrice($data->product);
                
                $updateCart = Cart::find($data->id);
                $updateCart->price = $priceData['original_price'];
                $updateCart->offer_price = $priceData['discounted_price'];
                $updateCart->offer_id = ($priceData['offer_id'] >= 0) ? $priceData['offer_id'] : NULL;
                $updateCart->save();

                if($priceData['offer_tag'] != ''){
                    $coupon_display++;
                }

                if($priceData['offer_id'] >= 0){
                    $offerIdCount++;
                }

                $sub_total = $sub_total + ($priceData['original_price'] * $data->quantity);

                $discount = $discount + (($priceData['original_price'] * $data->quantity) - ($priceData['discounted_price'] * $data->quantity));

                $result['products'][] = [
                    'id' => $data->id,
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'slug' => $data->product->slug,
                        'sku' => $data->product->sku,
                        'image' => app('url')->asset($data->product->thumbnail_img)
                    ],
                    'variation' => $data->variation,
                    'stroked_price' => $priceData['original_price'],
                    'main_price' => $priceData['discounted_price'],
                    'offer_tag' => $priceData['offer_tag'],
                    'quantity' => (integer) $data->quantity,
                    'date' => $data->created_at->diffForHumans(),
                    'total' => $data->offer_price * $data->quantity
                ];
                $coupon_code = $data->coupon_code;
                $coupon_applied = $data->coupon_applied;
                if($data->coupon_applied == 1){
                    $total_coupon_discount += $data->discount;
                }
            }

            if($offerIdCount > 0 && $user_id != ''){
                Cart::where('user_id', $user_id)->update([
                    'discount' => 0.00,
                    'coupon_code' => "",
                    'coupon_applied' => 0
                ]);
                $coupon_code = '';
                $coupon_applied = 0;
                $total_coupon_discount = 0;
            }
        }else{
            $result['products'] = [];
        }

        $result['summary'] = [
            'sub_total' => $sub_total,
            'discount' => $discount, // Discount is in percentage
            'shipping' => $shipping,
            'vat_percentage' => 0,
            'vat_amount' => 0,
            'total' => $sub_total - ($discount+$total_coupon_discount),
            'coupon_display' => ($coupon_display === 0) ? 1 : 0,
            'coupon_code' => $coupon_code,
            'coupon_applied' => $coupon_applied,
            'coupon_discount' => $total_coupon_discount
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
        $product_id = getProductIdFromSlug($product_slug);
        $product = Product::findOrFail($product_id);

        $str = null;

        $user = getUser();
     
        if($user['users_id'] != ''){
            if ($product) {
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
                        return response()->json([
                            'success' => false,
                            'message' => 'This item is out of stock!',
                            'cart_count' => $this->cartCount()
                        ], 200);
                    }
                }

                $carts = Cart::where([
                    $user['users_id_type'] => $user['users_id'],
                    'product_id' => $product->id,
                    'variation' => $str,
                ])->first();

                if ($carts) {
                    $carts->quantity += $request->quantity;
                    $carts->save();
                    $rtn_msg = 'Cart updated successfully';
                } else {
                    $price = $product_stock->price;

                    $offerData = getProductOfferPrice($product);
                    
                    $data[$user['users_id_type']] =  $user['users_id'];
                    $data['product_id'] = $product->id;
                    $data['quantity'] = $request['quantity'] ?? 1;
                    $data['price'] = $offerData['original_price'];
                    $data['offer_price'] = $offerData['discounted_price'];
                    $data['offer_id'] = ($offerData['offer_id'] >= 0) ? $offerData['offer_id'] : NULL;
                    $data['variation'] = $str;
                    $data['tax'] = 0;
                    $data['shipping_cost'] = 0;
                    $data['product_referral_code'] = null;
                    $data['cash_on_delivery'] = $product->cash_on_delivery;
                    $data['digital'] = $product->digital;
                    // print_r($data);
                    // die;
                    $rtn_msg = 'Item added to cart';

                    Cart::create($data);
                }

                return response()->json([
                    'success' => true,
                    'message' => $rtn_msg,
                    'cart_count' =>  $this->cartCount()
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
