@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="aiz-titlebar text-left mt-2 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="h3">All Offers</h1>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <a href="{{ route('offers.create') }}" class="btn btn-primary">
                            <span>{{ translate('Add New offers') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <form class="" id="sort_customers" action="" method="GET">

                    <div class="card-body">
                        <table class="table aiz-table mb-0">
                            <thead>
                                <tr>
                                    <th data-breakpoints="lg">Name</th>
                                    <th data-breakpoints="lg">Image</th>
                                    <th data-breakpoints="lg">Offer Type</th>
                                    <th data-breakpoints="lg">Start Date</th>
                                    <th data-breakpoints="lg">End Date</th>
                                    <th data-breakpoints="lg">Status</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($offers as $key => $offer)
                                    <tr>
                                        <td>
                                            {{ $offer->name }}
                                        </td>
                                        <td>
                                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                                @if ($offer->image)
                                                    <div class="col-auto">
                                                        <img src="{{ uploaded_asset($offer->image) }}" alt="Image"
                                                            class="size-50px img-fit">
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-capitalize">{{ str_replace('_', ' ', $offer->offer_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="text-capitalize">{{ $offer->start_date ? $offer->start_date->format('d/m/Y') : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="text-capitalize">{{ $offer->end_date ? $offer->end_date->format('d/m/Y') : '-' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if ($offer->status)
                                                <span
                                                    class="badge badge-inline badge-success text-capitalize">Enabled</span>
                                            @else
                                                <span
                                                    class="badge badge-inline badge-danger text-capitalize">Disabled</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                                href="{{ route('banners.edit', $offer) }}" title="Edit">
                                                <i class="las la-edit"></i>
                                            </a>
                                            <a href="#"
                                                class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                                data-href="{{ route('banners.destroy', $offer) }}" title="Delete">
                                                <i class="las la-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="aiz-pagination">
                            {{ $offers->appends(request()->input())->links() }}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
