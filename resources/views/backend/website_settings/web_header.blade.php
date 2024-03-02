@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col">
			<h1 class="h3">Website Header</h1>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12 mx-auto">
		<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
			<div class="card shadow-none">
				<div class="card-header">
					<h6 class="mb-0">Top Header Offer</h6>
				</div>
				<div class="card-body">
					<div class="form-group row ">
						<label class="col-md-2 col-from-label">{{ translate('Show Top Header Offer?') }}</label>
						<div class="col-md-9">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="types[]" value="show_top_header_offer">
								<input type="checkbox" name="show_top_header_offer"
									@if (get_setting('show_top_header_offer') == 'on') checked @endif>
								<span></span>
							</label>
						</div>
					</div>

					<div class="form-group">
						<label>Top Header Offer Content</label>
						<div class="input-group form-group">
							<input type="hidden" name="types[]" value="top_header_offer_title">
							<input type="text" class="form-control" placeholder="" name="top_header_offer_title"
								value="{{ get_setting('top_header_offer_title') }}">
							
						</div>
					</div>

				</div>
			</div>

			<div class="card shadow-none">
				<div class="card-header">
					<h6 class="mb-0">Top Header Content</h6>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label> Header Phone Number</label>
						<div class="input-group form-group">
							<input type="hidden" name="types[]" value="header_phone">
							<input type="text" class="form-control" placeholder="" name="header_phone"
								value="{{ get_setting('header_phone') }}">
							
						</div>
					</div>

				</div>
			</div>

			<div class="text-left mb-3">
				<button type="submit" class="btn btn-primary">Update</button>
			</div>
		</form>

		<div class="card">
			<div class="card-header">
				<h6 class="mb-0">Header Category Menu Setting</h6>
			</div>
			<div class="card-body">
				<form action="{{ route('store.header') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						
						<div class="header-target">
							
							@if (!empty($menus))
								@foreach ($menus as $key => $value)
									<div class="row gutters-5 mt-4">
										<div class="col-sm-10">
											<div class="col-sm-12">
												<div class="form-group">
													<label>Category</label>
													<select class="form-control aiz-selectpicker" name="category[{{$key}}]"
														data-live-search="true" data-selected={{ $value->category_id }}
														required>
														@foreach ($categories as $cat)
															<option value="{{ $cat->id }}">{{ $cat->name }}</option>
														@endforeach
													</select>
												</div>
											</div>
											
											<div class="col-sm-12">
												<div class="form-group">
													<label>Brands</label>
													<select class="form-control aiz-selectpicker" name="brands[{{$key}}][]"
														data-live-search="true" data-selected={{ $value->brands }} multiple
														required>
														@foreach ($brands as $brand)
															<option value="{{ $brand->id }}">{{ $brand->name }}</option>
														@endforeach
													</select>
												</div>
											</div>
										</div>
										<div class="col-sm-2" style="margin:auto;">
											<div class="col-sm-12 text-center" >
												<button type="button"
													class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
													data-toggle="remove-parent" data-parent=".row">
													<i class="las la-times"></i>
												</button>
											</div>
										</div>
									</div>
									
								@endforeach
							@endif
						</div>
						<button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more-menu" data-max="7"
							data-content=''
							data-target=".header-target">
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
				<h6 class="mb-0">Header Brands</h6>
			</div>
			<div class="card-body">
				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						<label>Brands</label>
						<div class="header-brands-target">
							<input type="hidden" name="types[]" value="header_brands">
							@if (get_setting('header_brands') != null)
								@foreach (json_decode(get_setting('header_brands'), true) as $keyb => $valueb)
									<div class="row gutters-5">
										<div class="col">
											<div class="form-group">
												<select class="form-control aiz-selectpicker" name="header_brands[]"
													data-live-search="true" data-selected={{ $valueb }}
													required>
													@foreach ($brands as $keys => $bnd)
														<option value="{{ $bnd->id }}">{{ $bnd->name }}
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
						<button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more" data-max="20"
							data-content='<div class="row gutters-5">
								<div class="col">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="header_brands[]"
											data-live-search="true" data-selected=""
											required>
											@foreach ($brands as $keys => $bnd)
												<option value="{{ $bnd->id }}">{{ $bnd->name }}
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
							</div>'
							data-target=".header-brands-target">
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
	<script>
		
		var childs = $('.header-target').children().length;

		AIZ.extra = {
			addMoreNew: function () {
				$('[data-toggle="add-more-menu"]').each(function () {
					var $this = $(this);
					var content = $this.data("content");
					var target = $this.data("target");
					var max = $this.data("max") ?? 100;
					$this.on("click", function (e) {
						e.preventDefault();
						if ($(target).children().length <= max) {
							$(target).append('<div class="row gutters-5 mt-4">\
								<div class="col-sm-10">\
									<div class="col-sm-12">\
										<div class="form-group">\
											<label>Category</label>\
											<select class="form-control aiz-selectpicker category" name="category['+childs+']"\
												data-live-search="true" data-selected=""\
												required>\
												@foreach ($categories as $key => $cat)\
													<option value="{{ $cat->id }}">{{ $cat->name }}</option>\
												@endforeach\
											</select>\
										</div>\
									</div>\
									<div class="col-sm-12">\
										<div class="form-group">\
											<label>Brands</label>\
											<select class="form-control aiz-selectpicker brand" name="brands['+childs+'][]"\
												data-live-search="true" data-selected="" multiple\
												required>\
												@foreach ($brands as $key => $brand)\
													<option value="{{ $brand->id }}">{{ $brand->name }}</option>\
												@endforeach\
											</select>\
										</div>\
									</div>\
								</div>\
								<div class="col-sm-2" style="margin:auto;">\
									<div class="col-sm-12 text-center" >\
										<button type="button"\
											class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"\
											data-toggle="remove-parent" data-parent=".row">\
											<i class="las la-times"></i>\
										</button>\
									</div>\
								</div>\
							</div>');
							AIZ.plugins.bootstrapSelect();
							childs = childs+1;
						}
					});
				});
			},
		};

		AIZ.extra.addMoreNew();
	</script>
		
@endsection		