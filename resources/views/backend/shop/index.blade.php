@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Shops')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('admin.shops.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Shop')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <form class="" id="sort_sellers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Shops') }}</h5>
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
                    <th data-breakpoints="lg">#</th>
                    <th>{{ translate('Branch Name') }}</th>
                    <th>{{ translate('Address') }}</th>
                    <th data-breakpoints="lg">{{ translate('Phone') }}</th>
                    <th data-breakpoints="lg">{{ translate('Email') }}</th>
                    <th data-breakpoints="lg">{{ translate('Working Hours') }}</th>
                    <th data-breakpoints="lg">{{ translate('Status') }}</th>
                    <th class="text-center" width="10%">{{ translate('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shops as $key => $shop)
                    <tr>
                        <td>{{ ($key+1) + ($shops->currentPage() - 1)*$shops->perPage() }}</td>
                        <td>
                            {{ $shop->name }}
                        </td>
                        <td>
                            {{ $shop->address }}
                        </td>
                        <td>{{ $shop->phone }}</td>
                        <td>{{ $shop->email }}</td>
                        <td>{{ $shop->working_hours }}</td>
                        <td>
                            @if ($shop->status == 1)
                                <span class="badge badge-soft-success" style="width:40px;">Active </span>
                            @else
                                <span class="badge badge-soft-danger w-40" style="width:50px;">Inactive </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.shops.edit', $shop) }}"
                                class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $shops->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
