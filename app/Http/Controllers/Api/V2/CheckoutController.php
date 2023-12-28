<?php


namespace App\Http\Controllers\Api\V2;


use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function apply_coupon_code(Request $request)
    {
        $user = getUser();
        // print_r($user);
        if($user['users_id'] != ''){
            $cart_items = Cart::where([$user['users_id_type'] => $user['users_id']])->get();
            $coupon = Coupon::where('code', $request->coupon_code)->first();
            if ($cart_items->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => translate('Cart is empty')
                ], 200);
            }
            
            $cartOffer = $cart_items->whereNotNull('offer_id')->count();
           
            if($cartOffer != 0){
                return response()->json([
                    'status' => false,
                    'message' => translate('Coupon code not applicable!')
                ], 200);
            }

            if ($coupon == null) {
                return response()->json([
                    'status' => false,
                    'message' => translate('Invalid coupon code!')
                ], 200);
            }

            $in_range = strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date;
            if (!$in_range) {
                return response()->json([
                    'status' => false,
                    'message' => translate('Coupon expired!')
                ], 200);
            }

            $is_used = CouponUsage::where('user_id', $user['users_id'])->where('coupon_id', $coupon->id)->first() != null;

            if ($is_used) {
                return response()->json([
                    'status' => false,
                    'message' => translate('You already used this coupon!')
                ], 200);
            }
            $coupon_details = json_decode($coupon->details);

            if ($coupon->type == 'cart_base') {
                $subtotal = 0;
                $tax = 0;
                $shipping = 0;
                foreach ($cart_items as $key => $cartItem) {
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
                
                    Cart::where('user_id', $user['users_id'])->update([
                        'discount' => $coupon_discount / count($cart_items),
                        'coupon_code' => $request->coupon_code,
                        'coupon_applied' => 1
                    ]);
    
                    return response()->json([
                        'status' => true,
                        'message' => translate('Coupon Applied')
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => translate('Sorry, this coupon cannot be applied to this order')
                    ], 200);
                }
            } elseif ($coupon->type == 'product_base') {
                $coupon_discount = 0;
                foreach ($cart_items as $key => $cartItem) {
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
    
                Cart::where('user_id', $user['users_id'])->update([
                    'discount' => $coupon_discount / count($cart_items),
                    'coupon_code' => $request->coupon_code,
                    'coupon_applied' => 1
                ]);
    
                return response()->json([
                    'status' => true,
                    'message' => translate('Coupon Applied')
                ], 200);
    
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 200);
        }
    }

    public function remove_coupon_code(Request $request)
    {
        $user = getUser();
        // print_r($user);
        if($user['users_id'] != ''){
            Cart::where('user_id', $user['users_id'])->update([
                'discount' => 0.00,
                'coupon_code' => "",
                'coupon_applied' => 0
            ]);
    
            return response()->json([
                'result' => true,
                'message' => translate('Coupon Removed')
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 200);
        }
    }

    public function placeOrder(Request $request){
        // print_r($request->all());
        $address_id = $request->address_id ?? null;
        $billing_shipping_same = $request->billing_shipping_same ?? null;

        $shipping_address_json = [];
        $billing_address_json = [];

        $user = getUser();
        $user_id = $user['users_id'];
        // print_r($user);
        if($user_id != ''){
            $address = Address::where('id', $address_id)->first();

            $shipping_address_json['name']        = $address->name;
            $shipping_address_json['email']       = auth('sanctum')->user()->email;
            $shipping_address_json['address']     = $address->address;
            $shipping_address_json['country']     = $address->country_name;
            $shipping_address_json['state']       = $address->state_name;
            $shipping_address_json['city']        = $address->city;
            $shipping_address_json['phone']       = $address->phone;
            $shipping_address_json['longitude']   = $address->longitude;
            $shipping_address_json['latitude']    = $address->latitude;
        }

        if ($billing_shipping_same == 0) {
            $billing_address_json['name']        = $request->name;
            $billing_address_json['email']       = $request->email;
            $billing_address_json['address']     = $request->address;
            $billing_address_json['country']     = $request->country;
            $billing_address_json['state']       = $request->state;
            $billing_address_json['city']        = $request->city;
            $billing_address_json['phone']       = $request->phone;
        } else {
            $billing_address_json = $shipping_address_json;
        }

        $shipping_address_json = json_encode($shipping_address_json);
        $billing_address_json = json_encode($billing_address_json);

        $carts = Cart::where('user_id', $user_id)->orderBy('id','asc')->get();
            
        if(!empty($carts[0])){
            $carts->load(['product', 'product.stocks']);

            $combined_order = CombinedOrder::create([
                'user_id' => $user_id,
                'shipping_address' => $shipping_address_json,
                'grand_total' => 0,
            ]);
            $sub_total = $discount = $coupon_applied = $total_coupon_discount = $grand_total = 0;
            $coupon_code = '';

            $order = Order::create([
                'user_id' => $user_id,
                'guest_id' => NULL,
                'seller_id' =>  0,
                'combined_order_id' => $combined_order->id,
                'shipping_address' => $shipping_address_json,
                'billing_address' => $billing_address_json,
                'order_notes' => $request->order_notes ?? '',
                'shipping_type' => 'free_shipping',
                'shipping_cost' => 0,
                'pickup_point_id' => 0,
                'delivery_status' => 'pending',
                'payment_type' => $request->payment_method ?? '',
                'payment_status' => 'un_paid',
                'grand_total' =>  0,
                'sub_total' => 0,
                'offer_discount' => 0,
                'coupon_discount' => 0,
                'code' => date('Ymd-His') . rand(10, 99),
                'date' => strtotime('now'),
                'delivery_viewed' => 0
            ]);

            $orderItems = [];

            foreach($carts as $data){
                $sub_total = $sub_total + ($data->price * $data->quantity);
                $discount = $discount + (($data->price * $data->quantity) - ($data->offer_price * $data->quantity));
                $coupon_code = $data->coupon_code;
                $coupon_applied = $data->coupon_applied;
                if($data->coupon_applied == 1){
                    $total_coupon_discount += $data->discount;
                }
                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $data->product_id,
                    'variation' => $data->variation,
                    'og_price' => $data->price,
                    'offer_price' => $data->offer_price,
                    'price' => $data->offer_price * $data->quantity,
                    'quantity' => $data->quantity,
                ];
            }
            OrderDetail::insert($orderItems);
            $grand_total = $sub_total - ($discount + $total_coupon_discount);

            $combined_order->grand_total = $grand_total;
            $combined_order->save();

            $order->grand_total         = $grand_total;
            $order->sub_total           = $sub_total;
            $order->offer_discount      = $discount;
            $order->coupon_discount     = $total_coupon_discount;
            $order->save();

            if($coupon_code != ''){
                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = $user_id;
                $coupon_usage->coupon_id = Coupon::where('code', $coupon_code)->first()->id;
                $coupon_usage->save();
            }
            if($request->payment_method == 'cash_on_delivery'){
                Cart::where('user_id', $user_id)->delete();

                return response()->json(['status' => true,'message' => 'Order placed successfully'], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Payment gateway'
                ], 200);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Cart Empty'
            ], 200);
        }
    }
}
