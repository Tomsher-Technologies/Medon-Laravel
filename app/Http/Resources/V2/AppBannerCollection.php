<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AppBannerCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $url = $data->link_type == 'external' ? $data->link : $data->link_ref_id;
                return [
                    'image' => $data->mainImage->file_name,
                    'url_type' => $data->link_type,
                    'url' => $url,
                    'position' => $data->sort_order
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
