@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="aiz-titlebar text-left mt-2 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="h3">{{ translate('All Splash Screen') }}</h1>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <a href="{{ route('splash_screen.create') }}" class="btn btn-primary">
                            <span>{{ translate('Add New Screen') }}</span>
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
                                    <th data-breakpoints="lg">Sort Order</th>
                                    <th data-breakpoints="lg">Status</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($screens as $key => $screen)
                                    <tr>
                                        <td>
                                            {{ $screen->name }}
                                        </td>
                                        <td>
                                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                                @if ($screen->image)
                                                    <div class="col-auto">
                                                        <img src="{{ uploaded_asset($screen->image) }}" alt="Image"
                                                            class="size-50px img-fit">
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {{ $screen->sort_order }}
                                        </td>

                                        <td>
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input onchange="update_published(this)" value="{{ $screen->id }}"
                                                    type="checkbox" {{ $screen->status == 1 ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td class="text-right">
                                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                                href="{{ route('splash_screen.edit', $screen) }}" title="Edit">
                                                <i class="las la-edit"></i>
                                            </a>
                                            <a href="#"
                                                class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                                data-href="{{ route('splash-screen.delete', $screen->id) }}" title="Delete">
                                                <i class="las la-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script>
        function update_published(el) {

            var status = 0

            if (el.checked) {
                status = 1;
            }

            $.post('{{ route('splash-screen.update-status') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', 'Splash Screen updated successfully');
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
