@extends('backend.layouts.app')

@section('content')
    @php
        $ids = json_decode($offer->link_id);
    @endphp
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">Update Offers</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" method="POST" action="{{ route('offers.update', $offer->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Name<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                            <div class="col-md-9">
                                <input type="text" placeholder="Name" value="{{ old('name',$offer->name) }}" id="name"
                                    name="name" class="form-control" required>
                                    <input type="hidden" value="{{ $offer->id }}" id="offerId" name="offerId" >
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @livewire('slug-check', ['model' => 'App\\Models\\Offers', 'model_id' => $offer->id, 'template' => 2])
                        {{-- @error('slug')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror --}}

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">
                                Image<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span>
                            </label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            Browse
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">Choose File</div>
                                    <input value="{{ $offer->image }}" type="hidden" name="image" class="selected-files"
                                        required>
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                @error('image')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">
                                Mobile Image<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span>
                            </label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            Browse
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">Choose File</div>
                                    <input value="{{ $offer->mobile_image }}" type="hidden" name="mobile_image" class="selected-files"
                                        required>
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                @error('mobile_image')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Link Type<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                            <div class="col-md-9">
                                <select onchange="banner_form()" class="form-control aiz-selectpicker" name="link_type"
                                    id="link_type" data-live-search="true" required>
                                    <option {{ old('link_type',$offer->link_type) == 'product' ? 'selected' : '' }} value="product">Product
                                    </option>
                                    <option {{ old('link_type',$offer->link_type) == 'category' ? 'selected' : '' }} value="category">Category
                                    </option>
                                </select>
                                @error('link_type')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="banner_form">
                        </div>
                        @error('link')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        @error('link_ref_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror


                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Offer type<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" onchange="offer_form()" name="offer_type"
                                    id="offer_type" required>
                                    <option {{ old('offer_type',$offer->offer_type) == 'percentage' ? 'selected' : '' }} value="percentage">
                                        Percentage</option>
                                    <option {{ old('offer_type',$offer->offer_type) == 'amount_off' ? 'selected' : '' }} value="amount_off">
                                        Amount Off</option>
                                    <option {{ old('offer_type',$offer->offer_type) == 'buy_x_get_y' ? 'selected' : '' }}
                                        value="buy_x_get_y">
                                        Buy X get Y</option>
                                </select>
                                @error('offer_type')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row offer_type_item" style="display: none" id="percentage">
                            <label class="col-md-3 col-form-label">Offer Percentage<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                            <div class="col-md-9">
                                <input type="number" step=".01" placeholder="Offer Percentage"
                                    value="{{ old('percentage', $offer->percentage) }}" id="percentage" name="percentage" class="form-control">
                                @error('percentage')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row offer_type_item" style="display: none" id="amount">
                            <label class="col-md-3 col-form-label">Offer Amount<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                            <div class="col-md-9">
                                <input type="number" step=".01" placeholder="Offer Amount" value="{{ old('amount', $offer->offer_amount) }}"
                                    id="amount" name="amount" class="form-control">
                                @error('amount')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="offer_type_item" id="buy_x_get_y" style="display: none">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Buy Quantity<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                                <div class="col-md-9">
                                    <input type="number" min="1" step="1" placeholder="Buy Quantity"
                                        value="{{ old('buy_amount', $offer->buy_amount) }}" id="buy_amount" name="buy_amount"
                                        class="form-control">
                                    @error('buy_amount')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Get Quantity<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                                <div class="col-md-9">
                                    <input type="number" min="1" step="1" placeholder="Get Quantity"
                                        value="{{ old('get_amount', $offer->get_amount) }}" id="get_amount" name="get_amount"
                                        class="form-control">
                                    @error('get_amount')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    
                        @php
                            $start_date = date('d-m-Y H:i:s', strtotime($offer->start_date));
                            $end_date = date('d-m-Y H:i:s', strtotime($offer->end_date));
                        @endphp

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Offer Date<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control aiz-date-range" id="date_range"
                                    name="date_range" placeholder="Select Date" data-time-picker="true"
                                    data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" value="{{ $start_date . ' to ' . $end_date }}">
                                @error('date_range')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row d-none">
                            <label class="col-md-3 col-form-label">Status<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="status" id="status" required>
                                    <option {{ old('status',$offer->status) == '1' ? 'selected' : '' }} value="1">Enabled</option>
                                    <option {{ old('status',$offer->status) == '0' ? 'selected' : '' }} value="0">Disabled</option>
                                </select>
                                @error('status')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
@endsection
@section('script')
    @livewireScripts
    @livewireStyles
    <script type="text/javascript">
        $(document).ready(function() {
            banner_form();
            offer_form();
        });

        function offer_form() {
            var offer_type = $('#offer_type').val();
            $('.offer_type_item').hide();
            if (offer_type == 'percentage') {
                $('#percentage').show();
            } else if (offer_type == 'amount_off') {
                $('#amount').show();
            } else {
                $('#buy_x_get_y').show();
            }
        }

        function banner_form() {
            var link_type = $('#link_type').val();
            var link_error = "{{ $errors->getBag('default')->first('link') }}"
            var link_id_error = "{{ $errors->getBag('default')->first('link_ref_id') }}"
            var offerId = $('#offerId').val();
            $.post('{{ route('offers.get_form') }}', {
                _token: '{{ csrf_token() }}',
                link_type: link_type,
                offerId : offerId
            }, function(data) {
                $('#banner_form').html(data);
            });
        }

        function brands_form(){
            var main_category = $('#main_category').val();
            
            $.post('{{ route('offers.get_brands') }}', {
                _token: '{{ csrf_token() }}',
                main_category: main_category,
            }, function(data) {
                console.log(data);
                $('#link_ref_id').html(data);
                $('#link_ref_id').selectpicker('refresh');
            });
        }
    </script>
@endsection
