<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Address;
use App\Models\CombinedOrder;
use App\Models\Country;
use App\Models\OrderDetail;
use Session;
use App\Utility\NotificationUtility;
use Cache;

class CheckoutController extends Controller
{

    public function __construct()
    {
        //
    }

    //check the selected payment gateway and redirect to that controller accordingly
    public function checkout(Order $order)
    {

        $order_success = false;

        if (Auth::check()) {
            $user_col = "user_id";
            $user_id = Auth::id();
        } else {
            $user_col = "temp_user_id";
            $user_id = getTempUserId();
        }

        if ($order->payment_type == 'cash_on_delivery') {
            $order->delivery_status = 'confirmed';
            $order->save();
            OrderDetail::where('order_id',  $order->id)->update([
                'delivery_status' => 'confirmed'
            ]);

            Cart::where($user_col, $user_id)
                ->delete();

            Cache::forget('user_cart_count_' . $user_id);

            $order_success = true;
        }


        if ($order_success) {
            NotificationUtility::sendOrderPlacedNotification($order);

            return redirect()->route('order_confirmed', [
                'order' => $order
            ]);
        }

        return redirect()->route('order_failed', [
            'order' => $order
        ]);
    }


    
    public function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();

            calculateCommissionAffilationClubPoint($order);
        }

        Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('order_confirmed');
    }

    public function checkout_page(Request $request)
    {
        $user_col = "";
        $user_id = "";

        $addresses = null;
        $country  = null;

        if (Auth::check()) {
            $user_col = "user_id";
            $user_id = Auth::id();
        } else {
            $user_col = "temp_user_id";
            $user_id = getTempUserId();
        }

        $carts = Cart::where($user_col, $user_id)->with('product')->get();

        if ($carts->count()) {
            // Apply coupons
            $coupon_code = null;
            foreach ($carts as $cart) {
                if ($cart->coupon_applied) {
                    $coupon_code = $cart->coupon_code;
                }
            }

            if ($coupon_code) {
                $coupon = Coupon::whereCode($coupon_code)->first();
                if ($coupon) {
                    $can_use_coupon = false;
                    if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                        $coupon_used = CouponUsage::where($user_col, $user_id)->where('coupon_id', $coupon->id)->first();
                        if ($coupon->one_time_use && $coupon_used == null) {
                            $can_use_coupon = true;
                        }
                    } else {
                        $can_use_coupon = false;
                    }
                }

                if ($can_use_coupon) {
                    $coupon_details = json_decode($coupon->details);

                    if ($coupon->type == 'cart_base') {
                        $sum = 0;

                        foreach ($carts  as $key => $cartItem) {
                            $sum += $cartItem['price'] * $cartItem['quantity'];
                        }

                        if ($sum >= $coupon_details->min_buy) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                            }
                        }
                    } elseif ($coupon->type == 'product_base') {
                        $coupon_discount = 0;
                        foreach ($carts as $key => $cartItem) {
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Get Address
        if ($carts->count() && Auth::check()) {
            $addresses = Address::whereUserId($user_id)->orderBy('set_default', 'desc')->get();
        }
        $country = Country::whereStatus(1);

        return view('frontend.checkout', compact('carts', 'addresses', 'country'));
    }

    public function store_shipping_info(Request $request)
    {
        if ($request->address_id == null) {
            flash(translate("Please add shipping address"))->warning();
            return back();
        }

        $carts = Cart::where('user_id', Auth::user()->id)->get();

        foreach ($carts as $key => $cartItem) {
            $cartItem->address_id = $request->address_id;
            $cartItem->save();
        }

        return view('frontend.delivery_info', compact('carts'));
    }

    public function store_delivery_info(Request $request)
    {
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();

        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        if ($carts && count($carts) > 0) {
            foreach ($carts as $key => $cartItem) {
                $product = \App\Models\Product::find($cartItem['product_id']);
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $subtotal += $cartItem['price'] * $cartItem['quantity'];

                if ($request['shipping_type_' . $product->user_id] == 'pickup_point') {
                    $cartItem['shipping_type'] = 'pickup_point';
                    $cartItem['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
                } else {
                    $cartItem['shipping_type'] = 'home_delivery';
                }
                $cartItem['shipping_cost'] = 0;
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                }

                if (isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {

                    foreach (json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                        if ($shipping_info['city'] == $shipping_region) {
                            $cartItem['shipping_cost'] = (float)($val);
                            break;
                        } else {
                            $cartItem['shipping_cost'] = 0;
                        }
                    }
                } else {
                    if (
                        !$cartItem['shipping_cost'] ||
                        $cartItem['shipping_cost'] == null ||
                        $cartItem['shipping_cost'] == 'null'
                    ) {

                        $cartItem['shipping_cost'] = 0;
                    }
                }

                $shipping += $cartItem['shipping_cost'];
                $cartItem->save();
            }
            $total = $subtotal + $tax + $shipping;
            return view('frontend.payment_select', compact('carts', 'shipping_info', 'total'));
        } else {
            flash(translate('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }

    public function apply_coupon_code(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
        $response_message = array();

        if ($coupon != null) {
            if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                if (CouponUsage::where('user_id', Auth::user()->id)->where('coupon_id', $coupon->id)->first() == null) {
                    $coupon_details = json_decode($coupon->details);

                    $carts = Cart::where('user_id', Auth::user()->id)
                        ->where('owner_id', $coupon->user_id)
                        ->get();

                    if ($coupon->type == 'cart_base') {
                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach ($carts as $key => $cartItem) {
                            $subtotal += $cartItem['price'] * $cartItem['quantity'];
                            $tax += $cartItem['tax'] * $cartItem['quantity'];
                            $shipping += $cartItem['shipping_cost'];
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
                        }
                    } elseif ($coupon->type == 'product_base') {
                        $coupon_discount = 0;
                        foreach ($carts as $key => $cartItem) {
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                    }
                                }
                            }
                        }
                    }

                    Cart::where('user_id', Auth::user()->id)
                        ->where('owner_id', $coupon->user_id)
                        ->update(
                            [
                                'discount' => $coupon_discount / count($carts),
                                'coupon_code' => $request->code,
                                'coupon_applied' => 1
                            ]
                        );

                    $response_message['response'] = 'success';
                    $response_message['message'] = translate('Coupon has been applied');
                } else {
                    $response_message['response'] = 'warning';
                    $response_message['message'] = translate('You already used this coupon!');
                }
            } else {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('Coupon expired!');
            }
        } else {
            $response_message['response'] = 'danger';
            $response_message['message'] = translate('Invalid coupon!');
        }

        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'))->render();
        return response()->json(array('response_message' => $response_message, 'html' => $returnHTML));
    }

    public function remove_coupon_code(Request $request)
    {
        Cart::where('user_id', Auth::user()->id)
            ->update(
                [
                    'discount' => 0.00,
                    'coupon_code' => '',
                    'coupon_applied' => 0
                ]
            );

        $coupon = Coupon::where('code', $request->code)->first();
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        return view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'));
    }

    public function order_confirmed(Order $order)
    {
        return view('frontend.order_confirmed', compact('order'));
    }
    public function order_failed(Order $order)
    {
        return view('frontend.order_failed', compact('order'));
    }

    public function get_shipping_methods(Request $request)
    {
        $shipping_methods = array();

        if (get_setting('free_shipping_status')) {
            $user_col = "";
            $user_id = "";

            if (Auth::check()) {
                $user_col = "user_id";
                $user_id = Auth::id();
            } else {
                $user_col = "temp_user_id";
                $user_id = getTempUserId();
            }

            $carts = Cart::where($user_col, $user_id)->with('product')->get();



            $cart_total = 0;
            foreach ($carts as $cart) {
                $cart_total += $cart->quantity * $cart->price;
            }

            if (
                $cart_total > get_setting('free_shipping_min_amount') &&
                $cart_total <= get_setting('free_shipping_max_amount')
            ) {
                $shipping_methods['free_shipping'] = get_setting('free_shipping_min_amount');
            }
        }

        if (get_setting('shipping_type') == 'flat_rate') {
            $shipping_methods['flat_rate'] = get_setting('flat_rate_shipping_cost');
        } elseif (get_setting('shipping_type') == 'area_wise_shipping') {
            $shipping_methods['flat_rate'] = get_setting('flat_rate_shipping_cost');
        }

        return response()->json([
            'shipping_methods' => $shipping_methods,
        ], 200);
    }
}
