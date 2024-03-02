<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WishlistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id' => (int) $data->id,
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'slug' => $data->product->slug,
                        'thumbnail_image' => get_product_image($data->product->thumbnail_img,'300'),
                        'has_discount' => home_base_price($data->product, false) != home_discounted_base_price($data->product, false),
                        'stroked_price' => home_base_price($data->product),
                        'main_price' => home_discounted_base_price($data->product),
                        'price_high_low' => (float)explode('-', home_discounted_base_price($data->product, false))[0] == (float)explode('-', home_discounted_price($data->product, false))[1] ? format_price((float)explode('-', home_discounted_price($data->product, false))[0]) : "From " . format_price((float)explode('-', home_discounted_price($data->product, false))[0]) . " to " . format_price((float)explode('-', home_discounted_price($data->product, false))[1]),
                    ]
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
