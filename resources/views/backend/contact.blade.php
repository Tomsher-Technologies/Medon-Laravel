@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Contact Enquiries')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">{{ translate('Contact Enquiries') }}</h5>
        </div>

    </div>
    <div class="card-body">
       
        <table class="table aiz-table mb-0 ">
            <thead>
                <tr>
                    <th scope="col" class="text-center">Sl No:</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col" style="width:40%;">Message</th>
                    <th scope="col" class="text-center">Date</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($contact[0]))
                    @foreach ($contact as $key=>$con)
                        <tr>
                            <td scope="row" class="text-center">{{ $key + 1 + ($contact->currentPage() - 1) * $contact->perPage() }}</td>
                            <td>{{ $con->name }}</td>
                            <td>{{ $con->email }}</td>
                            <td>{{ $con->phone_number }}</td>
                            <td>{{ $con->message }}</td>
                            <td class="text-center">{{ date('d M,Y', strtotime($con->created_at)) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">No data found </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $contact->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
