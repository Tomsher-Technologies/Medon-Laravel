<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Models\Cart;
use Hash;

class ProfileController extends Controller
{

    public function index()
    {
        
    }

    public function getUserAccountInfo(){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $user = User::find($user_id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }else{
            $data = [
                "id" => $user->id,
                "user_type" => $user->user_type,
                "name"  => $user->name,
                "email" => $user->email,
                "phone" => $user->phone ?? "",
                "wallet" => $user->wallet,
                "phone_verified" => $user->phone_verified,
                "created_at" => $user->created_at,
                "wishlist_count" => userWishlistCount($user->id),
                "order_count" => userOrdersCount($user->id),
                "pending_orders" => userPendingOrders($user->id)
            ];
            return response()->json([
                'status' => true,
                'message' => 'User found',
                'data' => $data
            ]);
        }
    }

    public function counters()
    {
        return response()->json([
            'cart_item_count' => Cart::where('user_id', auth()->user()->id)->count(),
            'wishlist_item_count' => Wishlist::where('user_id', auth()->user()->id)->count(),
            'order_count' => Order::where('user_id', auth()->user()->id)->count(),
        ]);
    }

    public function update(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found.")
            ]);
        }
        $user->name = $request->name;

        if ($request->password != "") {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate("Profile information updated")
        ]);
    }
}
