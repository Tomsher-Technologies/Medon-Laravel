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
	<div class="col-md-8 mx-auto">
		<div class="card">
			<div class="card-header">
				<h6 class="mb-0">Header Setting</h6>
			</div>
			<div class="card-body">
				<form class="repeater" action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div data-repeater-list="menu">
						<div data-repeater-item>
							<div class="form-group row">
								<label class="col-md-3 col-from-label">Category</label>
								<div class="col-md-9">
									<select name="category" id="category" class="form-control" data-live-search="true" data-max-options="10" data-selected="">
										@foreach ($categories as $key => $cat)
											<option value="{{ $cat->id }}">{{ $cat->name }}</option>
										@endforeach
									</select>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-md-3 col-from-label">Brands</label>
								<div class="col-md-9">
									<select name="brands" id="brands" class="form-control aiz-selectpicker" data-live-search="true" data-max-options="10" data-selected="" multiple>
										@foreach ($brands as $key => $brand)
											<option value="{{ $brand->id }}">{{ $brand->name }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="text-right col-md-12">
								<input data-repeater-delete type="button" class="btn btn-danger action-btn my-2"
								value="Delete" />
							</div>
						</div>
						
					</div>
					<input data-repeater-create type="button" class="btn btn-success action-btn my-1"
                                value="Add" />
                   
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"
        integrity="sha512-foIijUdV0fR0Zew7vmw98E6mOWd9gkGWQBWaoA1EOFAx+pY+N8FmmtIYAVj64R98KeD2wzZh1aHK0JSpKmRH8w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script>
		$('#category').selectpicker();
		$('#brands').selectpicker();

		var repItems = $("div[data-repeater-item]");
        var repCount = repItems.length;
        let count = parseInt(repCount) ;

		$('.repeater').repeater({
			initEmpty: false,
			show: function() {
				$(this).slideDown();
				var repeaterItems = $("div[data-repeater-item]");
                var repeatCount = repeaterItems.length;
                var cnt = parseInt(repeatCount) - 1;
                $('[name="menu['+cnt+'][category]"]').attr("id","category"+count);
				$('[name="menu['+cnt+'][brands]"]').attr("id","brands"+count);
				$('#category'+count).selectpicker('refresh');
				$('#brands'+count).selectpicker('refresh');
			},
			hide: function(deleteElement) {
				if (confirm('Are you sure you want to delete this element?')) {
					$(this).slideUp(deleteElement);
				}
			},
			isFirstItemUndeletable: false
		});
	</script>
		
@endsection		