<div class="form-group row">
    <label class="col-md-3 col-form-label">Category<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
    <div class="col-md-9">
        <select  onchange="brands_form()" class="form-control aiz-selectpicker" name="main_category" id="main_category" data-live-search="true" required>
            <option value="">Select Category</option>
            @foreach ($categories as $category)
                <option {{ ($category->id == $oldArray) ? 'selected' : '' }} value="{{ $category->id }}">
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">Brands<span class="text-danger" style="font-size: 20px;line-height: 1;">*</span></label>
    <div class="col-md-9">
        <select class="form-control aiz-selectpicker" name="link_ref_id[]" id="link_ref_id" data-actions-box="true" data-live-search="true" multiple required>
            @if (!empty($brandsData))
                @foreach ($brandsData as $bd)
                    <option {{ (in_array($bd->id , $selectedBrands)) ? 'selected' : '' }} value="{{ $bd->id }}">
                        {{ $bd->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
</div>
<script>
    $('#main_category').selectpicker({
        size: 5,
        virtualScroll: false
    });
    $('#link_ref_id').selectpicker({
        size: 5,
        virtualScroll: false
    });
</script>
