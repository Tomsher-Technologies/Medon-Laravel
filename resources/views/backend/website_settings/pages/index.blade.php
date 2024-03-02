@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col">
			<h1 class="h3">Website Pages</h1>
		</div>
	</div>
</div>

<div class="card">
	<div class="card-header">
		<h6 class="mb-0 fw-600">All Pages</h6>
		{{-- <a href="{{ route('custom-pages.create') }}" class="btn btn-primary">Add New Page</a> --}}
	</div>
	<div class="card-body">
		<table class="table aiz-table mb-0">
        <thead>
            <tr>
                <th >#</th>
                <th>{{translate('Name')}}</th>
                {{-- <th data-breakpoints="md">{{translate('URL')}}</th> --}}
                <th class="text-right">{{translate('Actions')}}</th>
            </tr>
        </thead>
        <tbody>
			@php
				$pages = \App\Models\Page::orderBy('slug', 'ASC')->get();
			@endphp
        	@foreach ($pages as $key => $page)
        	<tr>
        		<td>{{ $key+1 }}</td>
        		
				<td>{{ $page->slug }}</td>
				
        		<td class="text-right">
					<a href="{{route('custom-pages.edit', ['id'=>$page->type] )}}" class="btn btn-icon btn-circle btn-sm btn-soft-primary" title="Edit">
						<i class="las la-pen"></i>
					</a>

				{{-- 					
					@if($page->type == 'custom_page')
          				<a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('custom-pages.destroy', $page->id)}} " title="Delete">
          					<i class="las la-trash"></i>
          				</a>
					@endif --}}
        		</td>
        	</tr>
        	@endforeach
        </tbody>
    </table>
	</div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
