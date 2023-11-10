<?php

namespace App\Http\Controllers;

use App\Models\Frontend\Careers;
use App\Http\Requests\StoreCareersRequest;
use App\Http\Requests\UpdateCareersRequest;

class CareersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $careers = Careers::latest()->paginate(15);
        return view('backend.careers.index', compact('careers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCareersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCareersRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Frontend\Careers  $careers
     * @return \Illuminate\Http\Response
     */
    public function show(Careers $career)
    {
        return view('backend.careers.show', compact('career'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Frontend\Careers  $careers
     * @return \Illuminate\Http\Response
     */
    public function edit(Careers $careers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCareersRequest  $request
     * @param  \App\Models\Frontend\Careers  $careers
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCareersRequest $request, Careers $careers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Frontend\Careers  $careers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Careers $careers)
    {
        //
    }
}
