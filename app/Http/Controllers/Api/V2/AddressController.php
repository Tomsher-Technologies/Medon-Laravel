<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\City;
use App\Models\Country;
use App\Http\Resources\V2\AddressCollection;
use App\Models\Address;
use App\Http\Resources\V2\CitiesCollection;
use App\Http\Resources\V2\StatesCollection;
use App\Http\Resources\V2\CountriesCollection;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\State;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return new AddressCollection(Address::where('user_id', $request->user()->id)->get());
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'type' => 'required',
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ], [
            'type.required' => 'Please enter address type',
            'name.required' => 'Please enter your name',
            'address.required' => 'Please enter your address',
            'latitude.required' => 'Please enter your latitude',
            'longitude.required' => 'Please enter your longitude',
            'phone.required' => 'Please enter your phone',
        ]);

        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';

        if($user_id != ''){
            $address = new Address;
            $address->user_id = $user_id;
            $address->type = $request->type ?? null;
            $address->address = $request->address ?? null;
            $address->name = $request->name ?? null;
            $address->country_id = getCountryId($request->country);
            $address->state_id = getStateId($request->state);
            $address->country_name = $request->country ?? null;
            $address->state_name = $request->state ?? null;
            $address->city = $request->city ?? null;
            // $address->postal_code = $request->postal_code;
            $address->longitude = $request->longitude ?? null;
            $address->latitude = $request->latitude ?? null;
            $address->phone = $request->phone ?? null;
            $address->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Address has been added successfully'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
    }

    public function update(Address $address, Request $request)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'result' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validate = $request->validate([
            'name' => 'required',
            'address' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city' => 'required',
            'phone' => 'required',
        ], [
            'name.required' => 'Please enter your name',
            'address.required' => 'Please enter your address',
            'country_id.required' => 'Please select your address',
            'state_id.required' => 'Please select your address',
            'city.required' => 'Please enter your city',
            'phone.required' => 'Please enter your phone',
        ]);

        $address->address = $request->address;
        $address->name = $request->name;
        $address->country_id = $request->country_id;
        $address->state_id = $request->state_id;
        $address->city = $request->city;
        $address->postal_code = $request->postal_code;
        $address->longitude = $request->longitude;
        $address->latitude = $request->latitude;
        $address->phone = $request->phone;

        $address->save();

        return response()->json([
            'result' => true,
            'message' =>'Shipping information has been updated successfully'
        ]);
    }

    public function updateShippingAddressLocation(Request $request)
    {
        $address = Address::find($request->id);
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->save();

        return response()->json([
            'result' => true,
            'message' => 'Shipping location in map updated successfully'
        ]);
    }


    public function destroy(Request $request)
    {
        $validate = $request->validate([
            'address_id' => 'required'
        ], [
            'address_id.required' => 'Please enter address id'
        ]);

        $address =  Address::where([
            'id' => $request->address_id,
            'user_id' => $request->user()->id
        ])->firstOrFail();

        $address->delete();
        return response()->json([
            'result' => true,
            'message' =>'Shipping information has been deleted'
        ]);
    }

    public function makeShippingAddressDefault(Request $request)
    {

        $validate = $request->validate([
            'address_id' => 'required'
        ], [
            'address_id.required' => 'Please enter address id'
        ]);

        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';

        if($user_id != ''){
            $address =  Address::where([
                'id' => $request->address_id,
                'user_id' => $user_id
            ])->first();

            if($address){
                Address::where('user_id', $user_id)->update(['set_default' => 0]); //make all user addressed non default first
    
                $add = Address::find($request->address_id);
                $add->set_default = 1;
                $add->save();
                
                return response()->json([
                    'status' => true,
                    'message' => 'Default address has been updated'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Default address not updated'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
    }

    public function updateAddressInCart(Request $request)
    {
        try {
            Cart::where('user_id', auth()->user()->id)->update(['address_id' => $request->address_id]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Could not save the address'
            ]);
        }
        return response()->json([
            'result' => true,
            'message' => 'Address is saved'
        ]);
    }

    public function getCities()
    {
        return new CitiesCollection(City::where('status', 1)->get());
    }

    public function getStates()
    {
        return new StatesCollection(State::where('status', 1)->get());
    }

    public function getCountries(Request $request)
    {
        $country_query = Country::where('status', 1);
        if ($request->name != "" || $request->name != null) {
            $country_query->where('name', 'like', '%' . $request->name . '%');
        }
        $countries = $country_query->get();

        return new CountriesCollection($countries);
    }

    public function getCitiesByState($state_id, Request $request)
    {
        $city_query = City::where('status', 1)->where('state_id', $state_id);
        if ($request->name != "" || $request->name != null) {
            $city_query->where('name', 'like', '%' . $request->name . '%');
        }
        $cities = $city_query->get();
        return new CitiesCollection($cities);
    }

    public function getStatesByCountry(Request $request)
    {
        $states = State::where([
            'status' => 1,
            'country_id' => $request->country_id
        ])->get();
        return new StatesCollection($states);
    }

    public function deleteAddress(Request $request){
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';

        if($user_id != ''){
            $check = Address::where(['id' => $request->address_id,'user_id' => $user_id])->count();
            if($check != 0){
                Address::where(['id' => $request->address_id,'user_id' => $user_id])->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Address deleted successfullty'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Address not found'
                ]);
            }
            
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
    }

    public function updateAddress(Request $request){
        $validate = $request->validate([
            'address_id' => 'required',
            'type' => 'required',
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ], [
            'address_id.required' => 'Please enter address id',
            'type.required' => 'Please enter address type',
            'name.required' => 'Please enter your name',
            'address.required' => 'Please enter your address',
            'latitude.required' => 'Please enter your latitude',
            'longitude.required' => 'Please enter your longitude',
            'phone.required' => 'Please enter your phone',
        ]);

        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';

        if($user_id != ''){
            $address = Address::find($request->address_id);
            if($address){
                if ($address->user_id !== $user_id) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }else{
                    $address->type = $request->type ?? null;
                    $address->address = $request->address ?? null;
                    $address->name = $request->name ?? null;
                    $address->country_id = getCountryId($request->country);
                    $address->state_id = getStateId($request->state);
                    $address->country_name = $request->country ?? null;
                    $address->state_name = $request->state ?? null;
                    $address->city = $request->city ?? null;
                    $address->longitude = $request->longitude ?? null;
                    $address->latitude = $request->latitude ?? null;
                    $address->phone = $request->phone ?? null;
                    $address->save();
            
                    return response()->json([
                        'status' => true,
                        'message' => 'Address has been updated successfully'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Address not found'
                ]);
            }
            
        }
    }
}
