@extends('backend.layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">Order Details</h1>
            <a class="btn btn-primary" href="{{ Session::has('last_url') ? Session::get('last_url') : route('all_orders.index') }}" >Go Back</a>
        </div>
        <div class="card-body">
            <div class="row gutters-5">
                <div class="col text-center text-md-left">
                </div>
                @php
                    $delivery_status = $order->delivery_status;
                    $payment_status = $order->payment_status;
                @endphp

                <div class="col-md-3 ml-auto">
                    <label for="update_payment_status">Payment Status</label>
                    <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                        id="update_payment_status">
                        <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>Unpaid
                        </option>
                        <option value="paid" @if ($payment_status == 'paid') selected @endif>Paid
                        </option>
                    </select>
                </div>
                <div class="col-md-3 ml-auto">
                    <label for="update_delivery_status">Delivery Status</label>
                    @if ($delivery_status != 'delivered' && $delivery_status != 'cancelled')
                        <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                            id="update_delivery_status">
                            <option value="pending" @if ($delivery_status == 'pending') selected @endif>
                                Pending</option>
                            <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>
                                Confirmed</option>
                            <option value="partial_pick_up" @if ($delivery_status == 'partial_pick_up') selected @endif>
                                Partial Pick Up</option>
                            <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>
                                Picked Up</option>
                            <option value="partial_delivery" @if ($delivery_status == 'partial_delivery') selected  @endif disabled>
                                Partial Delivery</option>
                            <option value="delivered" @if ($delivery_status == 'delivered') selected @endif disabled>
                                Delivered</option>
                            <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>
                                Cancel</option>
                        </select>
                    @else
                        <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                    @endif
                </div>
                <div class="col-md-3 ml-auto d-none">
                    <label for="update_tracking_code">Tracking Code (optional)</label>
                    <input type="text" class="form-control" id="update_tracking_code"
                        value="{{ $order->tracking_code }}">
                </div>
            </div>
            <div class="mb-3">
                {!! QrCode::size(100)->generate($order->code) !!}
            </div>
            <div class="row gutters-5">
                <div class="col-sm-12 col-md-6 text-md-left">
                    <address>
                        <strong class="text-main">{{ json_decode($order->shipping_address)->name }}</strong><br>
                        {{ json_decode($order->shipping_address)->email }}<br>
                        {{ json_decode($order->shipping_address)->phone }}<br>
                        {{ json_decode($order->shipping_address)->address }},
                        {{ json_decode($order->shipping_address)->city }}
                        {{ json_decode($order->shipping_address)->state }}
                        <br>
                        {{ json_decode($order->shipping_address)->country }}
                    </address>
                    <p><b>Order Notes : </b> {{$order->order_notes ?? ''}}</p>
                    @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                        <br>
                        <strong class="text-main">Payment Information</strong><br>
                        Name: {{ json_decode($order->manual_payment_data)->name }},
                        Amount: {{ single_price(json_decode($order->manual_payment_data)->amount) }},
                        TRX ID: {{ json_decode($order->manual_payment_data)->trx_id }}
                        <br>
                        <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}"
                            target="_blank"><img
                                src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" alt=""
                                height="100"></a>
                    @endif
                </div>
                <div class="col-sm-12 col-md-6 float-right">
                    <table class="float-right">
                        <tbody>
                            <tr>
                                <td class="text-main text-bold">Order #</td>
                                <td class="text-right text-info text-bold"> {{ $order->code }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">Order Status</td>
                                <td class="text-right">
                                    @if ($delivery_status == 'delivered')
                                        <span
                                            class="badge badge-inline badge-success">{{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                    @else
                                        <span
                                            class="badge badge-inline badge-info">{{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">Order Date </td>
                                <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">
                                    Total amount
                                </td>
                                <td class="text-right">
                                    {{ single_price($order->grand_total) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">Payment method</td>
                                <td class="text-right">
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</td>
                            </tr>
                            @if ($order->payment_type == 'card' || $order->payment_type == 'card_wallet')
                                <tr>
                                    <td class="text-main text-bold">Payment Tracking Id</td>
                                    <td class="text-right">
                                        {{ $order->payment_tracking_id }}
                                    </td>
                                </tr>
                            @endif
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="new-section-sm bord-no">
            <ul class="status_indicator">
                <li class="status completed" style="float:left">Delivered</li>
                <li class="status picked_up ml-2" style="float:left">Picked Up</li>
            </ul>
            <br>
            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table table-bordered aiz-table invoice-summary">
                        <thead>
                            <tr class="bg-trans-dark">
                                <th>
                                    <div class="form-group">
                                        <div class="aiz-checkbox-inline">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" class="check-all">
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>
                                    </div>
                                </th>
                                <th data-breakpoints="lg" class="min-col">#</th>
                                <th width="10%">Photo</th>
                                <th class="text-uppercase">Description</th>
                                {{-- <th data-breakpoints="lg" class="text-uppercase">Delivery Type</th> --}}
                                <th data-breakpoints="lg" class="min-col text-center text-uppercase">Qty
                                </th>
                                <th data-breakpoints="lg" class="min-col text-center text-uppercase">
                                    Price</th>
                                <th data-breakpoints="lg" class="min-col text-center text-uppercase">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                @php
                                    $statusColor = '#fff';
                                    if ($orderDetail->delivery_status == 'picked_up'){
                                        $statusColor = '#e9ae004f';
                                    }elseif ($orderDetail->delivery_status == 'delivered') {
                                        $statusColor = '#03ff0338';
                                    }
                                    
                                @endphp
                                <tr style="background:{{$statusColor}}">
                                    <td>
                                        @if ($orderDetail->delivery_status == 'pending' || $orderDetail->delivery_status == 'confirmed')
                                            <div class="form-group">
                                                <div class="aiz-checkbox-inline">
                                                    <label class="aiz-checkbox">
                                                        <input type="checkbox" class="check-one" name="id[]" value="{{$orderDetail->id}}">
                                                        <span class="aiz-square-check"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="javascript:void(0)"
                                                target="_blank"><img height="50"
                                                    src="{{ asset($orderDetail->product->thumbnail_img) }}"></a>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="javascript:void(0)"
                                                target="_blank"><img height="50"
                                                    src="{{ asset($orderDetail->product->thumbnail_img) }}"></a>
                                        @else
                                            <strong>N/A</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <strong><a href="javascript:void(0)"
                                                    target="_blank"
                                                    class="text-muted">{{ $orderDetail->product->name }}</a></strong>
                                            <small>{{ $orderDetail->variation }}</small>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <strong><a href="javascript:void(0)"
                                                    target="_blank"
                                                    class="text-muted">{{ $orderDetail->product->name }}</a></strong>
                                        @else
                                            <strong>Product Unavailable</strong>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $orderDetail->quantity }}</td>
                                    <td class="text-center">
                                        @if ($orderDetail->og_price != $orderDetail->offer_price)
                                            <del>{{ single_price($orderDetail->og_price) }}</del> <br>
                                        @endif
                                        {{ single_price($orderDetail->price / $orderDetail->quantity) }}
                                    </td>
                                    <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="clearfix float-right">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>
                                <strong class="text-muted">Sub Total :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('price')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Tax :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('tax')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Shipping :</strong>
                            </td>
                            <td>
                                {{ single_price($order->shipping_cost) }}
                            </td>
                        </tr>
                        @if ($order->coupon_discount)
                            <tr>
                                <td>
                                    <strong class="text-muted">Coupon Discount :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->coupon_discount) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">Coupon Code :</strong>
                                </td>
                                <td>
                                    {{ $order->coupon_code }}
                                </td>
                            </tr>
                        @endif
                        {{-- @if ($order->offer_discount)
                            <tr>
                                <td>
                                    <strong class="text-muted">Offer Discount :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->offer_discount) }}
                                </td>
                            </tr>
                        @endif --}}
                        
                        <tr>
                            <td>
                                <strong class="text-muted">TOTAL :</strong>
                            </td>
                            <td class="text-muted h5">
                                {{ single_price($order->grand_total) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-right no-print">
                    <a href="{{ route('invoice.download', $order->id) }}" type="button"
                        class="btn btn-icon btn-light"><i class="las la-download"></i></a>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">Order Delivery Details</h1>
        </div>
        <div class="card-body">
            @php
                $delivery = getOrderDeliveryDetails($order->id);
                // echo '<pre>';
                // print_r($delivery);
                // die;
            @endphp
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered aiz-table invoice-summary">
                    <thead>
                        <tr class="bg-trans-dark">
                            <th data-breakpoints="lg" class="min-col">#</th>
                            <th width="20%">Delivery Boy</th>
                            <th class="text-uppercase  text-center">Assigned Date</th>
                            <th class="text-uppercase  text-center">delivery Status</th>
                            <th class="min-col text-center text-uppercase">payment status</th>
                            <th width="25%" class="min-col text-left text-uppercase">
                                delivery note</th>
                            <th class="min-col text-center text-uppercase">
                                delivery image</th>
                            <th class="min-col text-center text-uppercase">
                                    delivery date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $delivery as $delAs)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>
                                   {{ ucwords($delAs['deliveryBoy']['name'] ?? '') }}
                                </td>
                                <td class="text-center">
                                    {{ ($delAs['created_at'] != null) ? date('d-M-Y H:i a', strtotime($delAs['created_at'])) : ''  }}
                                </td>    
                                <td class="text-capitalize text-center">
                                    {{ ($delAs['status'] == 1) ? 'Delivered' : 'Pending'}}
                                </td>
                                <td class="text-capitalize text-center">
                                    {{ ($delAs['payment_status'] == 1) ? 'Paid' : ''}}
                                </td>
                                <td class="text-left">
                                    {{ $delAs['delivery_note'] ?? ''}}
                                </td>
                                <td class="text-center">
                                    @if (!empty($delAs['delivery_image']))
                                        <a href="{{ asset($delAs['delivery_image']) }}" target="_blank"><img src="{{ asset($delAs['delivery_image']) }}" width="150px" alt="Order Image"/></a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ ($delAs['delivery_date'] != null) ? date('d-M-Y H:i a', strtotime($delAs['delivery_date'])) : ''  }}
                                </td>             
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
@endsection

@section('styles')
    <style>
    .status_indicator {
        margin: 0px 0px 20px;
        padding: 0;
        list-style: none;
    }
    .status {
        &.completed:before {
            background-color: #03ff0338;
            border-color: #78D965;
            box-shadow: 0px 0px 4px 1px #94E185;
        }

        &.picked_up:before {
            background-color: #e9ae004f;
            border-color: #FFB161;
            box-shadow: 0px 0px 4px 1px #FFC182;
        }
        &:before {
            content: ' ';
            display: inline-block;
            width: 25px;
            height: 12px;
            margin-right: 10px;
            border: 1px solid #000;
        }
    }
    </style>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        $('#update_delivery_status').on('change', function() {
            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            let product_ids = []; 
            $('.check-one:checkbox').each(function() {
                if(this.checked) {
                    product_ids.push($(this).val()); 
                }
            });  

            if((status == 'partial_pick_up' || status == 'partial_delivery') && product_ids.length === 0){
                AIZ.plugins.notify('warning', 'Please select products');
                if(status == 'partial_pick_up'){
                    $('#update_delivery_status').val('confirmed').selectpicker('refresh');
                }
                if(status == 'partial_delivery'){
                    $('#update_delivery_status').val('partial_pick_up').selectpicker('refresh');
                }
                return false;
            }
            
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                status: status,
                product_ids: product_ids
            }, function(data) {
                AIZ.plugins.notify('success', 'Delivery status has been updated');
            });
        });

        $('#update_payment_status').on('change', function() {
            var order_id = {{ $order->id }};
            var status = $('#update_payment_status').val();
            $.post('{{ route('orders.update_payment_status') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                status: status
            }, function(data) {
                AIZ.plugins.notify('success', 'Payment status has been updated');
            });
        });

        $('#update_tracking_code').on('change', function() {
            var order_id = {{ $order->id }};
            var tracking_code = $('#update_tracking_code').val();
            $.post('{{ route('orders.update_tracking_code') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                tracking_code: tracking_code
            }, function(data) {
                AIZ.plugins.notify('success', 'Order tracking code has been updated');
            });
        });
    </script>
@endsection
