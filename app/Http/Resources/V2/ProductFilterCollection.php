<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductFilterCollection extends ResourceCollection
{
    
    public function toArray($request)
    {
       return $this->collection->map(function ($data) {
            // echo '<pre>';
            // print_r($data);
            // die;
            $priceData = getProductOfferPrice($data);
            $prodStock = $data->stocks->first();
            return [
                'id' => $data->id,
                'name' => $data->name,
                'sku' => $data->sku,
                'tags' => $data->tags,
                'thumbnail_image' => get_product_image($data->thumbnail_img,'300'),
                'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
                'stroked_price' => $priceData['original_price'],
                'main_price' => $priceData['discounted_price'],
                'price_high_low' => (float)explode('-', home_discounted_base_price($data, false))[0] == (float)explode('-', home_discounted_price($data, false))[1] ? format_price((float)explode('-', home_discounted_price($data, false))[0]) : "From " . format_price((float)explode('-', home_discounted_price($data, false))[0]) . " to " . format_price((float)explode('-', home_discounted_price($data, false))[1]),
                'min_qty' => $data->min_qty,
                'current_stock' => (integer) ($prodStock ? $prodStock->qty : 0),
                'slug' => $data->slug,
                'offer_tag' => $priceData['offer_tag'],
                'return_refund' => $data->return_refund
            ];
        });
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
