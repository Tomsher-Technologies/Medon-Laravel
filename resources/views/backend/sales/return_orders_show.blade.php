@extends('backend.layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">Order Details</h1>
            <a class="btn btn-primary" href="{{ Session::has('last_url') ? Session::get('last_url') : route('return_requests.index') }}" >Go Back</a>
        </div>
        <div class="card-body">
            <div class="row gutters-5">
                <div class="col text-center text-md-left">
                </div>
                @php
                    $delivery_status = $order->order->delivery_status;
                    $payment_status = $order->order->payment_status;
                @endphp
            </div>
            <div class="mb-3">
                {!! QrCode::size(100)->generate($order->order->code) !!}
            </div>
            <div class="row gutters-5">
                <div class="col-sm-12 col-md-6 text-md-left">
                    <address>
                        <strong class="text-main">{{ json_decode($order->order->shipping_address)->name }}</strong><br>
                        {{ json_decode($order->order->shipping_address)->email }}<br>
                        {{ json_decode($order->order->shipping_address)->phone }}<br>
                        {{ json_decode($order->order->shipping_address)->address }},
                        {{ json_decode($order->order->shipping_address)->city }}
                        {{ json_decode($order->order->shipping_address)->state }}
                        <br>
                        {{ json_decode($order->order->shipping_address)->country }}
                    </address>
                    <p><b>Order Notes : </b> {{$order->order_notes ?? ''}}</p>
                </div>
                <div class="col-sm-12 col-md-6 float-right">
                    <table class="float-right">
                        <tbody>
                            <tr>
                                <td class="text-main text-bold">Order #</td>
                                <td class="text-right text-info text-bold"> {{ $order->order->code }}</td>
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
                                <td class="text-right">{{ date('d-m-Y h:i A', $order->order->date) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">
                                    Total amount
                                </td>
                                <td class="text-right">
                                    {{ single_price($order->order->grand_total) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">Payment method</td>
                                <td class="text-right">
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->order->payment_type))) }}</td>
                            </tr>
                            @if ($order->order->payment_type == 'card' || $order->order->payment_type == 'card_wallet')
                                <tr>
                                    <td class="text-main text-bold">Payment Tracking Id</td>
                                    <td class="text-right">
                                        {{ $order->order->payment_tracking_id }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="new-section-sm bord-no">
            <ul class="status_indicator">
                {{-- <li class="status completed" style="float:left">Delivered</li> --}}
                <li class="status picked_up ml-2" style="float:left">Returned</li>
            </ul>
            <br>
            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table table-bordered aiz-table invoice-summary">
                        <thead>
                            <tr class="bg-trans-dark">
                                <th class="min-col">#</th>
                                <th width="10%">Photo</th>
                                <th class="text-uppercase">Description</th>
                                {{-- <th data-breakpoints="lg" class="text-uppercase">Delivery Type</th> --}}
                                <th class="min-col text-center text-uppercase">Qty
                                </th>
                                <th class="min-col text-center text-uppercase">
                                    Price</th>
                                <th class="min-col text-center text-uppercase">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->order->orderDetails as $key => $orderDetail)
                                @php
                                    $statusColor = '#fff';
                                    if ($order->product_id == $orderDetail->product_id){
                                        $statusColor = '#e9ae004f';
                                    }
                                    
                                @endphp
                                <tr style="background:{{$statusColor}}">
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($orderDetail->product != null)
                                            <img height="50" src="{{ get_product_image($orderDetail->product->thumbnail_img, '300') }}">
                                        @else
                                            <strong>N/A</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null)
                                            <strong class="text-muted">{{ $orderDetail->product->name }}</strong>
                                            {{-- <small> --}}
                                               
                                            {{-- </small> --}}
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
                                {{ single_price($order->order->orderDetails->sum('price')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Tax :</strong>
                            </td>
                            <td>
                                {{ single_price($order->order->orderDetails->sum('tax')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Shipping :</strong>
                            </td>
                            <td>
                                {{ single_price($order->order->shipping_cost) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Coupon :</strong>
                            </td>
                            <td>
                                {{ single_price($order->order->coupon_discount) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Offer Discount :</strong>
                            </td>
                            <td>
                                {{ single_price($order->offer_discount) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">TOTAL :</strong>
                            </td>
                            <td class="text-muted h5">
                                {{ single_price($order->order->grand_total) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-right no-print">
                    <a href="{{ route('invoice.download', $order->order->id) }}" type="button"
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
                $delivery = getOrderDeliveryDetails($order->order->id);
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

    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">Order Return Delivery Details</h1>
        </div>
        <div class="card-body">
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered aiz-table invoice-summary">
                    <thead>
                        <tr class="bg-trans-dark">
                            <th data-breakpoints="lg" class="min-col">#</th>
                            <th width="20%">Delivery Boy</th>
                            <th class="text-uppercase  text-center">Assigned Date</th>
                            <th class="text-uppercase  text-center">delivery Status</th>
                            <th width="25%" class="min-col text-left text-uppercase">
                                delivery note</th>
                            <th class="min-col text-center text-uppercase">
                                delivery image</th>
                            <th class="min-col text-center text-uppercase">
                                    delivery date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                {{ ucwords($order->deliveryBoy->name ?? '') }}
                            </td>
                            <td class="text-center">
                                {{ ($order->delivery_assigned_date != null) ? date('d-M-Y H:i a', strtotime($order->delivery_assigned_date)) : ''  }}
                            </td>    
                            <td class="text-capitalize text-center">
                                {{ ($order->delivery_status == 1) ? 'Delivered' : 'Pending'}}
                            </td>
                            
                            <td class="text-left">
                                {{ $order->delivery_note ?? ''}}
                            </td>
                            <td class="text-center">
                                @if (!empty($order->delivery_image))
                                    <a href="{{ asset($order->delivery_image) }}" target="_blank"><img src="{{ asset($order->delivery_image) }}" width="150px" alt="Order Image"/></a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="text-center">
                                {{ ($order->delivery_completed_date != null) ? date('d-M-Y H:i a', strtotime($order->delivery_completed_date)) : ''  }}
                            </td>             
                        </tr>
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
      
    </script>
@endsection
