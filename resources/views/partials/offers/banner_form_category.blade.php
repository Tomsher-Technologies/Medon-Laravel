<div class="form-group row">
    <label class="col-md-3 col-form-label">Link</label>
    <div class="col-md-9">
        <select class="form-control aiz-selectpicker" name="link_ref_id[]" id="link_ref_id" data-live-search="true" multiple
            required>
            
            @foreach ($categories as $category)
                <option {{ (in_array($category->id, $oldArray)) ? 'selected' : '' }} value="{{ $category->id }}">
                    {{ $category->name }}
                </option>
                @foreach ($category->childrenCategories as $childCategory)
                    @include('partials.offers.child_category', [
                        'child_category' => $childCategory,
                        'old_data' => $oldArray,
                    ])
                @endforeach
            @endforeach
        </select>
    </div>
</div>
<script>
    $('#link_ref_id').selectpicker({
        size: 5,
        virtualScroll: false
    });
</script>
