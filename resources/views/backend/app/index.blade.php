@extends('backend.layouts.app')
@section('content')
    <div class="row">
        <div class="col-xl-10 mx-auto">
            <h4 class="fw-600">Home Page Settings</h4>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Categories</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input type="hidden" name="types[]" value="app_top_categories">
                                <select name="app_top_categories[]" class="form-control aiz-selectpicker" multiple
                                    data-live-search="true" data-max-options="10"
                                    data-selected="{{ get_setting('app_top_categories') }}">
                                    @foreach ($categories as $key => $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('categories.child_category', [
                                                'child_category' => $childCategory,
                                            ])
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Offers</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-12">
                                <h6 class="mb-2">Offer Section 1</h6>
                            </div>
                            <div class="col-md-12">
                                <input type="hidden" name="types[]" value="app_offer_section_1">
                                <select name="app_offer_section_1[]" class="form-control aiz-selectpicker" multiple
                                    data-live-search="true" data-max-options="10"
                                    data-selected="{{ get_setting('app_offer_section_1') }}">
                                    @foreach ($offers as $off)
                                        <option value="{{ $off->id }}">{{ $off->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <h6 class="mb-2">Offer Section 2</h6>
                            </div>  
                            <div class="col-md-12">
                                <input type="hidden" name="types[]" value="app_offer_section_2">
                                <select name="app_offer_section_2[]" class="form-control aiz-selectpicker" multiple
                                    data-live-search="true" data-max-options="10"
                                    data-selected="{{ get_setting('app_offer_section_2') }}">
                                    @foreach ($offers as $off)
                                        <option value="{{ $off->id }}">{{ $off->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Banner Section 1</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label>Categories</label> --}}
                            <div class="home-categories-target">
                                <input type="hidden" name="types[]" value="app_banner_1">
                                @if (get_setting('app_banner_1') != null)
                                    @foreach (json_decode(get_setting('app_banner_1'), true) as $key => $value)
                                        <div class="row item-counter gutters-5">
                                            <div class="col">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="app_banner_1[]"
                                                        data-live-search="true" data-selected={{ $value }} required>
                                                        @foreach ($banners as $banner)
                                                            <option value="{{ $banner->id }}">{{ $banner->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-max="3"
                                data-toggle="add-more"
                                data-content='<div class="row item-counter gutters-5">
								<div class="col">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="app_banner_1[]" data-live-search="true" required>
											@foreach ($banners as $key => $banner)
                                                <option value="{{ $banner->id }}">{{ $banner->name }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
								<div class="col-auto">
									<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
										<i class="las la-times"></i>
									</button>
								</div>
							</div>'
                                data-target=".home-categories-target">
                                Add New
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Banner Section 2</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label>Categories</label> --}}
                            <div class="home-categories-target-1">
                                <input type="hidden" name="types[]" value="app_banner_2">
                                @if (get_setting('app_banner_2') != null)
                                    @foreach (json_decode(get_setting('app_banner_2'), true) as $key => $value)
                                        <div class="row item-counter gutters-5">
                                            <div class="col">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="app_banner_2[]"
                                                        data-live-search="true" data-selected={{ $value }} required>
                                                        @foreach ($banners as $banner)
                                                            <option value="{{ $banner->id }}">{{ $banner->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-max="3"
                                data-toggle="add-more"
                                data-content='<div class="row item-counter gutters-5">
								<div class="col">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="app_banner_2[]" data-live-search="true" required>
											@foreach ($banners as $key => $banner)
                                                <option value="{{ $banner->id }}">{{ $banner->name }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
								<div class="col-auto">
									<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
										<i class="las la-times"></i>
									</button>
								</div>
							</div>'
                                data-target=".home-categories-target-1">
                                Add New
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Banner Section 3</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label>Categories</label> --}}
                            <div class="home-categories-target-2">
                                <input type="hidden" name="types[]" value="app_banner_3">
                                @if (get_setting('app_banner_3') != null)
                                    @foreach (json_decode(get_setting('app_banner_3'), true) as $key => $value)
                                        <div class="row item-counter gutters-5">
                                            <div class="col">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="app_banner_3[]"
                                                        data-live-search="true" data-selected={{ $value }} required>
                                                        @foreach ($banners as $banner)
                                                            <option value="{{ $banner->id }}">{{ $banner->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-max="3"
                                data-toggle="add-more"
                                data-content='<div class="row item-counter gutters-5">
								<div class="col">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="app_banner_3[]" data-live-search="true" required>
											@foreach ($banners as $key => $banner)
                                                <option value="{{ $banner->id }}">{{ $banner->name }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
								<div class="col-auto">
									<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
										<i class="las la-times"></i>
									</button>
								</div>
							</div>'
                                data-target=".home-categories-target-2">
                                Add New
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>


         

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Brands</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input type="hidden" name="types[]" value="app_top_brands">
                                <select name="app_top_brands[]" class="form-control aiz-selectpicker" multiple
                                    data-live-search="true" data-max-options="10"
                                    data-selected="{{ get_setting('app_top_brands') }}">
                                    @foreach ($brands as $key => $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Banner Section 4</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label>Categories</label> --}}
                            <div class="home-categories-target-4">
                                <input type="hidden" name="types[]" value="app_banner_4">
                                @if (get_setting('app_banner_4') != null)
                                    @foreach (json_decode(get_setting('app_banner_4'), true) as $key => $value)
                                        <div class="row item-counter gutters-5">
                                            <div class="col">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="app_banner_4[]"
                                                        data-live-search="true" data-selected={{ $value }} required>
                                                        @foreach ($banners as $banner)
                                                            <option value="{{ $banner->id }}">{{ $banner->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-max="3"
                                data-toggle="add-more"
                                data-content='<div class="row item-counter gutters-5">
                                <div class="col">
                                    <div class="form-group">
                                        <select class="form-control aiz-selectpicker" name="app_banner_4[]" data-live-search="true" required>
                                            @foreach ($banners as $key => $banner)
                                                <option value="{{ $banner->id }}">{{ $banner->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                        <i class="las la-times"></i>
                                    </button>
                                </div>
                            </div>'
                                data-target=".home-categories-target-4">
                                Add New
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
    
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Banner Section 5</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label>Categories</label> --}}
                            <div class="home-categories-target-5">
                                <input type="hidden" name="types[]" value="app_banner_5">
                                @if (get_setting('app_banner_5') != null)
                                    @foreach (json_decode(get_setting('app_banner_5'), true) as $key => $value)
                                        <div class="row item-counter gutters-5">
                                            <div class="col">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="app_banner_5[]"
                                                        data-live-search="true" data-selected={{ $value }} required>
                                                        @foreach ($banners as $banner)
                                                            <option value="{{ $banner->id }}">{{ $banner->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-max="3"
                                data-toggle="add-more"
                                data-content='<div class="row item-counter gutters-5">
                                <div class="col">
                                    <div class="form-group">
                                        <select class="form-control aiz-selectpicker" name="app_banner_5[]" data-live-search="true" required>
                                            @foreach ($banners as $key => $banner)
                                                <option value="{{ $banner->id }}">{{ $banner->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                        <i class="las la-times"></i>
                                    </button>
                                </div>
                            </div>'
                                data-target=".home-categories-target-5">
                                Add New
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Banner Section 6</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label>Categories</label> --}}
                            <div class="home-categories-target-6">
                                <input type="hidden" name="types[]" value="app_banner_6">
                                @if (get_setting('app_banner_6') != null)
                                    @foreach (json_decode(get_setting('app_banner_6'), true) as $key => $value)
                                        <div class="row item-counter gutters-5">
                                            <div class="col">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="app_banner_6[]"
                                                        data-live-search="true" data-selected={{ $value }} required>
                                                        @foreach ($banners as $banner)
                                                            <option value="{{ $banner->id }}">{{ $banner->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-max="3"
                                data-toggle="add-more"
                                data-content='<div class="row item-counter gutters-5">
                                <div class="col">
                                    <div class="form-group">
                                        <select class="form-control aiz-selectpicker" name="app_banner_6[]" data-live-search="true" required>
                                            @foreach ($banners as $key => $banner)
                                                <option value="{{ $banner->id }}">{{ $banner->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                        <i class="las la-times"></i>
                                    </button>
                                </div>
                            </div>'
                                data-target=".home-categories-target-6">
                                Add New
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            AIZ.plugins.bootstrapSelect('refresh');
        });
    </script>
@endsection
