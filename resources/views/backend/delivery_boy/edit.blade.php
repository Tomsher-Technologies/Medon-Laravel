@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Delivery Boy Information') }}</h5>
                </div>

                <form action="{{ route('delivery_boy.update', $delivery_boy->id) }}" method="POST">
                    <input name="_method" type="hidden" value="PATCH">
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
                                        <option @if($shop->id == old('shop_id',$delivery_boy->shop_id)) {{ 'selected' }} @endif value="{{$shop->id}}">{{ $shop->name }}</option>
                                    @endforeach
                                </select>
                                @error('shop_id')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="name">{{ translate('Name') }}</label>
                            <div class="col-sm-9">
                                <input type="text" placeholder="{{ translate('Name') }}" id="name" name="name"
                                    value="{{ old('name', $delivery_boy->name) }}" class="form-control" >
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="email">{{ translate('Email') }}</label>
                            <div class="col-sm-9">
                                <input type="text" placeholder="{{ translate('Email') }}" id="email" name="email"
                                    value="{{ old('email', $delivery_boy->email) }}" class="form-control" >
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="phone">{{ translate('Phone') }}</label>
                            <div class="col-sm-9">
                                <input type="text" placeholder="{{ translate('Phone') }}" id="phone" name="phone"
                                    value="{{ old('phone', $delivery_boy->phone) }}" class="form-control" >
                                @error('phone')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="password">{{ translate('Password') }}</label>
                            <div class="col-sm-9">
                                <input type="password" placeholder="{{ translate('Password') }}" id="password"
                                    name="password" class="form-control" autocomplete="new-password">
                                @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Active Status') }}</label>
                            <div class="col-md-9">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="status" value="1" @if ($delivery_boy->banned == 0) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                            <a href="{{route('delivery_boy.index')}}"  class="btn btn-sm btn-warning">{{translate('Cancel')}}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
