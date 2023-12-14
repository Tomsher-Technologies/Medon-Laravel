@php
    $value = null;
    for ($i = 0; $i < $child_category->level; $i++) {
        $value .= '--';
    }
@endphp
<option {{ (in_array($child_category->id, $oldArray)) ? 'selected' : '' }} value="{{ $child_category->id }}">{{ $value . ' ' . $child_category->name }}</option>
@if ($child_category->categories)
    @foreach ($child_category->categories as $childCategory)
        @include('partials.offers.child_category', [
            'child_category' => $childCategory,
            'old_data' => $oldArray,
        ])
    @endforeach
@endif
Z