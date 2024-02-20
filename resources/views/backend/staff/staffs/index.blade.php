@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Staffs')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('staffs.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Staffs')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <form class="" id="sort_sellers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Staffs') }}</h5>
            </div>

            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search"
                        name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset
                        placeholder="{{ translate('Type search word & Enter') }}">
                </div>
            </div>
        </div>
    </form>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th  width="10%">#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Shop Name')}}</th>
                    <th >{{translate('Email')}}</th>
                    <th >{{translate('Phone')}}</th>
                    <th >{{translate('Role')}}</th>
                    <th >{{ translate('Status') }}</th>
                    <th class="text-center" width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffs as $key => $staff)
                    <tr>
                        <td>{{ ($key+1) + ($staffs->currentPage() - 1)*$staffs->perPage() }}</td>
                        <td>{{$staff->user?->name}}</td>
                        <td>{{$staff->user?->shop?->name}}</td>
                        <td>{{$staff->user?->email}}</td>
                        <td>{{$staff->user?->phone}}</td>
                        <td>
                            @if ($staff->role != null)
                                {{ $staff->role->name }}
                            @endif
                        </td>
                        <td>
                            @if ($staff->user?->banned == 0)
                                <span class="badge badge-soft-success" style="width:40px;">Active </span>
                            @elseif ($staff->user?->banned == 1)
                                <span class="badge badge-soft-danger w-40" style="width:50px;">Inactive </span>
                            @endif
                        </td>
                        <td class="text-center">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('staffs.edit', encrypt($staff->id))}}" title="Edit">
                                    <i class="las la-edit"></i>
                                </a>
                                {{-- <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('staffs.destroy', $staff->id)}}" title="Delete">
                                    <i class="las la-trash"></i>
                                </a> --}}
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $staffs->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
