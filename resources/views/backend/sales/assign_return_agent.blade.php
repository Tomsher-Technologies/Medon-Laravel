@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">Available Delivery Agents</h5>
        </div>
        <button class="btn btn-warning" onclick="getAvailableDeliveryAgents({{$return_id}})">Refresh List</button>
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
        var return_id = '{{$return_id}}';
        getAvailableDeliveryAgents(return_id);

        function getAvailableDeliveryAgents(return_id){
            
            $.ajax({
                url: "{{ route('get-order-return-delivery-boys') }}",
                type: "GET",
                data: {
                    return_id: return_id,
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
            var return_id = $(this).attr('data-return_id');
            
            $.ajax({
                url: "{{ route('assign-return-delivery-boy') }}",
                type: "POST",
                data: {
                    return_id: return_id,
                    agent_id: agentId,
                    _token: '{{ @csrf_token() }}',
                },
                dataType: "html",
                success: function(response) {
                    if(response == 1){
                        AIZ.plugins.notify('success', 'Delivery agent has been assigned');
                    }else if(response == 0){
                        AIZ.plugins.notify('warning', 'Return already completed');
                    }else if(response == 2){
                        AIZ.plugins.notify('error', 'Return request not found');
                    }
                    getAvailableDeliveryAgents(return_id);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    AIZ.plugins.notify('error', 'Something went wrong');
                }
            });
        });
    </script>
@endsection
