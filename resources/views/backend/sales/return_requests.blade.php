@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">All Return Requests</h5>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="Filter by date" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="Type Order code & hit Enter">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Code</th>
                        <th class="w-10">Order Shop</th>
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
                        @php
                            $shops = getActiveShops();
                        @endphp
                        <tr>
                            <td>
                                {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                            </td>
                            
                            <td>
                                {{ $order->order->code ?? '' }}
                            </td>
                            <td>
                                {{ $order->order->shop->name ?? '' }}
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
                                    <a href="{{route('return-delivery', encrypt($order->id))}}" class="btn btn-sm btn-success">Find Nearest Agent</a>
                                </td>

                                <td class="text-center">
                                    2024-01-02
                                </td>
                            @else
                                <td class="text-center">
                                    @if($order->admin_approval == 0)
                                        <button class="btn btn-sm btn-success d-innline-block adminApprove" data-id="{{$order->id}}" data-status="1">{{translate('Approve')}}</button>
                                        <button class="btn btn-sm btn-warning d-innline-block adminApprove" data-id="{{$order->id}}" data-status="2">{{translate('Reject')}}</button>
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
                                    @if($order->admin_approval == 0)
                                        <button class="btn btn-sm btn-success d-innline-block adminApprove" data-id="{{$order->id}}" data-status="1">{{translate('Approve')}}</button>
                                        <button class="btn btn-sm btn-warning d-innline-block adminApprove" data-id="{{$order->id}}" data-status="2">{{translate('Reject')}}</button>
                                    @else
                                        @if($order->admin_approval == 1)
                                            <span class=" badge-soft-success">Approved</span>
                                        @elseif($order->admin_approval == 2)
                                            <span class=" badge-soft-danger">Rejected</span>
                                        @endif
                                    @endif
                                </td>
                            @else
                                <td class="table-action text-right">
                                    @if($order->delivery_approval == 1)
                                    <span class=" badge-soft-success">Approved</span>
                                    @elseif($order->delivery_approval == 2)
                                        <span class=" badge-soft-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($order->delivery_approval == 1)
                                        <button class="btn btn-sm btn-success d-innline-block adminPaymentType" data-id="{{$order->id}}" data-type="wallet">{{translate('Wallet')}}</button>
                                        <button class="btn btn-sm btn-warning d-innline-block adminPaymentType" data-id="{{$order->id}}" data-type="cash">{{translate('Cash')}}</button>
                                    @endif
                                </td>
                            @endif

                            
                    
                            <td class="text-center">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('all_orders.show', encrypt($order->order_id))}}" title="View">
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
    </form>
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
            var msg = (status == '1') ? "Do you want to approve this request?" : "Do you want to reject this request?";
            e.preventDefault();
            if (confirm(msg)) {
                $.ajax({
                    url: "{{ route('return-request-status') }}",
                    type: "POST",
                    data: {
                        id: id,
                        status:status,
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
            if (confirm("Are you sure?")) {
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
                        window.location.reload();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert("Error deleting! Please try again");
                    }
                });
            }
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
