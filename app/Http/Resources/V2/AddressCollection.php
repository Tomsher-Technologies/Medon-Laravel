<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AddressCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {

                $location_available = false;
                $lat = 0;
                $lang = 0;

                if($data->latitude || $data->longitude) {
                    $location_available = true;
                    $lat = floatval($data->latitude) ;
                    $lang = floatval($data->longitude);
                }

                return [
                    'id'      =>(int) $data->id,
                    'user_id' =>(int) $data->user_id,
                    'name' =>(int) $data->name,
                    'address' => $data->address,
                    'country_id' => (int)  $data->country_id,
                    'state_id' =>  (int) $data->state_id,                  
                    'country' => $data->country->name,
                    'state' => $data->state->name,
                    'city' => $data->city,
                    'postal_code' => $data->postal_code,
                    'phone' => $data->phone,
                    'set_default' =>(int) $data->set_default,
                    'location_available' => $location_available,
                    'lat' => $lat,
                    'lang' => $lang,
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
