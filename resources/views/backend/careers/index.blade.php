@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h3">{{ translate('All Resumes') }}</h1>
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
                        <th >Email</th>
                        <th >Phone Number</th>
                        <th >Qualification</th>
                        <th >Resume</th>
                        <th class="text-right">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($careers as $key => $career)
                        <tr>
                            <td>
                                {{ $key + 1 + ($careers->currentPage() - 1) * $careers->perPage() }}
                            </td>
                            <td>
                                {{ $career->name }}
                            </td>
                            <td>
                                {{ $career->email }}
                            </td>
                            <td>
                                {{ $career->phone_number }}
                            </td>
                            <td>
                                {{ $career->qualification }}
                            </td>
                            <td>
                                <a target="_new" download="" class="btn btn-soft-secondary btn-icon btn-circle btn-sm"
                                    href="{{ URL::to('storage/' . $career->resume) }}" title="Show">
                                    <i class="las la-download"></i>
                                </a>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                    href="{{ route('career.show', $career) }}" title="Show">
                                    <i class="las la-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $careers->links() }}
            </div>
        </div>
    </div>
@endsection
