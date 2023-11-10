<?php

namespace App\Http\Controllers;

use App\Models\RequestQuote;
use Illuminate\Http\Request;

class RequestQuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rfqs = RequestQuote::latest()->paginate(15);
        return view('backend.rfq.index', compact('rfqs'));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestQuote  $requestQuote
     * @return \Illuminate\Http\Response
     */
    public function show(RequestQuote $rfq)
    {
        return view('backend.rfq.show', compact('rfq'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RequestQuote  $requestQuote
     * @return \Illuminate\Http\Response
     */
    public function edit(RequestQuote $requestQuote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestQuote  $requestQuote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequestQuote $requestQuote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RequestQuote  $requestQuote
     * @return \Illuminate\Http\Response
     */
    public function destroy(RequestQuote $requestQuote)
    {
        //
    }
}
