<?php

namespace App\Http\Controllers\Admin\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Delivery\DeliveryBoy;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class DeliveryBoyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $users = User::where('user_type', 'delivery_boy')->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $users->where(function ($q) use ($sort_search) {
                $q->where('name', 'like', '%' . $sort_search . '%')->orWhere('email', 'like', '%' . $sort_search . '%');
            });
        }
        $users = $users->paginate(15);
        return view('backend.delivery_boy.index', compact('users', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.delivery_boy.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'email'         => 'required|unique:users|email',
            'phone'         => 'required|unique:users',
        ]);

        $user = User::create(array_merge([
            'user_type' => 'delivery_boy',
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password)
        ]));

        $delivery_boy = new DeliveryBoy();
        $delivery_boy->user_id = $user->id;
        $delivery_boy->save();

        if (isset($user->id)) {
            flash('Delivery boy has been created successfully')->success();
            return redirect()->route('delivery_boy.index');
        } else {
            flash('Something went wrong, please try again')->error();
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $delivery_boy = User::findOrFail(decrypt($id));
        return view('backend.delivery_boy.edit', compact('delivery_boy'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $delivery_boy = User::findOrFail($id);

        if ($delivery_boy->user_type == 'delivery_boy') {
            $delivery_boy->name = $request->name;
            $delivery_boy->email = $request->email;
            $delivery_boy->phone = $request->mobile;
            if (strlen($request->password) > 0) {
                $delivery_boy->password = Hash::make($request->password);
            }
            if ($delivery_boy->save()) {
                flash(translate('Delivery boy has been updated successfully'))->success();
                return redirect()->route('delivery_boy.index');
            }
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delivery_boy = User::findOrFail($id);
        if ($delivery_boy->user_type == 'delivery_boy') {
            $delivery_boy->delivery_boy()->delete();

            if ($delivery_boy->delete()) {
                flash(translate('Delivery boy has been deleted successfully'))->success();
                return redirect()->route('delivery_boy.index');
            }
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }
}
