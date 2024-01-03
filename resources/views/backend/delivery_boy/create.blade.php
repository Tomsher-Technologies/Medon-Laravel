@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">Delivery Boy Information</h5>
            </div>

            <form class="form-horizontal" action="{{ route('delivery_boy.store') }}" method="POST" enctype="multipart/form-data">
            	@csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="shop_id">{{translate('Shop')}}</label>
                        <div class="col-sm-9">
                            <select id="shop_id" name="shop_id" class="form-control aiz-selectpicker" data-live-search="true" data-max-options="10" >
                                @php
                                    $shops = getActiveShops();
                                @endphp
                                <option value="">Select Shop</option>
                                @foreach ($shops as $shop)
                                    <option @if($shop->id == old('shop_id')) {{ 'selected' }} @endif value="{{$shop->id}}">{{ $shop->name }}</option>
                                @endforeach
                            </select>
                            @error('shop_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" value="{{ old('name') }}">
                            @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="email">{{translate('Email')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Email')}}" id="email" name="email" class="form-control" value="{{ old('email') }}">
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="phone">{{translate('Phone')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Phone')}}" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="password">{{translate('Password')}}</label>
                        <div class="col-sm-9">
                            <input type="password" placeholder="{{translate('Password')}}" autocomplete="new-password" id="password" name="password" class="form-control" >
                            @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        <a href="{{route('delivery_boy.index')}}"  class="btn btn-sm btn-warning">{{translate('Cancel')}}</a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>

@endsection
