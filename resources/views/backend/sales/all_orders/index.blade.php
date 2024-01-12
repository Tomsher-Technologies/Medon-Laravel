@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">All Orders</h5>
            </div>

            {{-- <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete()"> {{translate('Delete selection')}}</a>
    <!--                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">
                        <i class="las la-sync-alt"></i>
                        {{translate('Change Order Status')}}
                    </a>-->
                </div>
            </div> --}}

            <!-- Change Status Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{translate('Choose an order status')}}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <select class="form-control aiz-selectpicker" onchange="change_status()" data-minimum-results-for-search="Infinity" id="update_delivery_status">
                                <option value="pending">{{translate('Pending')}}</option>
                                <option value="confirmed">{{translate('Confirmed')}}</option>
                                <option value="picked_up">{{translate('Picked Up')}}</option>
                                <option value="on_the_way">{{translate('On The Way')}}</option>
                                <option value="delivered">{{translate('Delivered')}}</option>
                                <option value="cancelled">{{translate('Cancel')}}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="delivery_status" id="delivery_status">
                    <option value="">{{translate('Filter by Delivery Status')}}</option>
                    <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{translate('Pending')}}</option>
                    <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>{{translate('Confirmed')}}</option>
                    <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{translate('Picked Up')}}</option>
                    <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>{{translate('On The Way')}}</option>
                    <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{translate('Delivered')}}</option>
                    <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>{{translate('Cancel')}}</option>
                </select>
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
            <ul class="status_indicator">
                <li class="status completed" style="float:left">Delivered</li>
                <li class="status picked_up ml-2" style="float:left">Partial Delivery</li>
                <li class="status cancel_requested ml-2" style="float:left">Cancel Requested</li>
                <li class="status cancelled ml-2" style="float:left">Cancelled</li>
            </ul>
            <br>
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        {{-- <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th> --}}
                        <th>Order Code</th>
                        <th  class="text-center" data-breakpoints="md">No. of Products</th>
                        <th data-breakpoints="md">Customer</th>
                        <th data-breakpoints="md">Amount</th>
                        <th data-breakpoints="md" class="text-center">Delivery Status</th>
                        <th data-breakpoints="md"  class="text-center">Payment Status</th>
                        @if (addon_is_activated('refund_request'))
                        <th>Refund</th>
                        @endif

                        @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                            <th class="text-center" data-breakpoints="lg" width="20%">
                                {{translate('Assign Delivery Boy')}}
                            </th>
                        @else
                            <th class="text-center" data-breakpoints="lg" width="25%">
                                {{translate('Assign Store')}}
                            </th>
                        @endif
                        
                        <th class="text-center">{{translate('options')}}</th>
                    </tr>
                </thead>
                <tbody id="order-table">
                    @php
                        $shops = getActiveShops();
                    @endphp
                    @foreach ($orders as $key => $order)
                        @php
                            $statusColor = '#fff';
                            if ($order->delivery_status == 'partial_delivery'){
                                $statusColor = '#e9ae004f';
                            }elseif ($order->delivery_status == 'delivered') {
                                $statusColor = '#03ff0338';
                            }elseif ($order->delivery_status == 'cancelled') {
                                $statusColor = '#fd79798a';
                            }elseif ($order->cancel_request == 1 && $order->cancel_approval == 0) {
                                $statusColor = '#ffbebe30';
                            }
                            
                        @endphp
                    <tr style="background:{{$statusColor}}">
                        <td>
                            {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                        </td>
                        {{-- <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$order->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td> --}}
                        <td>
                            {{ $order->code }}
                        </td>
                        <td class="text-center">
                            {{ count($order->orderDetails) }}
                        </td>
                        <td>
                            @if ($order->user != null)
                            {{ $order->user->name }}
                            @else
                            Guest ({{ $order->guest_id }})
                            @endif
                        </td>
                        <td>
                            {{ single_price($order->grand_total) }}
                        </td>
                        <td class="text-center">
                            @php
                                $status = $order->delivery_status;
                                if($order->delivery_status == 'cancelled') {
                                    $status = '<span class="badge badge-inline badge-danger">'.translate('Cancel').'</span>';
                                }

                            @endphp
                            {!! ucfirst(str_replace('_', ' ', $status)) !!}
                        </td>
                        <td class="text-center">
                            @if ($order->payment_status == 'paid')
                            <span class="badge badge-inline badge-success">{{translate('Paid')}}</span>
                            @else
                            <span class="badge badge-inline badge-danger">{{translate('Unpaid')}}</span>
                            @endif
                        </td>
                        @if (addon_is_activated('refund_request'))
                        <td>
                            @if (count($order->refund_requests) > 0)
                            {{ count($order->refund_requests) }} Refund
                            @else
                            No Refund
                            @endif
                        </td>
                        @endif
                        @if (Auth::user()->shop_id != NULL && Auth::user()->user_type == 'staff')
                            <td class="text-center">
                                @if (!in_array($status,['pending','picked_up','delivered','cancelled']) && ($order->cancel_request == 1 && $order->cancel_approval == 2))
                                    <a href="{{route('delivery-agents', encrypt($order->id))}}" class="btn btn-sm btn-success">Find Nearest Agent</a>
                                @endif

                                @if (in_array($status,['partial_pick_up','picked_up','confirmed']))
                                    @php
                                        $assignedTo = getAssignedDeliveryBoy($order->id);
                                    @endphp
                                    @if ($assignedTo != '')
                                        <br>Assigned to <b> {{ $assignedTo }} </b>
                                    @endif
                                @elseif ($status == 'delivered')
                                    @php
                                        $deliveredBy = getDeliveryBoy($order->id);
                                    @endphp
                                    @if (count($deliveredBy) > 0)
                                        <br>Delivered by 
                                        @foreach ($deliveredBy as $k => $dby)
                                            @if ($k != 0)
                                                ,
                                            @endif
                                            <b> {{ $dby->deliveryBoy->name ?? '' }} </b>
                                        @endforeach
                                        
                                    @endif
                                @endif
                            </td>
                        @else
                            <td class="myInputGroupSelect">
                                @php
                                    if($order->shop_id != null){
                                        $color = 'border:2px solid #09c309';
                                    }else {
                                        $color = 'border:2px solid red';
                                    }
                                @endphp
                                @if($order->cancel_request == 0 || ($order->cancel_request == 1 && $order->cancel_approval == 2))
                                    <select id="shop_id{{$key}}" name="shop_id{{$key}}" class="form-control selectShop" data-order="{{$order->id}}" style="{{$color}}">
                                        <option value="">Select Shop</option>
                                        @foreach ($shops as $shop)
                                            <option @if($shop->id == old('shop_id',$order->shop_id)) {{ 'selected' }} @endif value="{{$shop->id}}">{{ $shop->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                        @endif

                        <td class="text-center">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('all_orders.show', encrypt($order->id))}}" title="View">
                                <i class="las la-eye"></i>
                            </a>
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}" title="Download Invoice">
                                <i class="las la-download"></i>
                            </a>
                            {{-- <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $order->id)}}" title="Delete">
                                <i class="las la-trash"></i>
                            </a> --}}
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

        &.cancelled:before {
            background-color: #e756568a;
            border-color: #e51e1e8a;
            box-shadow: 0px 0px 4px 1px #a61d1d8a;
        }

        &.cancel_requested:before {
            background-color: #ffbebe7a;
            border-color: #e147477a;
            box-shadow: 0px 0px 4px 1px #ee64647a;
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">

        $(document).on('change','.selectShop',function(){
            
            var shop_id = $(this).val();
            var order_id = $(this).attr('data-order');

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
                        url: "{{ route('assign-shop-order') }}",
                        type: "POST",
                        data: {
                            order_id: order_id,
                            shop_id : $(this).val(),
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

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
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

        function bulk_delete() {
            var data = new FormData($('#sort_orders')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-order-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }

     
    </script>
@endsection
