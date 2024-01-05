@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">Available Delivery Agents</h5>
        </div>
<a href="{{ Session::has('last_url') ? Session::get('last_url') : route('all_orders.index') }}" >Go Back</a>
    </div>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Delivery Agent Name</th>
                    <th >Delivery Agent Phone</th>
                    <th >Distance</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($locs as $key => $loc)
                <tr>
                    <td>
                        {{ ($key+1)  }}
                    </td>
                    
                    <td>
                        {{ $loc->user->name ?? '' }}
                    </td>
                    <td>
                        {{ $loc->user->phone ?? '' }}
                    </td>
                    <td>
                        <span class="badge badge-inline badge-success">{{ $loc->distance }} KM</span>
                    </td>
                   
                    <td class="text-center">
                        <button class="btn btn-sm btn-success d-innline-block adminApprove" data-id="{{$loc->id}}" data-status="1">{{translate('Assign Delivery')}}</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
    
    </script>
@endsection
