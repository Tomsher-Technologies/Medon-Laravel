<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\DeliveryBoyPurchaseHistoryMiniCollection;
use Illuminate\Http\Request;
use App\Models\Delivery\DeliveryBoy;
use App\Models\DeliveryHistory;
use App\Models\Order;
use App\Models\User;
use App\Models\SmsTemplate;
use App\Models\OrderDeliveryBoys;
use App\Models\RefundRequest;
use App\Utility\SmsUtility;
use App\Utility\SendSMSUtility;
use Carbon\Carbon;
use Storage;

class DeliveryBoyController extends Controller
{

    /**
     * Show the list of assigned delivery by the admin.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function dashboard_summary(Request $request)
    {
        $user_id = $request->user()->id;

        $orders = OrderDeliveryBoys::where('delivery_boy_id', $user_id)->get();
        $returns = RefundRequest::where('delivery_boy', $user_id)->get();
        return response()->json([
            'status' => true,
            'completed_delivery' => $orders->where('status', 1)->count() ,
            'completed_returns' => $returns->where('delivery_status', 1)->count(),
            'assigned_delivery' => $orders->whereIn('status', 0)->count() + $returns->where('delivery_status', 0)->count()
        ]);
    }

    public function assigned_delivery(Request $request)
    {
        $order = OrderDeliveryBoys::with(['order'])
                    ->where('delivery_boy_id', $request->user()->id)
                    ->where('status', 0)
                    ->orderBy('id','desc')
                    ->get();

        $return = RefundRequest::with(['order'])
                        ->where('delivery_boy', $request->user()->id)
                        ->where('delivery_status', 0)
                        ->orderBy('id','desc')
                        ->get();
        $orders = $order->merge($return);
        
       
        if(isset($orders[0]['order']) && !empty($orders[0]['order'])){
            return new DeliveryBoyPurchaseHistoryMiniCollection($orders);
        }else {
            return response()->json([
                'status' => true,
                "message" => "No Data Found!"
                ],200);
        }
    }
    public function completed_delivery(Request $request)
    {
        // $orders = Order::where([
        //     'assign_delivery_boy' => $request->user()->id,
        //     'delivery_status' => 'delivered'
        // ])->latest()->get();
        $start_date = $request->has('start_date') ? $request->start_date : '';
        $end_date   = $request->has('end_date') ? $request->end_date : '';

        $orderQuery = OrderDeliveryBoys::with(['order'])
                    ->where('delivery_boy_id', $request->user()->id)
                    ->where('status', 1);

        if($start_date  != '' && $end_date != ''){
           $orderQuery->WhereDate('delivery_date','>=',$start_date)->WhereDate('delivery_date','<=',$end_date);  
        }

        $orders = $orderQuery->orderBy('id','desc')->get();
       
        if(isset($orders[0]['order']) && !empty($orders[0]['order'])){
            return new DeliveryBoyPurchaseHistoryMiniCollection($orders);
        }else {
            return response()->json([
                'status' => true,
                "message" => "No Data Found!"
                ],200);
        }
    }

    public function completed_return_delivery(Request $request)
    {
        $start_date = $request->has('start_date') ? $request->start_date : '';
        $end_date   = $request->has('end_date') ? $request->end_date : '';

        $returnQuery = RefundRequest::with(['order'])
                        ->where('delivery_boy', $request->user()->id)
                        ->where('delivery_status', 1);

        if($start_date  != '' && $end_date != ''){
            $returnQuery->WhereDate('delivery_completed_date','>=',$start_date)->WhereDate('delivery_completed_date','<=',$end_date);  
        }

        $return = $returnQuery->orderBy('id','desc')
                        ->get();
       
        if(isset($return[0]['order']) && !empty($return[0]['order'])){
            return new DeliveryBoyPurchaseHistoryMiniCollection($return);
        }else {
            return response()->json([
                'status' => true,
                "message" => "No Data Found!"
                ],200);
        }
    }

    /**
     * Show the list of pickup delivery by the delivery boy.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function picked_up_delivery(Request $request)
    {
        $order = Order::where([
            'id' => $request->order_id,
            'assign_delivery_boy' => $request->user()->id
        ])->firstOrFail();

        $order->delivery_status = 'picked_up';

        if ($order->save()) {
            return response()->json([
                'status' => true,
                'order_id' => $request->order_id,
                'message' => "Order status changed to picked up"
            ]);
        }

        return response()->json([
            'status' => false,
            'order_id' => $request->order_id,
            'message' => "Somthing went wrong, please try again"
        ]);
    }

    /**
     * Show the list of completed delivery by the delivery boy.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function complete_delivery(Request $request)
    {
        $order_id = $request->order_id ?? '';
        $type = $request->type ?? '';
        $user_id = $request->user()->id ;
        $delivery_note = $request->delivery_note ?? '';
        $payment_status = $request->payment_status ?? 0;

        if($type == 'order'){
            $deliveryOrder = OrderDeliveryBoys::with(['order'])
                            ->where('delivery_boy_id', $user_id)
                            ->where('status', 0)
                            ->where('order_id', $order_id)
                            ->firstOrFail();

            if ($deliveryOrder) {
                $deliveryOrder->delivery_note = $request->delivery_note;
                $deliveryOrder->delivery_date = Carbon::now();
                $deliveryOrder->status = 1;
                $deliveryOrder->payment_status = $payment_status;

                // Update order status as delivered
                $order = Order::find($order_id);
                if($order->delivery_status == 'partial_pick_up'){
                    $order->delivery_status = 'partial_delivery';

                    foreach ($order->orderDetails as $key => $orderDetail) {
                        if ($orderDetail->delivery_status == 'picked_up') {
                            $orderDetail->delivery_status = 'delivered';
                            $orderDetail->delivery_by = $user_id;
                            $orderDetail->delivery_date = date('Y-m-d H:i:s');
                        } 
                        
                        if($order->payment_type == 'cash_on_delivery' && $payment_status == 1){
                            $orderDetail->payment_status = 'paid';
                        }

                        $orderDetail->save();
                    }

                }elseif($order->delivery_status == 'picked_up'){
                    $order->delivery_status = 'delivered';

                    foreach ($order->orderDetails as $key => $orderDetail) {
                        $orderDetail->delivery_status = 'delivered';
                        $orderDetail->delivery_by = $user_id;
                        $orderDetail->delivery_date = date('Y-m-d H:i:s');
                        
                        if($order->payment_type == 'cash_on_delivery' && $payment_status == 1){
                            $orderDetail->payment_status = 'paid';
                        }
                        $orderDetail->save();
                    }
                }

                if($order->payment_type == 'cash_on_delivery' && $payment_status == 1){
                    $order->payment_status = 'paid';
                }

                $order->save();

                $file_name = $name = NULL;
                $path = NULL;
                if ($request->hasFile('image')) {
                    $file_name = time() . '_' . $request->file('image')->getClientOriginalName();
                    $name = Storage::disk('public')->putFileAs(
                        'delivery_images/' . Carbon::now()->year . '/' . Carbon::now()->format('m'),
                        $request->file('image'),
                        $file_name
                    );
                }

                if ($name) {
                    $deliveryOrder->delivery_image =  Storage::url($name);
                }

                if ($deliveryOrder->save()) {
                    return response()->json([
                        'status' => true,
                        'order_id' => $order_id,
                        'message' => "Order Delivery Completed",
                    ]);
                }
            }
        }elseif($type == 'return'){
            $refund = RefundRequest::where('delivery_boy', $user_id)
                                    ->where('delivery_status', 0)
                                    ->where('id', $order_id)
                                    ->firstOrFail();
            if ($refund) {
                $refund->delivery_note = $delivery_note;

                $file_name = $name = NULL;
                $path = NULL;
                if ($request->hasFile('image')) {
                    $file_name = time() . '_' . $request->file('image')->getClientOriginalName();
                    $name = Storage::disk('public')->putFileAs(
                        'return_images/' . Carbon::now()->year . '/' . Carbon::now()->format('m'),
                        $request->file('image'),
                        $file_name
                    );
                }

                if ($name) {
                    $refund->delivery_image =  Storage::url($name);
                }

                $refund->delivery_status = 1;
                $refund->delivery_completed_date = date('Y-m-d H:i:s');
                
                if ($refund->save()) {
                    return response()->json([
                        'status' => true,
                        'order_id' => $order_id,
                        'message' => "Order Return Completed",
                    ]);
                }
            }
        }
    
        return response()->json([
            'status' => false,
            'order_id' => "Order not found",
        ]);
    }

    /**
     * For only delivery boy while changing delivery status.
     * Call from order controller
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function change_delivery_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        $delivery_history = new DeliveryHistory;

        $delivery_history->order_id         = $order->id;
        $delivery_history->delivery_boy_id  = $request->delivery_boy_id;
        $delivery_history->delivery_status  = $order->delivery_status;
        $delivery_history->payment_type     = $order->payment_type;

        if ($order->delivery_status == 'delivered') {
            foreach ($order->orderDetails as $key => $orderDetail) {
                if (addon_is_activated('affiliate_system')) {
                    if ($orderDetail->product_referral_code) {
                        $no_of_delivered = 0;
                        $no_of_canceled = 0;

                        if ($request->status == 'delivered') {
                            $no_of_delivered = $orderDetail->quantity;
                        }
                        if ($request->status == 'cancelled') {
                            $no_of_canceled = $orderDetail->quantity;
                        }

                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                    }
                }
            }
            $delivery_boy = DeliveryBoy::where('user_id', $request->delivery_boy_id)->first();

            if (get_setting('delivery_boy_payment_type') == 'commission') {
                $delivery_history->earning = get_setting('delivery_boy_commission');
                $delivery_boy->total_earning += get_setting('delivery_boy_commission');
            }
            if ($order->payment_type == 'cash_on_delivery') {
                $delivery_history->collection = $order->grand_total;
                $delivery_boy->total_collection += $order->grand_total;

                $order->payment_status = 'paid';
                if ($order->commission_calculated == 0) {
                    // calculateCommissionAffilationClubPoint($order);
                    $order->commission_calculated = 1;
                }
            }

            $delivery_boy->save();

            $message = getOrderStatusMessageTest($order->user->name, $order->code);
            $userPhone = $order->user->phone ?? '';
            
            if($userPhone != '' && isset($message['delivered']) && $message['delivered'] != ''){
                SendSMSUtility::sendSMS($userPhone, $message['delivered']);
            }
        }
        $order->delivery_history_date = date("Y-m-d H:i:s");

        $order->save();
        $delivery_history->save();

        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
            try {
                SmsUtility::delivery_status_change($order->user->phone, $order);
            } catch (\Exception $e) {
            }
        }

        return response()->json([
            'result' => true,
            'message' => translate('Delivery status changed to ') . ucwords(str_replace('_', ' ', $request->status))
        ]);
    }


    public function change_status(Request $request)
    {

        $status = DeliveryBoy::where([
            'user_id' => $request->user()->id
        ])->update([
            'status' => $request->status
        ]);

        if ($status) {
            return response()->json([
                'result' => true,
                'message' => "Rider status changed"
            ], 200);
        }

        return response()->json([
            'result' => false,
            'message' => "Failed"
        ], 404);
    }

    public function get_status(Request $request)
    {
        return response()->json([
            'result' => true,
            'message' => $request->user()->delivery_boy()->first()->status
        ], 200);
    }
}
