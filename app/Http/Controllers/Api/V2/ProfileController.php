<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Utility\SendSMSUtility;
use Carbon\Carbon;
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
                "pending_orders" => userPendingOrders($user->id),
                "default_address" => userDefaultAddress($user->id)
            ];
            return response()->json([
                'status' => true,
                'message' => 'User found',
                'data' => $data
            ]);
        }
    }

    public function changePassword(Request $request)
    {
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $user = User::find($user_id);
        if($user){
            // The passwords matches
            if (!Hash::check($request->get('current_password'), $user->password)){
                return response()->json(['status' => false,'message' => 'Old password is incorrect', 'data' => []]);
            }

            // Current password and new password same
            if (strcmp($request->get('current_password'), $request->new_password) == 0){
                return response()->json(['status' => false,'message' => 'New Password cannot be same as your current password.', 'data' => []]);
            }

            $user->password =  Hash::make($request->new_password);
            $user->save();
            return response()->json(['status' => true,'message' => 'Password Changed Successfully', 'data' => []]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
    }

    public function sendOTPPhonenumber(Request $request){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $user = User::find($user_id);
        $phone = $request->phone ?? '';
        if($user && ($phone != '')){
            $user->verification_code = rand(100000, 999999);
            $user->verification_code_expiry = Carbon::now()->addMinutes(5);
            $user->save();
            $message = "Hi $user->name, Greetings from Farook! Your OTP: $user->verification_code Treat this as confidential. Sharing this with anyone gives them full access to your Farook Account.";
    
            $status = SendSMSUtility::sendSMS($phone, $message);
            return response()->json(['status'=>true,'message'=>'Verification code sent to your phone number']);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
    }

    public function verifyPhonenumber(Request $request){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $user = User::find($user_id);
        $otp = $request->otp ?? '';
        if($user){
            if(($otp != '')){
                if($user->verification_code == $request->otp && Carbon::parse($user->verification_code_expiry
                ) > Carbon::now()){
                    $user->phone_verified = 1;
                    $user->verification_code_expiry = null;
                    $user->verification_code = null;
                    $user->save();
                    return response()->json(['status'=>true,'message'=>'Phone number verified successfully']);
                }else{
                    return response()->json(['status'=>false,'message'=>'Invalid OTP or code expired'],200);
                }
            }else{
                return response()->json(['status'=>false,'message'=>'Invalid OTP'],200);
            }    
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
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
