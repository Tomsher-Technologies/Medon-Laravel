<?php

use App\Models\Category;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $order_details = OrderDetail::find(1);

        for ($i = 2; $i < 22; $i++) {
            $newPost = $order_details->replicate();
            $newPost->order_id = $i;
            $newPost->created_at = Carbon::now();
            $newPost->save();
        }

        //     $total = rand(100, 5000);


        //     $combined_order = CombinedOrder::create([
        //         'user_id' => $order->user_id,
        //         'shipping_address' => $order->shipping_address,
        //         'grand_total' => $total,
        //     ]);

        //     $newPost = $order->replicate();
        //     $newPost->combined_order_id = $combined_order->id;
        //     $newPost->grand_total = $total;
        //     $newPost->code = date('Ymd-His') . rand(10, 99);
        //     $newPost->created_at = Carbon::now();
        //     $newPost->save();


        //     foreach ($order->orderDetails() as $cart) {
        //         OrderDetail::create([
        //             'order_id' => $newPost->id,
        //             'product_id' => $cart->product_id,
        //             'variation' => $cart->variation,
        //             'og_price' => $cart->price,
        //             'price' => $cart->price * $cart->quantity,
        //             'quantity' => $cart->quantity,
        //         ]);
        //     }
        // }
    }
}
