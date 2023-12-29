<?php


namespace App\Http\Controllers\Api\V2;


use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Address;
use App\Models\Cart;
use App\Models\User;
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

        $shipping_address = [];
        $billing_address = [];

        $user = getUser();
        $user_id = $user['users_id'];
        // print_r($user);
        if($user_id != ''){
            $address = Address::where('id', $address_id)->first();

            $shipping_address['name']        = $address->name;
            $shipping_address['email']       = auth('sanctum')->user()->email;
            $shipping_address['address']     = $address->address;
            $shipping_address['country']     = $address->country_name;
            $shipping_address['state']       = $address->state_name;
            $shipping_address['city']        = $address->city;
            $shipping_address['phone']       = $address->phone;
            $shipping_address['longitude']   = $address->longitude;
            $shipping_address['latitude']    = $address->latitude;
        }

        if ($billing_shipping_same == 0) {
            $billing_address['name']        = $request->name;
            $billing_address['email']       = $request->email;
            $billing_address['address']     = $request->address;
            $billing_address['country']     = $request->country;
            $billing_address['state']       = $request->state;
            $billing_address['city']        = $request->city;
            $billing_address['phone']       = $request->phone;
        } else {
            $billing_address = $shipping_address;
        }

        $shipping_address_json = json_encode($shipping_address);
        $billing_address_json = json_encode($billing_address);

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

                return response()->json([
                    'status' => true,
                    'message' => 'Your order has been placed successfully',
                    'data' => array(
                        'payment_type' => 'cash_on_delivery',
                        'url' => ''
                    )
                    ], 200);
            }else{
                $cardAmount = $amount = $grand_total;
                if($request->wallet == 1){
                    $userData = User::find($user_id);
                    $userWallet = $userData->wallet;
                    if($userWallet >= $amount){
                        $amountBal = $userWallet - $amount;
                        $cardAmount = 0;
                        $userData->wallet = $amountBal;
                        $order->wallet_deduction = $amount;
                    }else{
                        $amountBal = $amount - $userWallet;
                        $cardAmount = $amountBal;
                        $userData->wallet = 0;
                        $order->wallet_deduction = $userWallet;
                    }

                    $userData->save();
                    $order->payment_type = 'card_wallet';
                    $order->save();
                }
                
                if($cardAmount != 0){
                    $payment['amount'] = $grand_total;
                    $payment['order_id'] = $order->code;
                    $payment['currency'] = "AED";
                    $payment['redirect_url'] = route('payment-success');
                    $payment['cancel_url'] = route('payment-cancel');
                    $payment['language'] = "EN";
                    $payment['merchant_id'] = env('CCA_MERCHANT_ID');
    
                    $working_key = env('CCA_WORKING_KEY'); // config('cc-avenue.working_key'); //Shared by CCAVENUES
                    $access_code = env('CCA_ACCESS_CODE'); // config('cc-avenue.access_code'); //Shared by CCAVENUES
    
                    $payment['billing_name'] = $billing_address['name'];
                    $payment['billing_address'] = $billing_address['address'];
                    $payment['billing_city'] = $billing_address['city'];
                    $payment['billing_state'] = $billing_address['state'];
                    $payment['billing_country'] = $billing_address['country'];
                    $payment['billing_tel'] = $billing_address['phone'];
                    $payment['billing_email'] = $billing_address['email'];

                    $merchant_data = "";

                    foreach ($payment as $key => $value) {
                        $merchant_data .= $key . '=' . $value . '&';
                    }
                
                    $encrypted_data = encryptCC($merchant_data, $working_key);
                    $url = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction&encRequest=' . $encrypted_data . '&access_code=' . $access_code;
    
                    return response()->json([
                        'status' => true,
                        'message' => 'Payment gateway',
                        'data' => array(
                            'payment_type' => 'card',
                            'url' => $url
                        )
                    ], 200);
                }else{
                    $order->payment_status = 'paid';
                    $order->save();
                    return response()->json([
                        'status' => true,
                        'message' => 'Your order has been placed successfully',
                        'data' => array(
                            'payment_type' => 'wallet',
                            'url' =>''
                        )
                    ], 200);
                }
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Cart Empty',
                'data' => array(
                    'payment_type' => '',
                    'url' => ''
                )
            ], 200);
        }
    }

    public function successPayment(Request $request){
        print_r($request->all());
    }

    public function cancelPayment(Request $request){
        print_r($request->all());
    }
}
