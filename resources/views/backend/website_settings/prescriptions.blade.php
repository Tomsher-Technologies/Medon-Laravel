@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Prescriptions')}}</h1>
		</div>
		{{-- <div class="col-md-6 text-md-right">
			<a href="{{ route('roles.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Role')}}</span>
			</a>
		</div> --}}
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Prescriptions')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('Phone')}}</th>
                    <th width="20%">{{translate('Comment')}}</th>
                    <th class="text-center" width="10%">{{translate('Emirated ID Front')}}</th>
                    <th class="text-center" width="10%">{{translate('Emirated ID Back')}}</th>
                    <th class="text-center" width="10%">{{translate('Prescription')}}</th>
                    <th class="text-center" >{{translate('Date')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prescription as $key => $pre)
                    <tr>
                        <td>{{ ($key+1) + ($prescription->currentPage() - 1)*$prescription->perPage() }}</td>
                        <td>{{ $pre->user->name ??  $pre->name}} {!! ($pre->user) ? '<span class="badge badge-success" style="width:35px;">User</span>' : '<span class="badge badge-danger" style="width:40px;">Guest</span>' !!}</td>
                        <td>{{ $pre->user->email ??  $pre->email}}</td>
                        <td>{{ $pre->user->phone ??  $pre->phone}}</td>
                        <td>{{ $pre->comment}}</td>
                        <td class="text-center">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ asset($pre->user->eid_image_front ??  $pre->emirates_id_front)}}" target="_blank">
                                <i class="las la-file"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ asset($pre->user->eid_image_back ??  $pre->emirates_id_back)}}" target="_blank">
                                <i class="las la-file"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{ asset($pre->prescription)}}" target="_blank">
                                <i class="las la-file"></i>
                            </a>
                        </td>
                        <td>{{ date('d-m-Y H:i a', strtotime($pre->created_at)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $prescription->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
