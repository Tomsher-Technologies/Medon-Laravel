<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Role;
use App\Models\User;
use Hash;
use Validator;
use DB;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        if ($request->has('search')) {
            $sort_search = $request->search;
        }
     
        $query = Staff::with(['user'])->select("*");

        if($sort_search){ 
            $query->whereHas('user', function ($query) use ($sort_search){
                $query->where('name', 'LIKE', "%$sort_search%")
                ->orWhere('phone', 'LIKE', "%$sort_search%")
                ->orWhere('email', 'LIKE', "%$sort_search%");
                $query->orwhereHas('shop', function ($query) use ($sort_search){
                    $query->where('name', 'LIKE', "%$sort_search%");
                });    
            });        
            $query->orwhereHas('role', function ($query) use ($sort_search){
                $query->where('name', 'LIKE', "%$sort_search%");
            });  
        }
                        
        $query->orderBy('id','DESC');

        $staffs = $query->paginate(20);
        return view('backend.staff.staffs.index', compact('staffs','sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all();
        return view('backend.staff.staffs.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required|min:6',
            'phone' => 'required|integer',
            'email' => 'required|email|unique:users',
            'role_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (User::where('email', $request->email)->first() == null) {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->user_type = "staff";
            $user->shop_id = $request->shop_id;
            $user->password = Hash::make($request->password);
            if ($user->save()) {
                $staff = new Staff;
                $staff->user_id = $user->id;
                $staff->role_id = $request->role_id;
                if ($staff->save()) {
                    flash(translate('Staff has been inserted successfully'))->success();
                    return redirect()->route('staffs.index');
                }
            }
        }

        flash(translate('Email already used'))->error();
        return back();
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
        $staff = Staff::findOrFail(decrypt($id));
        $roles = Role::all();
        return view('backend.staff.staffs.edit', compact('staff', 'roles'));
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
        $staff = Staff::findOrFail($id);
        $user = $staff->user;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'nullable|min:6',
            'phone' => 'required|integer',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->shop_id = $request->shop_id;
        $user->banned = ($request->has('status')) ? 0 : 1;
        if (strlen($request->password) > 0) {
            $user->password = Hash::make($request->password);
        }
        if ($user->save()) {
            $staff->role_id = $request->role_id;
            if ($staff->save()) {
                flash(translate('Staff has been updated successfully'))->success();
                return redirect()->route('staffs.index');
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
        User::destroy(Staff::findOrFail($id)->user->id);
        if (Staff::destroy($id)) {
            flash(translate('Staff has been deleted successfully'))->success();
            return redirect()->route('staffs.index');
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }
}
