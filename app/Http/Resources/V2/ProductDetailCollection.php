<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;
use Illuminate\Http\Resources\Json\JsonResource;


class ProductDetailCollection extends JsonResource
{
    public function toArray($request)
    {
        $precision = 2;
        $calculable_price = home_discounted_base_price($this, false);
        $calculable_price = number_format($calculable_price, $precision, '.', '');
        $calculable_price = floatval($calculable_price);
        
        $photo_paths = explode(',',$this->photos);
        
        $photos = [];
        if (!empty($photo_paths)) {
            for ($i = 0; $i < count($photo_paths); $i++) {
                if ($photo_paths[$i] != "") {
                    $item = array();
                    $item['variant'] = "";
                    $item['path'] = app('url')->asset($photo_paths[$i]);
                    $photos[] = $item;
                }
            }
        }

        foreach ($this->stocks as $stockItem) {
            if ($stockItem->image != null && $stockItem->image != "") {
                $item = array();
                $item['variant'] = $stockItem->variant;
                $item['path'] = api_upload_asset($stockItem->image);
                $photos[] = $item;
            }
        }

        $brand = [
            'id' => 0,
            'name' => "",
            'logo' => "",
        ];

        if ($this->brand != null) {
            $brand = [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'logo' => api_upload_asset($this->brand->logo),
            ];
        }

        return [
            'id' => (int)$this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'photos' => $photos,
            'thumbnail_image' => app('url')->asset($this->thumbnail_img),
            'tags' => explode(',', $this->tags),
            'price_high_low' => (float)explode('-', home_discounted_base_price($this, false))[0] == (float)explode('-', home_discounted_price($this, false))[1] ? format_price((float)explode('-', home_discounted_price($this, false))[0]) : "From " . format_price((float)explode('-', home_discounted_price($this, false))[0]) . " to " . format_price((float)explode('-', home_discounted_price($this, false))[1]),
            'choice_options' => $this->convertToChoiceOptions(json_decode($this->choice_options)),
            'has_discount' => home_base_price($this, false) != home_discounted_base_price($this, false),
            'stroked_price' => home_base_price($this, false),
            'main_price' => home_discounted_base_price($this, false),
            'calculable_price' => $calculable_price,
            'currency_symbol' => currency_symbol(),
            'unit' => $this->unit,
            'rating' => (float)$this->rating,
            'rating_count' => (int)Review::where(['product_id' => $this->id])->count(),
            'earn_point' => (float)$this->earn_point,
            'description' => $this->description,
            'video_link' => $this->video_link != null ?  $this->video_link : "",
            'brand' => $brand,
            'tabs' => $this->tabs,
            'reviews' => $this->reviews
        ];

        // return [
        //     'data' => $this->collection->map(function ($data) {


        //         // $calculable_price = round($calculable_price, 2);











        //         return [
        //             'id' => (int)$data->id,
        //             'name' => $data->name,
        //             'added_by' => $data->added_by,
        //             'seller_id' => $data->user->id,
        //             'shop_id' => $data->added_by == 'admin' ? 0 : $data->user->shop->id,
        //             'shop_name' => $data->added_by == 'admin' ? translate('In House Product') : $data->user->shop->name,
        //             'shop_logo' => $data->added_by == 'admin' ? api_asset(get_setting('header_logo')) : api_asset($data->user->shop->logo),
        //             'photos' => $photos,
        //             'thumbnail_image' => api_asset($data->thumbnail_img),
        //             'tags' => explode(',', $data->tags),
        //             'price_high_low' => (float)explode('-', home_discounted_base_price($data, false))[0] == (float)explode('-', home_discounted_price($data, false))[1] ? format_price((float)explode('-', home_discounted_price($data, false))[0]) : "From " . format_price((float)explode('-', home_discounted_price($data, false))[0]) . " to " . format_price((float)explode('-', home_discounted_price($data, false))[1]),
        //             'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
        //             'colors' => json_decode($data->colors),
        //             'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
        //             'stroked_price' => home_base_price($data),
        //             'main_price' => home_discounted_base_price($data),
        //             'calculable_price' => $calculable_price,
        //             'currency_symbol' => currency_symbol(),
        //             'current_stock' => (int)$data->stocks->first()->qty,
        //             'unit' => $data->unit,
        //             'rating' => (float)$data->rating,
        //             'rating_count' => (int)Review::where(['product_id' => $data->id])->count(),
        //             'earn_point' => (float)$data->earn_point,
        //             'description' => $data->description,
        //             'video_link' => $data->video_link != null ?  $data->video_link : "",
        //             'brand' => $brand,
        //             'link' => route('product', $data->slug)
        //         ];
        //     })
        // ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    protected function convertToChoiceOptions($data)
    {
        $result = array();
        //        if($data) {
        foreach ($data as $key => $choice) {
            $item['id'] = (int)$choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->name;
            $item['options'] = $choice->values;
            array_push($result, $item);
        }
        //        }
        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, api_asset($item));
        }
        return $result;
    }
}
