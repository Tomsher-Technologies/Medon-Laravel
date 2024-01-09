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
use App\Utility\SmsUtility;
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

        return response()->json([
            'status' => true,
            'completed_delivery' => $orders->where('status', 1)->count(),
            'assigned_delivery' => $orders->whereIn('status', 0)->count()
        ]);
    }

    public function assigned_delivery(Request $request)
    {
        // $orders = Order::where([
        //     'assign_delivery_boy' => $request->user()->id,
        // ])->whereIn('delivery_status', array('picked_up', 'confirmed'))->latest()->get();

        $orders = OrderDeliveryBoys::with(['order'])
                    ->where('delivery_boy_id', $request->user()->id)
                    ->where('status', 0)
                    ->orderBy('id','desc')
                    ->get();
       
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
        $orders = OrderDeliveryBoys::with(['order'])
                    ->where('delivery_boy_id', $request->user()->id)
                    ->where('status', 1)
                    ->orderBy('id','desc')
                    ->get();
       
        if(isset($orders[0]['order']) && !empty($orders[0]['order'])){
            return new DeliveryBoyPurchaseHistoryMiniCollection($orders);
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
        $order = Order::where([
            'assign_delivery_boy' => $request->user()->id,
            'delivery_status' => 'picked_up',
            'id' => $request->order_id
        ])->firstOrFail();

        if ($order) {
            $order->delivery_note = $request->delivery_note;
            $order->delivery_completed_date = Carbon::now();
            $order->delivery_status = 'delivered';

            $file_name = NULL;
            $path = NULL;
            if ($request->hasFile('image')) {
                $file_name = time() . '_' . $request->file('image')->getClientOriginalName();
                $path = 'delivery_images/' . Carbon::now()->year . '/' . Carbon::now()->format('m') . '/';
                Storage::disk('public')->putFileAs($path, $request->file('image'),  $file_name);
            }

            if ($file_name && $path) {
                $order->delivery_image =  $path .  $file_name;
            }

            if ($order->payment_type == 'cash_on_delivery') {
                $order->payment_status = 'paid';
            }

            if ($order->save()) {
                return response()->json([
                    'status' => true,
                    'order_id' => $request->order_id,
                    'message' => "Order Completed",
                ]);
            }
        }
        return response()->json([
            'status' => false,
            'order_id' => "Something went wrong, please try again",
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
                    calculateCommissionAffilationClubPoint($order);
                    $order->commission_calculated = 1;
                }
            }

            $delivery_boy->save();
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
