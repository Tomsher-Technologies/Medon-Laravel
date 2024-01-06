@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">Available Delivery Agents</h5>
        </div>
        <button class="btn btn-warning" onclick="getAvailableDeliveryAgents({{$order_id}})">Refresh List</button>
        <a class="btn btn-primary" href="{{ Session::has('last_url') ? Session::get('last_url') : route('all_orders.index') }}" >Go Back</a>
    </div>

    <div class="card-body">
        <table class="table table-bordered fs-12 mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Delivery Agent Name</th>
                    <th >Delivery Agent Phone</th>
                    <th class="text-center">Distance</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="deliveryData">
               
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        var order_id = '{{$order_id}}';
        getAvailableDeliveryAgents(order_id);

        function getAvailableDeliveryAgents(order_id){
            
            $.ajax({
                url: "{{ route('get-order-delivery-boys') }}",
                type: "GET",
                data: {
                    order_id: order_id,
                    _token: '{{ @csrf_token() }}',
                },
                dataType: "html",
                success: function(response) {
                    // console.log(response);
                   $('#deliveryData').html(response);
                //    $('.aiz-table').trigger('footable_redraw');
                   $('.aiz-table').footable(); 
                },
                error: function(xhr, ajaxOptions, thrownError) {
                   
                }
            });
        }

        $(document).on('click','.assignDelivery', function(){
            var agentId = $(this).attr('data-agentid');
            var orderId = $(this).attr('data-orderid');
            alert(agentId);
            alert(orderId);
        });
    </script>
@endsection
