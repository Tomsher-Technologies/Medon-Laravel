@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h3">{{ translate('All Quotes') }}</h1>
            </div>
        </div>
    </div>
    <br>

    <div class="card">
        <div class="card-body">
            <table class="table mb-0 aiz-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th data-breakpoints="lg">Email</th>
                        <th data-breakpoints="lg">Phone Number</th>
                        <th class="text-right">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rfqs as $key => $rfq)
                        <tr>
                            <td>
                                {{ $key + 1 + ($rfqs->currentPage() - 1) * $rfqs->perPage() }}
                            </td>
                            <td>
                                {{ $rfq->name }}
                            </td>
                            <td>
                                {{ $rfq->email }}
                            </td>
                            <td>
                                {{ $rfq->phone_number }}
                            </td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                    href="{{ route('rfq.show', $rfq) }}" title="Show">
                                    <i class="las la-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $rfqs->links() }}
            </div>
        </div>
    </div>
@endsection
