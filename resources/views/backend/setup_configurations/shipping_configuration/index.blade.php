@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-6 d-none">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Select Shipping Method') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('shipping_configuration.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="shipping_type">
                        {{-- <div class="radio mar-btm">
                        <input id="product-shipping" class="magic-radio" type="radio" name="shipping_type" value="product_wise_shipping" <?php if (get_setting('shipping_type') == 'product_wise_shipping') {
                            echo 'checked';
                        } ?>>
                        <label for="product-shipping">
                            <span>{{translate('Product Wise Shipping Cost')}}</span>
                            <span></span>
                        </label>
                    </div> --}}
                        <div class="radio mar-btm">
                            <input id="flat-shipping" class="magic-radio" type="radio" name="shipping_type"
                                value="flat_rate" <?php if (get_setting('shipping_type') == 'flat_rate') {
                                    echo 'checked';
                                } ?>>
                            <label for="flat-shipping">{{ translate('Flat Rate Shipping Cost') }}</label>
                        </div>
                        {{-- <div class="radio mar-btm">
                        <input id="seller-shipping" class="magic-radio" type="radio" name="shipping_type" value="seller_wise_shipping" <?php if (get_setting('shipping_type') == 'seller_wise_shipping') {
                            echo 'checked';
                        } ?>>
                        <label for="seller-shipping">{{translate('Seller Wise Flat Shipping Cost')}}</label>
                    </div> --}}
                        <div class="radio mar-btm">
                            <input id="area-shipping" class="magic-radio" type="radio" name="shipping_type"
                                value="area_wise_shipping" <?php if (get_setting('shipping_type') == 'area_wise_shipping') {
                                    echo 'checked';
                                } ?>>
                            <label for="area-shipping">{{ translate('Country Wise Flat Shipping Cost') }}</label>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 d-none">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Note') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        {{-- <li class="list-group-item">
                        1. Product Wise Shipping Cost calulation: Shipping cost is calculate by addition of each product shipping cost.
                    </li> --}}
                        <li class="list-group-item">
                            2. Flat Rate Shipping Cost calulation: How many products a customer purchase, doesn\'t matter.
                            Shipping cost is fixed.
                        </li>
                        {{-- <li class="list-group-item">
                        3. Seller Wise Flat Shipping Cost calulation: Fixed rate for each seller. If customers purchase 2 product from two seller shipping cost is calculated by addition of each seller flat shipping cost.
                    </li> --}}
                        <li class="list-group-item">
                            4. Area Wise Flat Shipping Cost calulation: Fixed rate for each area. If customers purchase
                            multiple products from one seller shipping cost is calculated by the customer shipping area. To
                            configure area wise shipping cost go to <a href="{{ route('cities.index') }}">Shipping
                                Cities</a>.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row d-none">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Flat Rate Cost') }}</h5>
                </div>
                <form action="{{ route('shipping_configuration.update') }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        @csrf
                        <input type="hidden" name="type" value="flat_rate_shipping_cost">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control" type="text" name="flat_rate_shipping_cost"
                                    value="{{ get_setting('flat_rate_shipping_cost') }}">
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Note') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            1. Flat rate shipping cost is applicable if Flat rate shipping is enabled.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row d-none">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Pickup from store') }}</h5>
                </div>
                <form action="{{ route('shipping_configuration.update') }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        @csrf
                        <input type="hidden" name="type" value="pickup_from_store">
                        <div class="form-group row">
                            <label class="col-md-8 col-from-label">
                                Free shipping status
                            </label>
                            <div class="col-md-4">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input name="pickup_from_store"
                                        {{ get_setting('pickup_from_store') == 'on' ? 'checked' : '' }} type="checkbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Note') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            1. Flat rate shipping cost is applicable if Flat rate shipping is enabled.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">Free Shipping Settings</h5>
                </div>
                <form action="{{ route('shipping_configuration.free_shipping') }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        @csrf
                        <input type="hidden" name="type" value="free_shipping">

                        <div class="form-group row">
                            <label class="col-md-4 col-from-label">
                                Default shipping amount
                            </label>
                            <div class="col-md-8">
                                <input step="0.01" class="form-control" type="number"
                                    name="default_shipping_amount"
                                    value="{{ get_setting('default_shipping_amount') ?? 0 }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-from-label">
                                Free shipping status
                            </label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input name="free_shipping_status"
                                        {{ get_setting('free_shipping_status') == '1' ? 'checked' : '' }} type="checkbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-from-label">
                                Free shipping min amount
                            </label>
                            <div class="col-md-8">
                                <input step="0.01" class="form-control" type="number"
                                    name="free_shipping_min_amount"
                                    value="{{ get_setting('free_shipping_min_amount') ?? 0 }}">
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">Order Return Time Limit</h5>
                </div>
                <form action="{{ route('configuration.return_settings') }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        @csrf
                        <input type="hidden" name="type" value="return_product_limit">

                        <div class="form-group row">
                            <label class="col-md-4 col-from-label">
                                Return Time Limit (Days)
                            </label>
                            <div class="col-md-8">
                                <input step="1" class="form-control" type="number"
                                    name="default_return_time"
                                    value="{{ get_setting('default_return_time') ?? 0 }}">
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Shipping Cost for Admin Products')}}</h5>
            </div>
            <form action="{{ route('shipping_configuration.update') }}" method="POST" enctype="multipart/form-data">
              <div class="card-body">
                  @csrf
                  <input type="hidden" name="type" value="shipping_cost_admin">
                  <div class="form-group">
                      <div class="col-lg-12">
                          <input class="form-control" type="text" name="shipping_cost_admin" value="{{ get_setting('shipping_cost_admin') }}">
                      </div>
                  </div>
                  <div class="form-group mb-0 text-right">
                      <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                  </div>
              </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Note')}}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        1. Shipping cost for admin is applicable if Seller wise shipping cost is enabled.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div> --}}
@endsection
