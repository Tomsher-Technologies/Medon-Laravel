<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\OrderTracking;
use App\Models\Prescriptions;
use App\Models\Shops;
use App\Models\Delivery\DeliveryBoy;
use App\Models\LiveLocations;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use App\Utility\SendSMSUtility;
use Carbon\Carbon;
use Hash;
use Storage;
use Str;
use File;

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
                "eid_front" => $user->getEidFrontImage(),
                "eid_back" => $user->getEidBackImage(),
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

    public function updateUserData(Request $request){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $user = User::find($user_id);

        if($user){
            $request->validate([
                'eid_front' => 'nullable|max:300',
                'eid_back' => 'nullable|max:300',
            ],[
                'eid_front.max' => 'File size should be less than 300 KB',
                'eid_back.max' => 'File size should be less than 300 KB'
            ]);

            $presentFrontImage = $user->eid_image_front;
            $presentBackImage = $user->eid_image_back;
            
            if ($request->hasFile('eid_front')) {
                $eid_front = $request->file('eid_front');
                $filename =    strtolower(Str::random(2)).time().'.'. $eid_front->getClientOriginalName();
                $name = Storage::disk('public')->putFileAs(
                    'users/'.$user_id,
                    $eid_front,
                    $filename
                );
               $user->eid_image_front = Storage::url($name) ;
               if($presentFrontImage != '' && File::exists(public_path($presentFrontImage))){
                    unlink(public_path($presentFrontImage));
                }
            } 

            if ($request->hasFile('eid_back')) {
                $eid_back = $request->file('eid_back');
                $filename =    strtolower(Str::random(2)).time().'.'. $eid_back->getClientOriginalName();
                $name = Storage::disk('public')->putFileAs(
                    'users/'.$user_id,
                    $eid_back,
                    $filename
                );
                $user->eid_image_back = Storage::url($name);
                if($presentBackImage != '' && File::exists(public_path($presentBackImage))){
                    unlink(public_path($presentBackImage));
                }
            } 

            $user->name = $request->name ?? NULL;
            $user->phone = $request->phone ?? NULL;
            $user->save(); 
            return response()->json(['status' => true,'message' => 'User details updated successfully']);   
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
    }

    public function orderList(Request $request){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $user = User::find($user_id);
        if($user){
            $sort_search = null;
            $delivery_status = null;
            $limit = $request->limit ? $request->limit : 10;
            $offset = $request->offset ? $request->offset : 0;
            // $date = $request->date;

            $orders = Order::select('id','code','delivery_status','payment_type','grand_total','created_at')->orderBy('id', 'desc')->where('user_id',$user_id);
            if ($request->has('search')) {
                $sort_search = $request->search;
                $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            }
            if ($request->delivery_status != null) {
                $orders = $orders->where('delivery_status', $request->delivery_status);
                $delivery_status = $request->delivery_status;
            }
            // if ($date != null) {
            //     $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
            // }
            
            $total_count = $orders->count();
            $data['orders'] = $orders->skip($offset)->take($limit)->get();
            
            $data['next_offset'] = $offset + $limit;

            return response()->json(['status' => true,'message' => 'Data fetched successfully','data' => $data]);   
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
    }

    public function orderDetails(Request $request){
        $order_code = $request->order_code ?? '';
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $default_return_time = get_setting('default_return_time') ?? 0;
        if($order_code != ''){
            $order = Order::where('code',$order_code)->where('user_id',$user_id)->first();
            if($order){
                $details['id'] = $order->id ?? '';
                $details['code'] = $order->code ?? '';
                $details['user_id'] = $order->user_id ?? '';
                $details['shipping_address'] = json_decode($order->shipping_address ?? '');
                $details['billing_address'] = json_decode($order->billing_address ?? '');
                $details['order_notes'] = $order->order_notes ?? '';
                $details['shipping_type'] = $order->shipping_type ?? '';
                $details['shipping_cost'] = $order->shipping_cost ?? '';
                $details['delivery_status'] = $order->delivery_status ?? '';
                $details['payment_type'] = $order->payment_type ?? '';
                $details['payment_status'] = $order->payment_status ?? '';
                $details['tax'] = $order->tax ?? '';
            
                $details['sub_total'] = $order->sub_total ?? '';
                $details['coupon_discount'] = $order->coupon_discount ?? '';
                $details['offer_discount'] = $order->offer_discount ?? '';
                $details['grand_total'] = $order->grand_total ?? '';
                $details['wallet_deduction'] = $order->wallet_deduction ?? '';
                $details['card_deduction'] = $order->grand_total - $order->wallet_deduction;
                $details['delivery_completed_date'] = $order->delivery_completed_date ?? '';
                $details['date'] = date('d-m-Y h:i A', $order->date);
                $details['cancel_request'] = $order->cancel_request;
    
                $details['products'] = [];
                if($order->orderDetails){
                    foreach($order->orderDetails as $product){
                        $requestCount = ($product->refund_request) ? 1 : 0 ;
                        $return_expiry = null;
                        if($product->delivery_date != null && $default_return_time != 0 ){
                            $return_expiry = getDatePlusXDays($product->delivery_date, $default_return_time);
                        }

                        $details['products'][] = array(
                            'id' => $product->id ?? '',
                            'product_id' => $product->product_id ?? '',
                            'name' => $product->product->name ?? '',
                            'sku' => $product->product->sku ?? '',
                            'slug' => $product->product->slug ?? '',
                            'original_price' => $product->og_price ?? '',
                            'offer_price' => $product->offer_price ?? '',
                            'quantity' => $product->quantity ?? '',
                            'total_price' => $product->price ?? '',
                            'delivery_status' => $product->delivery_status ?? '',
                            'delivery_date'   => $product->delivery_date ?? '',
                            'thumbnail_img' => app('url')->asset($product->product->thumbnail_img ?? ''),
                            'return_refund' => $product->product->return_refund ?? '',
                            'refund_requested' => $requestCount,
                            'return_expiry' =>  $return_expiry
                        );
                    }
                }

                $tracks = OrderTracking::where('order_id', $order->id)->orderBy('id','ASC')->get();
                $track_list = [];
                if ($tracks) {
                    foreach ($tracks as $key=>$value) {
                        $temp = array();
                        $temp['id'] = $value->id;
                        $temp['status'] = $value->status;
                        $temp['date'] = date("d-m-Y H:i a", strtotime($value->status_date));
                        $track_list[] = $temp;
                    }
                }    
                $details['timeline'] = $track_list;
                
                return response()->json([
                    'status' => true,
                    'message' => 'Order found',
                    'data'=> $details
                ],200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'No Order Found!',
                ], 200);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }
    }

    
    public function uploadPrescription(Request $request){
        // print_r($request->all());
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        if($user_id != ''){
            $request->validate([
                'eid_front' => 'nullable|mimes:jpg,jpeg,png,bmp,svg,webp,pdf,doc,docx|max:300',
                'eid_back' => 'nullable|mimes:jpg,jpeg,png,bmp,svg,webp,pdf,doc,docx|max:300',
                'prescription' => 'required|mimes:jpg,jpeg,png,bmp,svg,webp,pdf,doc,docx|max:1024',
            ],[
                'eid_front.max' => 'File size should be less than 300 KB',
                'eid_back.max' => 'File size should be less than 300 KB',
                'prescription.required' => "Prescription is required",
                'prescription.max' => 'File size should be less than 1 MB'
            ]);
        }else{
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'eid_front' => 'required|mimes:jpg,jpeg,png,bmp,svg,webp,pdf,doc,docx|max:300',
                'eid_back' => 'required|mimes:jpg,jpeg,png,bmp,svg,webp,pdf,doc,docx|max:300',
                'prescription' => 'required|mimes:jpg,jpeg,png,bmp,svg,webp,pdf,doc,docx|max:1024',
            ],[
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'phone.required' => 'Phone is required',
                'eid_front.max' => 'File size should be less than 300 KB',
                'eid_back.max' => 'File size should be less than 300 KB',
                'prescription.required' => "Prescription is required",
                'eid_front.required' => "Emirates ID front is required",
                'eid_back.required' => "Emirates ID back is required",
                'prescription.max' => 'File size should be less than 1 MB'
            ]);
        }
        
        $presentFrontImage = $presentBackImage = '';
        if($user_id == ''){
            $data = [
                'name'=> $request->name ?? '',
                'email'=> $request->email ?? '',
                'phone' => $request->phone ?? '',
                'comment' => $request->comment ?? '',
            ];
        }else{
            $data = [
                'comment' => $request->comment ?? '',
                'user_id' => $user_id,
            ];
            $user = User::find($user_id);
            $presentFrontImage = $user->eid_image_front;
            $presentBackImage = $user->eid_image_back;
        }

        if ($request->hasFile('eid_front')) {
            $eid_front = $request->file('eid_front');
            $filename =    strtolower(Str::random(2)).time().'.'. $eid_front->getClientOriginalName();
            $name = Storage::disk('public')->putFileAs(
                'prescriptions/eid',
                $eid_front,
                $filename
            );
            
            $eid_image_front = Storage::url($name) ;
            $data['emirates_id_front'] = $eid_image_front;

            if($user_id != ''){
                Storage::disk('public')->put('users/'.$user_id.'/'.$filename, Storage::disk('public')->get(str_replace('/storage','', $eid_image_front)));
                $user->eid_image_front  = Storage::url('users/'.$user_id.'/'.$filename) ;
                if($presentFrontImage != '' && File::exists(public_path($presentFrontImage))){
                    unlink(public_path($presentFrontImage));
                }
            }
        } 

        if ($request->hasFile('eid_back')) {
            $eid_back = $request->file('eid_back');
            $filename =    strtolower(Str::random(2)).time().'.'. $eid_back->getClientOriginalName();
            $name = Storage::disk('public')->putFileAs(
                'prescriptions/eid',
                $eid_back,
                $filename
            );
            $eid_image_back = Storage::url($name);
            $data['emirates_id_back'] = $eid_image_back;
            
            if($user_id != ''){
                Storage::disk('public')->put('users/'.$user_id.'/'.$filename, Storage::disk('public')->get(str_replace('/storage','', $eid_image_back)));
                $user->eid_image_back  = Storage::url('users/'.$user_id.'/'.$filename) ;
                if($presentBackImage != '' && File::exists(public_path($presentBackImage))){
                    unlink(public_path($presentBackImage));
                }
            }
        }

        if ($request->hasFile('prescription')) {
            $prescription = $request->file('prescription');
            $filename =    strtolower(Str::random(2)).time().'.'. $prescription->getClientOriginalName();
            $name = Storage::disk('public')->putFileAs(
                'prescriptions',
                $prescription,
                $filename
            );
            $prescriptionFile = Storage::url($name);
            $data['prescription'] = $prescriptionFile;
        }

        if($user_id != ''){
            $user->save(); 
        }
        Prescriptions::create($data);
        return response()->json(['status' => true,'message' => 'Prescription uploaded successfully']);   
    }

    public function getPrescriptions(Request $request){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $limit = $request->limit ? $request->limit : 10;
        $offset = $request->offset ? $request->offset : 0;
        if($user_id != ''){
            $query = Prescriptions::where('user_id', $user_id)->orderBy('id','desc');
            $total_count = $query->count();
            $prescriptions = $query->skip($offset)->take($limit)->get();
            
            $details = [];
            foreach($prescriptions as $pre){
                $details[] = [
                    "id" => $pre->id ,
                    "comment"=> $pre->comment,
                    "prescription" => asset($pre->prescription),
                    "front_side" => asset($pre->user->eid_image_front ??  $pre->emirates_id_front),
                    "back_side" => asset($pre->user->eid_image_back ??  $pre->emirates_id_back),
                    "date" => date("d-m-Y H:i a",strtotime($pre->created_at)),
                ];
            }
            $data['total_count'] = $total_count;
            $data['prescriptions'] = $details;
            $data['next_offset'] = $offset + $limit;
            return response()->json(['status' => true,'message' => 'Prescriptions fetched successfully', 'data' =>  $data]);   
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Prescriptions not found'
            ]);
        }
    }

    public function saveLiveLocation(Request $request){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';
        $order_code = $request->order_code;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $type = $request->type ?? '';

        if($user_id != '' && $order_code != '' && $latitude != '' && $longitude != ''){
            if($type == 'order'){
                $order = Order::where('code', $order_code)->first();
                if(!$order) {
                    return response()->json([
                        'status'=>false,
                        'message'=>'Order Not Found!'
                        ],200);
                }else{
                    $shop = Shops::find(auth('sanctum')->user()->shop_id);
                    $shop_latitude = $shop->delivery_pickup_latitude;
                    $shop_longitude = $shop->delivery_pickup_longitude;
    
                    $deliveryBoyStatus = DeliveryBoy::where('user_id',$user_id)->where('status',1)->count();
                    if($deliveryBoyStatus != 0){
                        $checkLoc = LiveLocations::where('user_id', $user_id)->where('order_id', $order->id)->first();
                    
                        if(!empty($checkLoc)){
                            $checkLoc->latitude = $latitude;
                            $checkLoc->longitude = $longitude;
                            $checkLoc->distance = distanceCalculator($shop_latitude, $shop_longitude, $latitude, $longitude,'km');
                            $checkLoc->save();
                        }else{
                            $loc = new LiveLocations;
                            $loc->user_id = $user_id;
                            $loc->order_id = $order->id;
                            $loc->latitude = $latitude;
                            $loc->longitude = $longitude;
                            $loc->distance = distanceCalculator($shop_latitude, $shop_longitude, $latitude, $longitude, 'km');
                            $loc->save();
                        }
        
                        return response()->json(['status' => true,'message' => 'Live location saved']); 
                    }else{
                        return response()->json(['status' => false ,'message'=>"Delivery boy not available"]);
                    }
                }
            }elseif($type == 'return'){
                $return = RefundRequest::where('id', $order_code)->firstOrFail();
                $order_latitude = $order_longitude = '';
                if($return->order->shipping_address){
                    $shipping_address = json_decode($return->order->shipping_address);
                    $order_latitude = $shipping_address->latitude ?? '';
                    $order_longitude = $shipping_address->longitude ?? '';
                }

                if(!$return) {
                    return response()->json([
                        'status'=>false,
                        'message'=>'Request Not Found!'
                        ],200);
                }else{
                    if($order_latitude != '' && $order_longitude != ''){
                        $deliveryBoyStatus = DeliveryBoy::where('user_id',$user_id)->where('status',1)->count();
                        if($deliveryBoyStatus != 0){
                            $checkLoc = LiveLocations::where('user_id', $user_id)->where('return_id', $return->id)->first();
                            if(!empty($checkLoc)){
                                $checkLoc->latitude = $latitude;
                                $checkLoc->longitude = $longitude;
                                $checkLoc->distance = distanceCalculator($order_latitude, $order_longitude, $latitude, $longitude,'km');
                                $checkLoc->save();
                            }else{
                                $loc = new LiveLocations;
                                $loc->user_id = $user_id;
                                $loc->return_id = $return->id;
                                $loc->latitude = $latitude;
                                $loc->longitude = $longitude;
                                $loc->distance = distanceCalculator($order_latitude, $order_longitude, $latitude, $longitude, 'km');
                                $loc->save();
                            }
                            return response()->json(['status' => true,'message' => 'Live location saved']); 
                        }else{
                            return response()->json(['status' => false ,'message'=>"Delivery boy not available"]);
                        }
                    }else{
                        return response()->json([
                            'status' => false,
                            'message' => 'Order shipping address not correct'
                        ]);
                    }   
                }
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid data'
            ]);
        }
    }
}
