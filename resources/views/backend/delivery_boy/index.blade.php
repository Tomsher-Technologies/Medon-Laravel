@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">All Delivery Boys</h1>
            </div>
            <div class="col-md-6 text-md-right">
                <a href="{{ route('delivery_boy.create') }}" class="btn btn-circle btn-info">
                    <span>{{ translate('Add New Delivery Boys') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">Delivery Boys</h5>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg" width="10%">#</th>
                        <th>Name</th>
                        <th data-breakpoints="lg">Email</th>
                        <th data-breakpoints="lg">Phone</th>
                        <th width="10%">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $key => $staff)
                        <tr>
                            <td>{{ $key + 1 + ($users->currentPage() - 1) * $users->perPage() }}</td>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>{{ $staff->phone }}</td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                    href="{{ route('delivery_boy.edit', encrypt($staff->id)) }}" title="Edit">
                                    <i class="las la-edit"></i>
                                </a>

                                <form style="display: inline-block" onsubmit="return confirm('Are you sure you want to delete this?')" action="{{ route('delivery_boy.destroy', $staff->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-soft-danger btn-icon btn-circle btn-sm"
                                        title="Delete" type="submit">
                                        <i class="las la-trash"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $users->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection