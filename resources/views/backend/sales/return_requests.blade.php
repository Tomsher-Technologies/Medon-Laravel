@extends('backend.layouts.app')

@section('content')

<div class="card">
    
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">All Return Requests</h5>
            </div>
        </div>

        @php
            $shops = getActiveShops();
        @endphp
        
        <div class="card-body">
            
                <form class="" action="" id="sort_orders" method="GET">
                    <div class="row">
                        
                        <div class="col-lg-3 mt-2">
                            <div class="form-group mb-2">
                                <label>Order Code</label>
                                <input type="text" class="form-control" id="search" name="search"@isset($search) value="{{ $search }}" @endisset placeholder="Type Order code & hit Enter">
                            </div>
                        </div>

                        @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                            @php 
                                $shopAgents = getShopDeliveryAgents(Auth::user()->shop_id);
                            @endphp
                            <div class="col-lg-3 mt-2">
                                <div class="form-group mb-2">
                                    <label>Delivery Boy</label>
                                    <select id="agent_search" name="agent_search" class="form-control" >
                                        <option value="">Select delivery boy</option>
                                        @foreach ($shopAgents as $agent)
                                            <option {{ ($agent_search == $agent['id']) ? 'selected' : '' }} value="{{$agent['id']}}">{{ $agent['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 mt-2">
                                <div class="form-group mb-2">
                                    <label>Delivery Approval Status</label>
                                    <select id="da_search" name="da_search" class="form-control" >
                                        <option {{ ($da_search == '') ? 'selected' : '' }} value="">Select status</option>
                                        <option {{ ($da_search == '0') ? 'selected' : '' }} value="10">Pending</option>
                                        <option {{ ($da_search == '1') ? 'selected' : '' }} value="1">Approved</option>
                                        <option {{ ($da_search == '2') ? 'selected' : '' }} value="2">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="col-lg-3 mt-2">
                                <div class="form-group mb-2">
                                    <label>Request Date</label>
                                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="Filter by request date" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-lg-3 mt-2">
                                <div class="form-group mb-2">
                                    <label>Request Approval Status</label>
                                    <select id="ra_search" name="ra_search" class="form-control" >
                                        <option {{ ($ra_search == '') ? 'selected' : '' }} value="">Select status</option>
                                        <option {{ ($ra_search == '0') ? 'selected' : '' }} value="10">Pending</option>
                                        <option {{ ($ra_search == '1') ? 'selected' : '' }} value="1">Approved</option>
                                        <option {{ ($ra_search == '2') ? 'selected' : '' }} value="2">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 mt-2">
                                <div class="form-group mb-2">
                                    <label>Assigned Shop</label>
                                    <select id="shop_search" name="shop_search" class="form-control" >
                                        <option value="">Select Shop</option>
                                        @foreach ($shops as $shop)
                                            <option {{ ($shop_search == $shop->id) ? 'selected' : '' }} value="{{$shop->id}}">{{ $shop->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 mt-2">
                                <div class="form-group mb-2">
                                    <label>Delivery Approval Status</label>
                                    <select id="da_search" name="da_search" class="form-control" >
                                        <option {{ ($da_search == '') ? 'selected' : '' }} value="">Select status</option>
                                        <option {{ ($da_search == '0') ? 'selected' : '' }} value="10">Pending</option>
                                        <option {{ ($da_search == '1') ? 'selected' : '' }} value="1">Approved</option>
                                        <option {{ ($da_search == '2') ? 'selected' : '' }} value="2">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 mt-2">
                                <div class="form-group mb-2">
                                    <label>Refund Type</label>
                                    <select id="refund_search" name="refund_search" class="form-control" >
                                        <option {{ ($refund_search == '') ? 'selected' : '' }} value="">Select type</option>
                                        <option {{ ($refund_search == 'wallet') ? 'selected' : '' }} value="wallet">Wallet</option>
                                        <option {{ ($refund_search == 'cash') ? 'selected' : '' }} value="cash">Cash</option>
                                    </select>
                                </div>
                            </div>

                        @endif

                        <div class="col-auto mt-4">
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            <hr>

            <table class="table aiz-table mb-2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Code</th>
                        @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                            
                        @else
                            <th class="w-10">Order Shop</th>
                        @endif
                        <th data-breakpoints="xl">Request Date</th>
                        <th data-breakpoints="xl">Customer</th>
                        <th data-breakpoints="xl">Product</th>
                        <th data-breakpoints="xl">Reason</th>
                        <th data-breakpoints="xl">Price</th>
                        <th data-breakpoints="xl">Quantity</th>
                        <th data-breakpoints="xl">Refund Amount</th>

                        @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                            <th class="text-center">Delivery Boy</th>
                            <th class="text-center">Delivery Date</th>
                        @else
                            <th class="text-center">Request Approval</th>
                            <th class="text-center">Assigned Shop</th>
                        @endif

                        <th class="text-center">Delivery Approval</th>
                        @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')

                        @else
                            <th class="text-center">Refund Type</th>
                        @endif
                        <th class="text-center">Order Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $key => $order)
                        
                        <tr>
                            <td>
                                {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                            </td>
                            
                            <td>
                                {{ $order->order->code ?? '' }}
                            </td>
                            @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                            
                            @else
                                <td>
                                    {{ $order->order->shop->name ?? '' }}
                                </td>
                            @endif

                            <td>
                                {{ ($order->created_at) ? date('d-m-Y H:i a', strtotime($order->created_at)) : ''}}
                            </td>

                            <td>
                                {{ $order->user->name }}
                            </td>
                            <td>
                                {{ $order->product->name }}
                            </td>
                            <td>
                                {{ $order->reason }}
                            </td>
                            <td>
                                {{ $order->offer_price }}
                            </td>
                            <td>
                                {{ $order->quantity }}
                            </td>
                            
                            <td>
                                {{ $order->refund_amount }}
                            </td>

                            @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                                <td class="text-center">
                                    @if ($order->delivery_status == 0)
                                        <a href="{{route('return-delivery', encrypt($order->id))}}" class="btn btn-sm btn-success">Find Nearest Agent</a><br>
                                        @if ($order->delivery_boy != NULL)
                                            <b class="">{{ $order->deliveryBoy->name ?? '' }}</b>
                                        @endif
                                    @else
                                        <b>{{ $order->deliveryBoy->name ?? '' }}</b>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{ ($order->delivery_completed_date != NULL) ? date('d-m-Y H:i a',strtotime($order->delivery_completed_date)) : '' }}
                                </td>
                            @else
                                <td class="text-center">
                                    @if($order->admin_approval == 0)
                                        <button class="btn btn-sm btn-success d-innline-block adminApprove" data-id="{{$order->id}}" data-status="1" data-type="admin">{{translate('Approve')}}</button>
                                        <button class="btn btn-sm btn-warning d-innline-block adminApprove" data-id="{{$order->id}}" data-status="2" data-type="admin">{{translate('Reject')}}</button>
                                    @else
                                        @if($order->admin_approval == 1)
                                            <span class=" badge-soft-success">Approved</span>
                                        @elseif($order->admin_approval == 2)
                                            <span class=" badge-soft-danger">Rejected</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($order->admin_approval == 1)
                                        @php
                                            if($order->shop_id != null){
                                                $color = 'border:2px solid #09c309';
                                            }else {
                                                $color = 'border:2px solid red';
                                            }
                                        @endphp
                                        <select id="shop_id{{$key}}" name="shop_id{{$key}}" class="form-control selectShop" data-refund="{{$order->id}}" style="{{$color}}">
                                            <option value="">Select Shop</option>
                                            @foreach ($shops as $shop)
                                                <option @if($shop->id == old('shop_id',$order->shop_id)) {{ 'selected' }} @endif value="{{$shop->id}}">{{ $shop->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            @endif
                            
                            @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                                <td class="text-center">
                                    @if($order->delivery_approval == 0 && $order->delivery_status == 1)
                                        <button class="btn btn-sm btn-success d-innline-block deliveryApprove" data-id="{{$order->id}}" data-status="1" data-type="delivery">{{translate('Approve')}}</button>
                                        <button class="btn btn-sm btn-warning d-innline-block deliveryApprove" data-id="{{$order->id}}" data-status="2" data-type="delivery">{{translate('Reject')}}</button>
                                    @else
                                        @if($order->delivery_approval == 1)
                                            <span class=" badge-soft-success">Approved</span>
                                        @elseif($order->delivery_approval == 2)
                                            <span class=" badge-soft-danger">Rejected</span>
                                        @endif
                                    @endif
                                </td>
                            @else
                                <td class="table-action text-center">
                                    @if($order->delivery_approval == 1)
                                    <span class=" badge-soft-success">Approved</span>
                                    @elseif($order->delivery_approval == 2)
                                        <span class=" badge-soft-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($order->delivery_approval == 1 && $order->refund_type == NULL)
                                        <button class="btn btn-sm btn-success d-innline-block adminPaymentType" data-id="{{$order->id}}" data-type="wallet">{{translate('Wallet')}}</button>
                                        <button class="btn btn-sm btn-warning d-innline-block adminPaymentType" data-id="{{$order->id}}" data-type="cash">{{translate('Cash')}}</button>
                                    @elseif ($order->refund_type != NULL)
                                        {{ ucfirst($order->refund_type) }}
                                    @endif
                                </td>
                            @endif

                            
                    
                            <td class="text-center">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('return_orders.show', encrypt($order->id))}}" title="View">
                                    <i class="las la-eye"></i>
                                </a>
                            
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $orders->appends(request()->input())->links() }}
            </div>

        </div>
    
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        $(document).on("click", ".adminApprove", function(e) {
            var status = $(this).attr('data-status');
            var id = $(this).attr('data-id');
            var type = $(this).attr('data-type');
            var msg = (status == '1') ? "Do you want to approve this request?" : "Do you want to reject this request?";
            e.preventDefault();
            if (confirm(msg)) {
                $.ajax({
                    url: "{{ route('return-request-status') }}",
                    type: "POST",
                    data: {
                        id: id,
                        status:status,
                        type:type,
                        _token: '{{ @csrf_token() }}',
                    },
                    dataType: "html",
                    success: function() {
                        window.location.reload();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert("Error deleting! Please try again");
                    }
                });
            }
        });

        $(document).on("click", ".deliveryApprove", function(e) {
            var status = $(this).attr('data-status');
            var id = $(this).attr('data-id');
            var type = $(this).attr('data-type');
            var msg = (status == '1') ? "Do you want to approve this request?" : "Do you want to reject this request?";
            e.preventDefault();
            if (confirm(msg)) {
                $.ajax({
                    url: "{{ route('return-request-status') }}",
                    type: "POST",
                    data: {
                        id: id,
                        status:status,
                        type:type,
                        _token: '{{ @csrf_token() }}',
                    },
                    dataType: "html",
                    success: function() {
                        window.location.reload();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert("Error deleting! Please try again");
                    }
                });
            }
        });

        $(document).on('change','.selectShop',function(){
            
            var shop_id = $(this).val();
            var refund_id = $(this).attr('data-refund');
            
            swal({
                title: "Are you sure?",
                text: "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('assign-shop-refund') }}",
                        type: "POST",
                        data: {
                            refund_id: refund_id,
                            shop_id : $(this).val(),
                            _token: '{{ @csrf_token() }}',
                        },
                        dataType: "html",
                        success: function(response) {
                            swal("Successfully updated!", {
                                    icon: "success",
                                });
                            
                            window.location.reload();
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            swal("Something went wrong!", {
                                icon: "warning",
                            });
                        }
                    });
                }else{
                    $(this).val('');
                }
            });
        });

        $(document).on("click", ".adminPaymentType", function(e) {
            var type = $(this).attr('data-type');
            var id = $(this).attr('data-id');
            
            e.preventDefault();
            swal({
                title: "Are you sure?",
                text: "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('return-payment-type') }}",
                        type: "POST",
                        data: {
                            id: id,
                            type:type,
                            _token: '{{ @csrf_token() }}',
                        },
                        dataType: "html",
                        success: function() {
                            swal("Successfully updated!", {
                                    icon: "success",
                                });
                            
                            window.location.reload();
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            alert("Error deleting! Please try again");
                        }
                    });
                }
            });
         
        });

//        function change_status() {
//            var data = new FormData($('#order_form')[0]);
//            $.ajax({
//                headers: {
//                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                },
//                url: "{{route('bulk-order-status')}}",
//                type: 'POST',
//                data: data,
//                cache: false,
//                contentType: false,
//                processData: false,
//                success: function (response) {
//                    if(response == 1) {
//                        location.reload();
//                    }
//                }
//            });
//        }

    </script>
@endsection
