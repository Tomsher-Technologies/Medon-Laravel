<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DeliveryBoyPurchaseHistoryMiniCollection extends ResourceCollection
{
    public function with($request)
    {
        return [
            'status' => true,
            'message' => 'Data fetched successfully'
        ];
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                if(isset($data->delivery_approval)){
                    $type = 'return';
                }else{
                    $type = 'order';
                }
                $shipping_address = json_decode($data->order->shipping_address ?? '', true);
                $grand_total = $data->order->grand_total ?? null;
                $created_at = $data->order->created_at ?? null;
                return [
                    'id' => ($type == 'order') ? $data->order->id  : $data->id ,
                    'code' => $data->order->code ?? '' ,
                    'user_id' => intval($data->order->user_id ?? ''),
                    'payment_type' => ucwords(str_replace('_', ' ', $data->order->payment_type ?? '')),
                    'payment_status' => $data->order->payment_status ?? '',
                    'payment_status_string' => ucwords(str_replace('_', ' ', $data->order->payment_status ?? '')),
                    'delivery_status' => $data->order->delivery_status ?? '',
                    'delivery_status_string' => (($data->order->delivery_status ?? '') == 'pending') ? "Order Placed" : ucwords(str_replace('_', ' ',  $data->delivery_status ?? '')),
                    'grand_total' => ($grand_total != null) ? format_price($grand_total) : '',
                    'date' => ($created_at != null) ? Carbon::createFromFormat('Y-m-d H:i:s', $created_at)->format('d-m-Y') : '',
                    'shipping_address' => $shipping_address,
                    'type' => $type,
                    'delivery_date' => ($type == 'order') ? date('d-m-Y H:i a', strtotime($data->delivery_date)) : (($type == 'return') ? date('d-m-Y H:i a', strtotime($data->delivery_completed_date)) : '')
                ];
            })
        ];
    }

   
}
