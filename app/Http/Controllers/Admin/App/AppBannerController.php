<?php

namespace App\Http\Controllers\Admin\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppBannerRequest;
use App\Http\Requests\UpdateAppBannerRequest;
use App\Models\App\AppBanner;
use Cache;
use Illuminate\Http\Request;

class AppBannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sliders = AppBanner::paginate(15);
        return view('backend.app_banners.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.app_banners.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAppBannerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAppBannerRequest $request)
    {
        $slider = AppBanner::create([
            'name' => $request->name,
            'image' => $request->banner,
            'link_type' => $request->link_type,
            'link_ref' => $request->link_type,
            'link_ref_id' => $request->link_ref_id,
            'link' => $request->link,
            'sort_order' => $request->sort_order,
            'status' => $request->status,
        ]);

        flash(translate('App Banner created successfully'))->success();
        return redirect()->route('app-banner.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Frontend\AppBanner  $AppBanner
     * @return \Illuminate\Http\Response
     */
    public function show(AppBanner $AppBanner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Frontend\AppBanner  $AppBanner
     * @return \Illuminate\Http\Response
     */
    public function edit(AppBanner $AppBanner)
    {
        return view('backend.app_banners.edit', compact('AppBanner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAppBannerRequest  $request
     * @param  \App\Models\Frontend\AppBanner  $AppBanner
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAppBannerRequest $request, AppBanner $AppBanner)
    {
        $AppBanner->update([
            'name' => $request->name,
            'image' => $request->banner,
            'link_type' => $request->link_type,
            'link_ref' => $request->link_type,
            'link_ref_id' => $request->link_ref_id,
            'link' => $request->link,
            'sort_order' => $request->sort_order,
            'status' => $request->status,
        ]);


        flash(translate('Slider updated successfully'))->success();
        return redirect()->route('app-banner.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Frontend\AppBanner  $AppBanner
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        AppBanner::destroy($id);
        flash(translate('Slider deleted successfully'))->success();
        return redirect()->route('app-banner.index');
    }

    public function updateStatus(Request $request)
    {
        $slider = AppBanner::findOrFail($request->id);
        $slider->status = $request->status;
        if ($slider->save()) {
            return 1;
        }
        return 0;
    }
}
