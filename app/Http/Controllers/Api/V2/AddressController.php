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

        $address = new Address;
        $address->user_id = $request->user()->id;
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
            'message' => 'Shipping information has been added successfully'
        ]);
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

        $address =  Address::where([
            'id' => $request->address_id,
            'user_id' => $request->user()->id
        ])->firstOrFail();

        Address::where('user_id', $request->user()->id)->update(['set_default' => 0]); //make all user addressed non default first

        $address->set_default = 1;
        $address->save();
        return response()->json([
            'result' => true,
            'message' => 'Default shipping information has been updated'
        ]);
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
}
