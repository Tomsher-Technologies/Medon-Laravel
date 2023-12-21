<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CartCollection;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
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
            $carts = Cart::where('user_id', $user_id)->get();
            if(!empty($carts[0])){
                $carts->load(['product', 'product.stocks']);
            }
            
        } else {
            $temp_user_id = $request->header('UserToken');
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
            
            if(!empty($carts[0])){
                $carts->load(['product', 'product.stocks']);
            }
        }
        

        $result = [];
        $sub_total = $discount = $shipping = 0;
        if(!empty($carts[0])){
            foreach($carts as $data){
                $sub_total = $sub_total + ($data->price * $data->quantity);
                $result['products'][] = [
                    'id' => $data->id,
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'image' => app('url')->asset($data->product->thumbnail_img)
                    ],
                    'variation' => $data->variation,
                    'price' => $data->price,
                    'quantity' => (integer) $data->quantity,
                    'date' => $data->created_at->diffForHumans(),
                    'total' => $data->price * $data->quantity
                ];
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
            'total' => round($sub_total - ($sub_total * ($discount/100)), 2)
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
                        'message' => 'This item is out of stock!',
                    ], 200);
                }
            } else {
                $product_stock = $product->stocks->first();
                if (($product_stock->qty < $request['quantity']) || ($product->hide_price)) {
                    return response()->json([
                        'message' => 'This item is out of stock!',
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

                $discount_applicable = false;

                if (
                    $product->discount_start_date == null ||
                    (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                        strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date)
                ) {
                    $discount_applicable = true;
                }

                if ($discount_applicable) {
                    if ($product->discount_type == 'percent') {
                        $price -= ($price * $product->discount) / 100;
                    } elseif ($product->discount_type == 'amount') {
                        $price -= $product->discount;
                    }
                }

                $data[$user['users_id_type']] =  $user['users_id'];
                $data['product_id'] = $product->id;
                $data['quantity'] = $request['quantity'] ?? 1;
                $data['price'] = $price;
                $data['variation'] = $str;
                $data['tax'] = 0;
                $data['shipping_cost'] = 0;
                $data['product_referral_code'] = null;
                $data['cash_on_delivery'] = $product->cash_on_delivery;
                $data['digital'] = $product->digital;

                $rtn_msg = 'Item added to cart';

                Cart::create($data);
            }

            return response()->json([
                'success' => true,
                'message' => $rtn_msg,
                'count' =>  $this->cartCount()
            ], 201);
        }

        return response()->json([
            'success' => false,
            'cart_count' => "Something went wrong, please try again"
        ]);
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
        $request->validate([
            'cart_id' => 'required',
            'quantity' => 'required',
        ], [
            'cart_id.required' => 'Please enter a cart id',
            'quantity.required' => 'Please enter a qantity',
        ]);

        $user = getUser();

        $cart = Cart::where([
            $user['users_id_type'] => $user['users_id']
        ])->with([
            'product',
            'product.stocks',
        ])->findOrFail($request->cart_id);

        $min_qty = $cart->product->min_qty;
        $max_qty = 0;
        $quantity =  $request->quantity;

        if ($cart->product->variant_product) {
        } else {
            $max_qty = $cart->product->stocks->first()->qty;
        }

        if ($quantity >= $min_qty && $quantity <= $max_qty) {
            $cart->quantity = $quantity;
            $cart->save();
            return response()->json([
                'success' => true,
                'message' => "Cart updated",
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Quantity not matched",
                'min_qty' => $min_qty,
                'max_qty' => $max_qty,
            ]);
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
}
