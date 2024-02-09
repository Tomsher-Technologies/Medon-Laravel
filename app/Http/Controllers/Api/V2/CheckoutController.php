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
use App\Models\OrderPayments;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\OrderTracking;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use App\Utility\NotificationUtility;
use App\Utility\SendSMSUtility;
use App\Mail\EmailManager;

use Mail;
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

            if($coupon->one_time_use == 1){
                $is_used = CouponUsage::where('user_id', $user['users_id'])->where('coupon_id', $coupon->id)->first() != null;

                if ($is_used) {
                    return response()->json([
                        'status' => false,
                        'message' => translate('You already used this coupon!')
                    ], 200);
                }
            }
            
            $coupon_details = json_decode($coupon->details);

            if ($coupon->type == 'cart_base') {
                $subtotal = 0;
                $tax = 0;
                $shipping = 0;
                foreach ($cart_items as $key => $cartItem) {
                    $subtotal += $cartItem['offer_price'] * $cartItem['quantity'];
                    $tax += $cartItem['tax'] ;
                    $shipping += $cartItem['shipping_cost'] ;
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
        
        $platform = $request->has('platform') ? $request->platform : '';
        
        $address_id = $request->address_id ?? null;
        $billing_shipping_same = $request->billing_shipping_same ?? null;

        $shipping_address = [];
        $billing_address = [];

        $user = getUser();
        $user_id = $user['users_id'];
        // print_r($user);
        if($user_id != ''){
            $address = Address::where('id', $address_id)->first();
            if($address){
                $shipping_address['name']        = $address->name;
                $shipping_address['email']       = auth('sanctum')->user()->email;
                $shipping_address['address']     = $address->address;
                $shipping_address['country']     = $address->country_name;
                $shipping_address['state']       = $address->state_name;
                $shipping_address['city']        = $address->city;
                $shipping_address['phone']       = $address->phone;
                $shipping_address['longitude']   = $address->longitude;
                $shipping_address['latitude']    = $address->latitude;
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Shipping address not exist',
                    'data' => array(
                        'order_id' => '',
                        'order_code' => '',
                        'grand_total' => 0,
                        'payment_type' => '',
                        'url' => '',
                        'tracking_id' => '',
                        'request_hash' => ''
                    )
                ], 200);
            }
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
            $sub_total = $discount = $coupon_applied = $total_coupon_discount = $grand_total = $total_shipping = $total_tax = 0;
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
                'tax' => 0,
                'sub_total' => 0,
                'offer_discount' => 0,
                'coupon_discount' => 0,
                'code' => date('Ymd-His') . rand(10, 99),
                'date' => strtotime('now'),
                'delivery_viewed' => 0
            ]);

            $track = new OrderTracking;
            $track->order_id = $order->id;
            $track->status = 'pending';
            $track->description = "The order has been placed successfully";
            $track->status_date = date('Y-m-d H:i:s');
            $track->save();

            $orderItems = $productQuantities = [];

            foreach($carts as $data){
                $sub_total = $sub_total + ($data->price * $data->quantity);
                $total_tax = $total_tax + $data->tax;
                $total_shipping = $total_shipping + $data->shipping_cost;
                $discount = $discount + (($data->price * $data->quantity) - ($data->offer_price * $data->quantity)) + $data->offer_discount;
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
                    'tax' => $data->tax,
                    'shipping_cost' => $data->shipping_cost,
                    'offer_price' => $data->offer_price,
                    'price' => $data->offer_price * $data->quantity,
                    'quantity' => $data->quantity,
                ];
                $productQuantities[$data->product_id] = $data->quantity;

                $product = Product::find($data->product_id);
                $product->num_of_sale += $data->quantity;
                $product->save();
            }
            OrderDetail::insert($orderItems);
            $grand_total = ($sub_total + $total_tax + round($total_shipping))  - ($discount + $total_coupon_discount);

            $combined_order->grand_total = $grand_total;
            $combined_order->save();

            $order->grand_total         = $grand_total;
            $order->sub_total           = $sub_total;
            $order->offer_discount      = $discount;
            $order->tax                 = $total_tax;
            $order->shipping_cost       = round($total_shipping);
            $order->shipping_type       = ($total_shipping == 0) ? 'free_shipping' : 'flat_rate';
            $order->coupon_discount     = $total_coupon_discount;
            $order->coupon_code         = $coupon_code;
            $order->save();

            if($coupon_code != ''){
                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = $user_id;
                $coupon_usage->coupon_id = Coupon::where('code', $coupon_code)->first()->id;
                $coupon_usage->save();
            }
            if($request->payment_method == 'cash_on_delivery'){
                reduceProductQuantity($productQuantities);
                Cart::where('user_id', $user_id)->delete();

                NotificationUtility::sendOrderPlacedNotification($order);
                NotificationUtility::sendNotification($order, 'created');
                $message = getOrderStatusMessageTest($order->user->name, $order->code);
                $userPhone = $order->user->phone ?? '';
                if($userPhone != '' && $message['order_placed'] != ''){
                    SendSMSUtility::sendSMS($userPhone, $message['order_placed']);
                }
            
                return response()->json([
                    'status' => true,
                    'message' => 'Your order has been placed successfully',
                    'data' => array(
                        'order_id' => $order->id,
                        'order_code' => $order->code,
                        'grand_total' => $grand_total,
                        'payment_type' => 'cash_on_delivery',
                        'url' => '',
                        'tracking_id' => '',
                        'request_hash' => ''
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
                    $working_key = env('CCA_WORKING_KEY'); // config('cc-avenue.working_key'); //Shared by CCAVENUES
                    $access_code = env('CCA_ACCESS_CODE'); // config('cc-avenue.access_code'); //Shared by CCAVENUES

                    $tracking_id = $requestHash = $url = ''; 
                    if($platform == 'mob'){
                        $payment['amount'] = $cardAmount;
                        $payment['order_id'] = $order->code;
                        $payment['currency'] = "AED";
                        $payment['tid'] = "";
                        $payment['merchant_id'] = env('CCA_MERCHANT_ID');
                        $merchant_data = json_encode($payment);
                        
                        $encrypted_data = encryptCC($merchant_data, $working_key);
                        
                        $post_field = [
                            'encRequest' => $encrypted_data,
                            'access_code' => $access_code
                        ];
                      
                        $response = Http::timeout(300)->withHeaders(array('Content-Type:application/json'))->post(env('CCA_URL_MOB').'?command=generateTrackingId', $post_field);
                        $result = $response->getBody()->getContents();
                
                        $resultData = json_decode($result);

                        $encResp = '';
                        if(isset($resultData->status)){
                            if($resultData->status == 'success'){
                                $encResp = $resultData->data->encResp;
                            }
                        }
                        $responseTracking = [];
                        $paymentAmount = '';
                        if($encResp != ''){
                            $responseTracking = decryptCC($encResp,$working_key);
                            $responseTracking = json_decode($responseTracking);
                            $tracking_id = $responseTracking->tracking_id;
                            $paymentAmount = $responseTracking->amount;
                        }
                       
                        if($tracking_id != ''){
                           $requestHash = hash("sha512", $tracking_id.'AED'.$paymentAmount.$working_key);
                        }
                    }else{
                        $payment['amount'] = $cardAmount;
                        $payment['order_id'] = $order->code;
                        $payment['currency'] = "AED";
                        $payment['redirect_url'] = route('payment-success');
                        $payment['cancel_url'] = route('payment-cancel');
                        $payment['language'] = "EN";
                        $payment['merchant_id'] = env('CCA_MERCHANT_ID');
        
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
                        $url = env('CCA_URL').'?command=initiateTransaction&encRequest=' . $encrypted_data . '&access_code=' . $access_code;
                    }
                    
                    return response()->json([
                        'status' => true,
                        'message' => 'Payment gateway',
                        'data' => array(
                            'order_id' => $order->id,
                            'order_code' => $order->code,
                            'grand_total' => $cardAmount,
                            'payment_type' => 'card',
                            'url' => $url,
                            'tracking_id' => $tracking_id,
                            'request_hash' => $requestHash
                        )
                    ], 200);
                }else{
                    $order->payment_status = 'paid';
                    $order->save();

                    $orderDetails = OrderDetail::where('order_id', $order->id)->update(['payment_status'=>'paid']);

                    reduceProductQuantity($productQuantities);
                    Cart::where('user_id', $user_id)->delete();
                    NotificationUtility::sendOrderPlacedNotification($order);
                    NotificationUtility::sendNotification($order, 'created');
                    $message = getOrderStatusMessageTest($order->user->name, $order->code);
                    $userPhone = $order->user->phone ?? '';
                    if($userPhone != '' && $message['order_placed'] != ''){
                        SendSMSUtility::sendSMS($userPhone, $message['order_placed']);
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Your order has been placed successfully',
                        'data' => array(
                            'order_id' => $order->id,
                            'order_code' => $order->code,
                            'grand_total' => $cardAmount,
                            'payment_type' => 'wallet',
                            'url' =>'',
                            'tracking_id' => '',
                            'request_hash' => ''
                        )
                    ], 200);
                }
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Cart Empty',
                'data' => array(
                    'order_id' => '',
                    'order_code' => '',
                    'grand_total' => 0,
                    'payment_type' => '',
                    'url' => '',
                    'tracking_id' => '',
                    'request_hash' => ''
                )
            ], 200);
        }
    }

    public function successPayment(Request $request){
        $encResponse = $request->encResp;          //This is the response sent by the CCAvenue Server
        $rcvdString = decryptCC($encResponse,env('CCA_WORKING_KEY')); //Crypto Decryption used as per the specified working key.
        $order_status = $order_code = $tracking_id = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        $details = [];
        for($i = 0; $i < $dataSize; $i++) {
            $information=explode('=',$decryptValues[$i]);
            $details[$information[0]] = $information[1];
            if($i==0)  $order_code=$information[1];
            if($i==1)  $tracking_id=$information[1];
            if($i==3)  $order_status=$information[1];
        }
     
        $payment_details = json_encode($details);

        if($order_code != ''){
            $order = Order::where('code','=',$order_code)->firstOrFail();
            if(strtolower($order_status) === "success"){
                $order->payment_status = 'paid';
                $order->payment_tracking_id = $tracking_id;
                $order->payment_details = $payment_details;
                $order->save();
                Cart::where('user_id', $order->user_id)->delete();

                NotificationUtility::sendOrderPlacedNotification($order);
                NotificationUtility::sendNotification($order, 'created');
                $message = getOrderStatusMessageTest($order->user->name, $order->code);
                $userPhone = $order->user->phone ?? '';
                if($userPhone != '' && $message['order_placed'] != ''){
                    SendSMSUtility::sendSMS($userPhone, $message['order_placed']);
                }
    
                $orderDetails = OrderDetail::where('order_id', $order->id)->get();

                if(!empty($orderDetails[0])){
                    foreach($orderDetails as $od){
                        $od->payment_status = 'paid';
                        $od->save();

                        $product_stock = ProductStock::where('product_id', $od->product_id)->first();
                        $product_stock->qty -= ($od->quantity >= $product_stock->qty) ? 0: ($product_stock->qty - $od->quantity);
                        $product_stock->save();
                    }
                }
    
                $orderPayments = new OrderPayments();
                $orderPayments->order_id = $order->id;
                $orderPayments->payment_status = $order_status;
                $orderPayments->payment_details = $payment_details;
                $orderPayments->save();
            }else{
                $orderDetails = Order::where('code','=',$order_code)->delete();
            }    
        }

        // if($order_status === "Success"){
        //     $resMessage = "Thank you for shopping with us. Your card has been charged and your transaction is successful. We will be shipping your order to you soon.";
        // }else if($order_status === "Aborted"){
        //     $resMessage = "Thank you for shopping with us. We will keep you posted regarding the status of your order through e-mail";
        // }else if($order_status === "Failure"){
        //     $resMessage = "Thank you for shopping with us. However,the transaction has been declined.";
        // }else{
        //     $resMessage = "Security Error. Illegal access detected";
        // }
        return redirect(env('MEDON_PAYMENT_SUCCESS').'?status='.$order_status.'&code='.$order_code);
    }

    public function cancelPayment(Request $request){
        $encResponse = $request->encResp;          //This is the response sent by the CCAvenue Server
        $rcvdString = decryptCC($encResponse,env('CCA_WORKING_KEY')); //Crypto Decryption used as per the specified working key.
        $order_status = $order_code = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        $details = [];
        for($i = 0; $i < $dataSize; $i++) {
            $information=explode('=',$decryptValues[$i]);
            $details[$information[0]] = $information[1];
            if($i==0)  $order_code=$information[1];
            if($i==3)  $order_status=$information[1];
        }
     
        $payment_details = json_encode($details);
        
        if($order_code != ''){
            $orderDetails = Order::where('code','=',$order_code)->delete();

            // $orderPayments = new OrderPayments();
            // $orderPayments->order_id = $orderDetails->id;
            // $orderPayments->payment_status = $order_status;
            // $orderPayments->payment_details = $payment_details;
            // $orderPayments->save();
        }
        return redirect(env('MEDON_PAYMENT_CANCEL').'?status='.$order_status.'&code='.$order_code);
    }

    public function successAppPayment(Request $request){

        $encResponse = $request->encResp;          //This is the response sent by the CCAvenue Server
        $rcvdString = decryptCC($encResponse,env('CCA_WORKING_KEY')); //Crypto Decryption used as per the specified working key.
        $order_status = $order_code = $tracking_id = "";
        $decryptValues = explode('&', $rcvdString);

        echo '<pre>';
        print_r($decryptValues);

        $orderPayments = new OrderPayments();
        $orderPayments->order_id = 375;
        $orderPayments->payment_status = 'test';
        $orderPayments->payment_details = json_encode($decryptValues);
        $orderPayments->save();

        die;
        // $data = $request->all();
        // $order_status = $data['order_status'];
        // $order_code = $data['order_id'];
        // $tracking_id = $data['tracking_id'];

        // $payment_details = json_encode($data);

        // if($order_code != ''){
        //     $order = Order::where('code','=',$order_code)->firstOrFail();
        //     if(strtolower($order_status) === "successful"){
        //         $order->payment_status = 'paid';
        //         $order->payment_tracking_id = $tracking_id;
        //         $order->payment_details = $payment_details;
        //         $order->save();
        //         Cart::where('user_id', $order->user_id)->delete();

        //         NotificationUtility::sendOrderPlacedNotification($order);
        //         NotificationUtility::sendNotification($order, 'created');
        //         $message = getOrderStatusMessageTest($order->user->name, $order->code);
        //         $userPhone = $order->user->phone ?? '';
        //         if($userPhone != '' && $message['order_placed'] != ''){
        //             SendSMSUtility::sendSMS($userPhone, $message['order_placed']);
        //         }
    
        //         $orderDetails = OrderDetail::where('order_id', $order->id)->get();

        //         if(!empty($orderDetails[0])){
        //             foreach($orderDetails as $od){
        //                 $od->payment_status = 'paid';
        //                 $od->save();

        //                 $product_stock = ProductStock::where('product_id', $od->product_id)->first();
        //                 $product_stock->qty -= ($od->quantity >= $product_stock->qty) ? 0: ($product_stock->qty - $od->quantity);
        //                 $product_stock->save();
        //             }
        //         }
    
        //         $orderPayments = new OrderPayments();
        //         $orderPayments->order_id = $order->id;
        //         $orderPayments->payment_status = $order_status;
        //         $orderPayments->payment_details = $payment_details;
        //         $orderPayments->save();

        //         $details['id'] = $order->id ?? '';
        //         $details['code'] = $order->code ?? '';
        //         $details['shipping_address'] = json_decode($order->shipping_address ?? '');
        //         $details['billing_address'] = json_decode($order->billing_address ?? '');
        //         $details['order_notes'] = $order->order_notes ?? '';
        //         $details['shipping_cost'] = $order->shipping_cost ?? '';
        //         $details['delivery_status'] = $order->delivery_status ?? '';
        //         $details['payment_type'] = $order->payment_type ?? '';
        //         $details['payment_status'] = $order->payment_status ?? '';
        //         $details['tax'] = $order->tax ?? '';
        //         $details['coupon_code'] = $order->coupon_code ?? '';
        //         $details['sub_total'] = $order->sub_total ?? '';
        //         $details['coupon_discount'] = $order->coupon_discount ?? '';
        //         $details['offer_discount'] = $order->offer_discount ?? '';
        //         $details['grand_total'] = $order->grand_total ?? '';
        //         $details['wallet_deduction'] = $order->wallet_deduction ?? '';
        //         $details['card_deduction'] = $order->grand_total - $order->wallet_deduction;
        //         $details['date'] = date('d-m-Y h:i A', $order->date);

        //         return response()->json([
        //             'status' => true,
        //             'message' => 'Order placed successfully',
        //             'data' => $details
        //         ], 200);

        //     }else{
        //         $orderDetails = Order::where('code','=',$order_code)->delete();
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Payment failed'
        //         ], 200);
        //     }    
        // }else{
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Order code not found'
        //     ], 200);
        // }
    }
    public function cancelAppPayment(Request $request){
        $order_code = $request->order_code;
        if($order_code != ''){
            $orderDetails = Order::where('code','=',$order_code)->delete();
        }
        return response()->json([
            'status' => false,
            'message' => 'Payment cancelled'
        ], 200);
    }

    public function returnRequest(Request $request){
        $order_id = $request->order_id ?? '';
        $products = $request->products ?? '';
        $reason   = $request->reason ?? '';
        $user = getUser();
        $order_details = explode(',', $products);
        $default_return_time = get_setting('default_return_time') ?? 0;
        if($order_id != ''){
            if(!empty($order_details)){
                $details = OrderDetail::with(['product'])->whereIn('id', $order_details)->where('order_id',$order_id)->get();
                
                $data = [];
                if(!empty($details[0])){
                    foreach($details as $od){

                        $return_expiry = null;
                        if($od->delivery_date != null && $default_return_time != 0 ){
                            $return_expiry = getDatePlusXDays($od->delivery_date, $default_return_time);
                        }

                        if($od->product->return_refund == 1){
                            $checkReturn = RefundRequest::where('order_id',$order_id)->where('order_details_id', $od->id)->count();
                            if($checkReturn == 0 && ($return_expiry != null && $return_expiry > date('Y-m-d H:i:s'))){
                                $data[] = array(
                                    'order_id' => $order_id,
                                    'order_details_id' => $od->id,
                                    'product_id' => $od->product_id,
                                    'user_id' => $user['users_id'],
                                    'reason' => $reason,
                                    'offer_price' => $od->offer_price,
                                    'quantity' => $od->quantity,
                                    'refund_amount' => ($od->offer_price * $od->quantity) + $od->tax
                                );
                            }
                        }
                    }
                   
                    if(!empty($data)){
                        RefundRequest::insert($data);

                        $order = Order::find($order_id);
                        if($order){
                            $array['view'] = 'emails.commonmail';
                            $array['subject'] = "New Order Return Request - ".$order->code;
                            $array['from'] = env('MAIL_FROM_ADDRESS');
                            $array['content'] = "<p>Hi,</p>
                                            <p>We have received a new return request. Below are the details of the return:</p><br>
                                            <p><b>Order Code : </b>".$order->code."</p>
                                            <p><b>Customer Name : </b>".$order->user->name ."</p>
                                            <p><b>Reason for Return: </b>".$reason ."</p>
                                            <p><b>Return Request Date: </b>".date('d-M-Y H:i a')."</p><br>
                                            <p>Thank you for your cooperation.</p>
                                            <p>Best regards,</p>
                                            <p>Team ".env('APP_NAME')."</p>";
                            Mail::to(env('MAIL_ADMIN'))->queue(new EmailManager($array));
                        }
                        

                        return response()->json([
                            'status' => true,
                            'message' => 'Refund request sent successfully'
                        ], 200);
                    }else{
                        return response()->json([
                            'status' => false,
                            'message' => "You can not make a refund request for this product."
                        ], 200);
                    }
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Order details not found'
                    ], 200);
                }
               
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Order details not found'
                ], 200);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 200);
        }
    }

    public function cancelOrderRequest(Request $request){
        $order_id = $request->order_id ?? '';
        $reason   = $request->reason ?? '';
        $user = getUser();
        if($order_id != ''){
            $order = Order::find($order_id);
            if($order){
                if($order->cancel_request == 0){
                    $order->cancel_request = 1;
                    $order->cancel_request_date = date('Y-m-d H:i:s');
                    $order->cancel_refund_amount = $order->grand_total;
                    $order->cancel_reason = $reason;
                    $order->save();

                    $array['view'] = 'emails.commonmail';
                    $array['subject'] = "New Order Cancel Request - ".$order->code;
                    $array['from'] = env('MAIL_FROM_ADDRESS');
                    $array['content'] = "<p>Hi,</p>
                                    <p style='line-height: 25px;'>We have received a new order cancel request. Below are the details of the order:</p>
                                    <p><b>Order Code : </b>".$order->code."</p>
                                    <p><b>Customer Name : </b>".$order->user->name ."</p>
                                    <p style='line-height: 25px;'><b>Reason for cancel: </b>".$reason ."</p>
                                    <p><b>Cancel Request Date: </b>".date('d-M-Y H:i a')."</p><br>
                                    <p>Thank you for your cooperation.</p>
                                    <p>Best regards,</p>
                                    <p>Team ".env('APP_NAME')."</p>";
                    Mail::to(env('MAIL_ADMIN'))->queue(new EmailManager($array));
                    
                    return response()->json([
                        'status' => true,
                        'message' => 'Order cancel request send successfully'
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Order cancel request already send'
                    ], 200);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 200);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 200);
        }
    }

}
