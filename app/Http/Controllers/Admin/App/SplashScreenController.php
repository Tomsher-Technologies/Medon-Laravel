<?php

namespace App\Http\Controllers\Admin\App;

use App\Http\Controllers\Controller;
use App\Models\App\SplashScreens;
use Illuminate\Http\Request;

class SplashScreenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $screens = SplashScreens::all();
        return view('backend.splash_screen.index', compact('screens'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.splash_screen.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        SplashScreens::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'sort_order' => $request->sort_order,
            'status' => $request->status,
        ]);

        flash(translate('Splash screen created successfully'))->success();
        return redirect()->route('splash_screen.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\App\SplashScreens  $splashScreens
     * @return \Illuminate\Http\Response
     */
    public function show(SplashScreens $splashScreens)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\App\SplashScreens  $splashScreens
     * @return \Illuminate\Http\Response
     */
    public function edit(SplashScreens $splash_screen)
    {
        return view('backend.splash_screen.edit', compact('splash_screen'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\App\SplashScreens  $splashScreens
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SplashScreens $splash_screen)
    {
        $splash_screen->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'sort_order' => $request->sort_order,
            'status' => $request->status,
        ]);

        flash(translate('Screen updated successfully'))->success();
        return redirect()->route('splash_screen.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\App\SplashScreens  $splashScreens
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        SplashScreens::destroy($id);
        flash(translate('Screen deleted successfully'))->success();
        return redirect()->route('splash_screen.index');
    }

    public function updateStatus(Request $request)
    {
        $slider = SplashScreens::findOrFail($request->id);
        $slider->status = $request->status;
        if ($slider->save()) {
            return 1;
        }
        return 0;
    }
}
