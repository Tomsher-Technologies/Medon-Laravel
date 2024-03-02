@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">

        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">All Brands</h1>
            </div>
            <div class="col-md-6 text-md-right">
                <a href="{{ route('brands.create') }}" class="btn btn-primary">
                    <span>Add new brand</span>
                </a>
            </div>
        </div>
    </div>

    

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">Brands</h5>
                    </div>
                    <div class="col-md-4">
                        <form class="" id="sort_brands" action="" method="GET">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="search"
                                    name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                    placeholder="Type name & Enter">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Logo</th>
                                <th class="text-center">Is Active</th>
                                <th class="text-right">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brands as $key => $brand)
                                <tr>
                                    <td>{{ $key + 1 + ($brands->currentPage() - 1) * $brands->perPage() }}</td>
                                    <td>{{ $brand->name }}</td>
                                    <td>
                                        <img src="{{ api_upload_asset($brand->logo) }}" alt="Brand"
                                            class="h-50px">
                                    </td>
                                    <td class="text-center">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" onchange="update_status(this)" value="{{ $brand->id }}"
                                                <?php if ($brand->is_active == 1) {
                                                    echo 'checked';
                                                } ?>>
                                            <span></span>
                                        </label>
                                    </td>
                                    <td class="text-right">
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                            href="{{ route('brands.edit', $brand) }}" title="Edit">
                                            <i class="las la-edit"></i>
                                        </a>
                                        {{-- <a href="#"
                                            class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                            data-href="{{ route('brands.destroy', $brand->id) }}"
                                            title="Delete">
                                            <i class="las la-trash"></i>
                                        </a> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination">
                        {{ $brands->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        function sort_brands(el) {
            $('#sort_brands').submit();
        }
        function update_status(el) {
            if (el.checked) {
                var status = 1;
            } else {
                var status = 0;
            }
            $.post('{{ route('brands.status') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', 'Brand status updated successfully');
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
