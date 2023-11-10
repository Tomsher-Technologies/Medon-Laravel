@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h1 class="mb-0 h6">Edit Product</h5>
    </div>
    <div class="">
        <form class="form form-horizontal mar-top" action="{{ route('products.update', $product->id) }}" method="POST"
            enctype="multipart/form-data" id="choice_form">
            <div class="row gutters-5">
                <div class="col-lg-8">
                    <input name="_method" type="hidden" value="POST">
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Product Name <span class="text-danger">*</span>
                                </label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="name" placeholder="Product Name"
                                        value="{{ $product->name }}" required>
                                </div>
                            </div>
                            <div class="form-group row" id="category">
                                <label class="col-lg-3 col-from-label">Category<span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" name="category_id" id="category_id"
                                        data-selected="{{ $product->category_id }}" data-live-search="true" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}
                                            </option>
                                            @foreach ($category->childrenCategories as $childCategory)
                                                @include('categories.child_category', [
                                                    'child_category' => $childCategory,
                                                ])
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row" id="brand">
                                <label class="col-lg-3 col-from-label">Brand</label>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id"
                                        data-live-search="true">
                                        <option value="">Select Brand</option>
                                        @foreach (\App\Models\Brand::all() as $brand)
                                            <option value="{{ $brand->id }}"
                                                @if ($product->brand_id == $brand->id) selected @endif>
                                                {{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">
                                    Unit
                                </label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="unit"
                                        placeholder="Unit (e.g. KG, Pc etc)" value="{{ $product->unit }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Minimum Purchase Qty</label>
                                <div class="col-lg-8">
                                    <input type="number" lang="en" class="form-control" name="min_qty"
                                        value="{{ $product->min_qty <= 1 ? 1 : $product->min_qty }}" min="1"
                                        required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Tags</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control aiz-tag-input" name="tags[]" id="tags"
                                        value="{{ $product->tags }}" placeholder="Type to add a tag" data-role="tagsinput">
                                    <small class="text-muted">This is used for search. Input those words by which cutomer
                                        can find this product.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Slug<span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Slug" id="slug" name="slug"
                                        value="{{ $product->slug }}" required class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Product Images</h5>
                        </div>
                        <div class="card-body">

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="signinSrEmail">Gallery
                                    Images<small>(1000*1000)</small></label>
                                <div class="col-md-8">
                                    <input type="file" name="gallery_images[]" multiple class="form-control"
                                        accept="image/*">

                                    @if ($product->photos)
                                        <div class="file-preview box sm">
                                            @php
                                                $photos = explode(',', $product->photos);
                                            @endphp
                                            @foreach ($photos as $photo)
                                                <div
                                                    class="d-flex justify-content-between align-items-center mt-2 file-preview-item">
                                                    <div
                                                        class="align-items-center align-self-stretch d-flex justify-content-center thumb">
                                                        <img src="{{ $product->image($photo) }}" class="img-fit">
                                                    </div>
                                                    <div class="remove">
                                                        <button class="btn btn-sm btn-link remove-galley"
                                                            data-url="{{ $photo }}" type="button">
                                                            <i class="la la-close"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="signinSrEmail">Thumbnail Image
                                    <small>(1000*1000)</small></label>
                                <div class="col-md-8">
                                    <input type="file" name="thumbnail_image" class="form-control" accept="image/*">

                                    @if ($product->thumbnail_img)
                                        <div class="file-preview box sm">
                                            <div
                                                class="d-flex justify-content-between align-items-center mt-2 file-preview-item">
                                                <div
                                                    class="align-items-center align-self-stretch d-flex justify-content-center thumb">
                                                    <img src="{{ $product->image($product->thumbnail_img) }}"
                                                        class="img-fit">
                                                </div>
                                                <div class="remove">
                                                    <button class="btn btn-sm btn-link remove-thumbnail" type="button">
                                                        <i class="la la-close"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>


                    {{-- Attributes --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ translate('Product Variation') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row gutters-5">
                                <div class="col-lg-3">
                                    <input type="text" class="form-control" value="{{ translate('Attributes') }}"
                                        disabled>
                                </div>
                                <div class="col-lg-8">
                                    <select name="choice_attributes[]" id="choice_attributes"
                                        data-selected-text-format="count" data-live-search="true"
                                        class="form-control aiz-selectpicker" multiple
                                        data-placeholder="{{ translate('Choose Attributes') }}">
                                        @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                            <option value="{{ $attribute->id }}"
                                                @if ($product->attributes != null && in_array($attribute->id, json_decode($product->attributes, true))) selected @endif>
                                                {{ $attribute->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="">
                                <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}
                                </p>
                                <br>
                            </div>
                            <div class="customer_choice_options" id="customer_choice_options">

                                @if ($product->choice_options)
                                    @foreach (json_decode($product->choice_options) as $key => $choice_option)
                                        <div class="form-group row">
                                            <div class="col-lg-3">
                                                <input type="hidden" name="choice_no[]"
                                                    value="{{ $choice_option->attribute_id }}">
                                                <input type="text" class="form-control" name="choice[]"
                                                    value="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->name }}"
                                                    placeholder="{{ translate('Choice Title') }}" disabled>
                                            </div>
                                            <div class="col-lg-8">
                                                <select class="form-control aiz-selectpicker attribute_choice"
                                                    data-live-search="true"
                                                    name="choice_options_{{ $choice_option->attribute_id }}[]" multiple>
                                                    @foreach (\App\Models\AttributeValue::where('attribute_id', $choice_option->attribute_id)->get() as $row)
                                                        <option value="{{ $row->value }}"
                                                            @if (in_array($row->value, $choice_option->values)) selected @endif>
                                                            {{ $row->value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                {{-- <input type="text" class="form-control aiz-tag-input" name="choice_options_{{ $choice_option->attribute_id }}[]" placeholder="{{ translate('Enter choice values') }}" value="{{ implode(',', $choice_option->values) }}" data-on-change="update_sku"> --}}
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                            </div>
                        </div>
                    </div>
                    {{-- End Attributes --}}


                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Product price + stock</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Unit price</label>
                                <div class="col-lg-6">
                                    <input type="text" placeholder="Unit price" name="unit_price"
                                        class="form-control" value="{{ $product->unit_price }}" required>
                                </div>
                            </div>

                            @php
                                $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                                $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                            @endphp

                            <div class="form-group row">
                                <label class="col-sm-3 col-from-label" for="start_date">Discount Date Range</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control aiz-date-range"
                                        @if ($product->discount_start_date && $product->discount_end_date) value="{{ $start_date . ' to ' . $end_date }}" @endif
                                        name="date_range" placeholder="Select Date" data-time-picker="true"
                                        data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Discount</label>
                                <div class="col-lg-6">
                                    <input type="number" lang="en" min="0" step="0.01"
                                        placeholder="Discount" name="discount" class="form-control"
                                        value="{{ $product->discount }}">
                                </div>
                                <div class="col-lg-3">
                                    <select class="form-control aiz-selectpicker" name="discount_type">
                                        <option value="amount" <?php if ($product->discount_type == 'amount') {
                                            echo 'selected';
                                        } ?>>Flat</option>
                                        <option value="percent" <?php if ($product->discount_type == 'percent') {
                                            echo 'selected';
                                        } ?>>Percent</option>
                                    </select>
                                </div>
                            </div>

                            @if (addon_is_activated('club_point'))
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">
                                        Set Point
                                    </label>
                                    <div class="col-md-6">
                                        <input type="number" lang="en" min="0"
                                            value="{{ $product->earn_point }}" step="1" placeholder="1"
                                            name="earn_point" class="form-control">
                                    </div>
                                </div>
                            @endif

                            <div id="show-hide-div">
                                <div class="form-group row" id="quantity">
                                    <label class="col-lg-3 col-from-label">Quantity</label>
                                    <div class="col-lg-6">
                                        <input type="number" lang="en"
                                            value="{{ optional($product->stocks->first())->qty }}" step="1"
                                            placeholder="Quantity" name="current_stock" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">
                                        SKU
                                    </label>
                                    <div class="col-md-6">
                                        <input type="text" placeholder="SKU"
                                            value="{{ optional($product->stocks->first())->sku }}" required name="sku"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="sku_combination" id="sku_combination">

                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Product Description</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Description </label>
                                <div class="col-lg-9">
                                    <textarea class="aiz-text-editor" name="description">{{ $product->description }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card repeater">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Product Tabs</h5>
                        </div>
                        <div class="card-body">
                            <div data-repeater-list="tabs">
                                <div data-repeater-item>
                                    <input type="hidden" name="tab_id">
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">Heading</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="tab_heading">
                                        </div>
                                        <input data-repeater-delete type="button" class="btn btn-danger action-btn"
                                            value="Delete" />
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">Description</label>
                                        <div class="col-md-8">
                                            <textarea class="text-area" name="tab_description"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input data-repeater-create type="button" class="btn btn-success action-btn"
                                value="Add" />
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Product Dimensions</h5>
                        </div>
                        <div class="card-body">

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">Length</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" placeholder="Length" name="length"
                                        class="form-control" value="{{ $product->length }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">Height</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" placeholder="Height" name="height"
                                        class="form-control" value="{{ $product->height }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">Width</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" placeholder="Width" name="width"
                                        class="form-control" value="{{ $product->width }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">Weight</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" placeholder="Weight" name="weight"
                                        class="form-control" value="{{ $product->weight }}">
                                </div>
                            </div>

                        </div>
                    </div>


                    <!--                 <div class="card">
                                                                                                                                                                                                                                        <div class="card-header">
                                                                                                                                                                                                                                            <h5 class="mb-0 h6">Product Shipping Cost</h5>
                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                        <div class="card-body">

                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                    </div>-->

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Product Videos</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Video Provider</label>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" name="video_provider"
                                        id="video_provider">
                                        <option value="youtube" <?php if ($product->video_provider == 'youtube') {
                                            echo 'selected';
                                        } ?>>Youtube</option>
                                        <option value="dailymotion" <?php if ($product->video_provider == 'dailymotion') {
                                            echo 'selected';
                                        } ?>>Dailymotion
                                        </option>
                                        <option value="vimeo" <?php if ($product->video_provider == 'vimeo') {
                                            echo 'selected';
                                        } ?>>Vimeo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Video Link</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="video_link"
                                        value="{{ $product->video_link }}" placeholder="Video Link">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">PDF Specification</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="signinSrEmail">PDF Specification</label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                Browse</div>
                                        </div>
                                        <div class="form-control file-amount">Choose File</div>
                                        <input type="hidden" name="pdf" value="{{ $product->pdf }}"
                                            class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">External link</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    External link
                                </label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="External link" name="external_link"
                                        value="{{ $product->external_link }}" class="form-control">
                                    <small class="text-muted">Leave it blank if you do not use external site link</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    External link button text
                                </label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="External link button text"
                                        name="external_link_btn" value="{{ $product->external_link_btn }}"
                                        class="form-control">
                                    <small class="text-muted">Leave it blank if you do not use external site link</small>
                                </div>
                            </div>
                        </div>
                    </div> --}}


                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">SEO Meta Tags</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Meta Title</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control"
                                        value="{{ getSeoValues($product->seo, 'meta_title') }}" name="meta_title"
                                        placeholder="Meta Title">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Description</label>
                                <div class="col-lg-8">
                                    <textarea name="meta_description" rows="8" class="form-control">{{ getSeoValues($product->seo, 'meta_description') }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">Keywords</label>
                                <div class="col-md-8">
                                    {{-- data-max-tags="1" --}}
                                    <input type="text" class="form-control aiz-tag-input" name="meta_keywords[]"
                                        placeholder="Type and hit enter to add a keyword"
                                        value="{{ getSeoValues($product->seo, 'meta_keywords') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">OG Title</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="og_title" placeholder="OG Title"
                                        value="{{ getSeoValues($product->seo, 'og_title') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">OG Description</label>
                                <div class="col-lg-8">
                                    <textarea name="og_description" rows="8" class="form-control">{{ getSeoValues($product->seo, 'og_description') }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Twitter Title</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="twitter_title"
                                        placeholder="Twitter Title"
                                        value="{{ getSeoValues($product->seo, 'twitter_title') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">Twitter Description</label>
                                <div class="col-lg-8">
                                    <textarea name="twitter_description" rows="8" class="form-control">{{ getSeoValues($product->seo, 'twitter_description') }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>

                <div class="col-lg-4">

                    <div class="card bg-transparent shadow-none border-0">
                        <div class="card-body p-0">
                            <div class="btn-toolbar justify-content-end" role="toolbar"
                                aria-label="Toolbar with button groups">
                                <div class="btn-group" role="group" aria-label="Second group">
                                    <button type="submit" name="button" value="publish"
                                        class="btn btn-info action-btn">Update Product</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Price visibility</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">Hide Price</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="hide_price" value="1"
                                            @if ($product->hide_price == 1) checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Low Stock Quantity Warning</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    Quantity
                                </label>
                                <input type="number" name="low_stock_quantity"
                                    value="{{ $product->low_stock_quantity }}" min="0" step="1"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">
                                Stock Visibility State
                            </h5>
                        </div>

                        <div class="card-body">

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">Show Stock Quantity</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="quantity"
                                            @if ($product->stock_visibility_state == 'quantity') checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">Show Stock With Text Only</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="text"
                                            @if ($product->stock_visibility_state == 'text') checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">Hide Stock</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="hide"
                                            @if ($product->stock_visibility_state == 'hide') checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Featured</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-md-6 col-from-label">Status</label>
                                        <div class="col-md-6">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="featured" value="1"
                                                    @if ($product->featured == 1) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Todays Deal</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-md-6 col-from-label">Status</label>
                                        <div class="col-md-6">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="todays_deal" value="1"
                                                    @if ($product->todays_deal == 1) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3 text-right">
                        <button type="submit" name="button" class="btn btn-info">Update Product</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"
        integrity="sha512-foIijUdV0fR0Zew7vmw98E6mOWd9gkGWQBWaoA1EOFAx+pY+N8FmmtIYAVj64R98KeD2wzZh1aHK0JSpKmRH8w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $('.remove-thumbnail').on('click', function() {
            thumbnail = $(this)
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: '{{ route('products.delete_thumbnail') }}',
                data: {
                    id: '{{ $product->id }}'
                },
                success: function(data) {
                    $(thumbnail).closest('.file-preview-item').remove();
                }
            });

        });
        $('.remove-galley').on('click', function() {
            thumbnail = $(this)
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: '{{ route('products.delete_gallery') }}',
                data: {
                    url: $(thumbnail).data('url'),
                    id: '{{ $product->id }}'
                },
                success: function(data) {
                    $(thumbnail).closest('.file-preview-item').remove();
                }
            });
        });
    </script>

    @php
        $tabs = [];
        foreach ($product->tabs as $key => $tab) {
            $tabs[$key]['tab_id'] = $tab->id;
            $tabs[$key]['tab_heading'] = $tab->heading;
            $tabs[$key]['tab_description'] = $tab->content;
        }
    @endphp

    <script>
        var repeater = $('.repeater').repeater({
            initEmpty: true,
            show: function() {

                buttons = [
                    ["font", ["bold", "underline", "italic", "clear"]],
                    ["para", ["ul", "ol", "paragraph"]],
                    ["style", ["style"]],
                    ["color", ["color"]],
                    ["table", ["table"]],
                    ["insert", ["link", "picture", "video"]],
                    ["view", ["fullscreen", "undo", "redo"]],
                ];

                note = $(this).find('.text-area').summernote({
                    toolbar: buttons,
                    height: 200,
                    callbacks: {
                        onImageUpload: function(data) {
                            data.pop();
                        },
                        onPaste: function(e) {
                            if (format) {
                                var bufferText = ((e.originalEvent || e).clipboardData || window
                                    .clipboardData).getData('Text');
                                e.preventDefault();
                                document.execCommand('insertText', false, bufferText);
                            }
                        }
                    }
                });

                var nativeHtmlBuilderFunc = note.summernote('module', 'videoDialog').createVideoNode;

                note.summernote('module', 'videoDialog').createVideoNode = function(url) {
                    var wrap = $('<div class="embed-responsive embed-responsive-16by9"></div>');
                    var html = nativeHtmlBuilderFunc(url);
                    html = $(html).addClass('embed-responsive-item');
                    return wrap.append(html)[0];
                };

                $(this).slideDown();

            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                }
            },
        });

        repeater.setList({!! json_encode($tabs) !!});
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            show_hide_shipping_div();
        });

        $("[name=shipping_type]").on("change", function() {
            show_hide_shipping_div();
        });

        function show_hide_shipping_div() {
            var shipping_val = $("[name=shipping_type]:checked").val();

            $(".flat_rate_shipping_div").hide();

            if (shipping_val == 'flat_rate') {
                $(".flat_rate_shipping_div").show();
            }
        }

        function add_more_customer_choice_option(i, name) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: '{{ route('products.add-more-choice-option') }}',
                data: {
                    attribute_id: i
                },
                success: function(data) {
                    var obj = JSON.parse(data);
                    $('#customer_choice_options').append(
                        '\
                                                                                                                                                                                                                                    <div class="form-group row">\
                                                                                                                                                                                                                                        <div class="col-md-3">\
                                                                                                                                                                                                                                            <input type="hidden" name="choice_no[]" value="' +
                        i +
                        '">\
                                                                                                                                                                                                                                            <input type="text" class="form-control" name="choice[]" value="' +
                        name +
                        '" placeholder="Choice Title" readonly>\
                                                                                                                                                                                                                                        </div>\
                                                                                                                                                                                                                                        <div class="col-md-8">\
                                                                                                                                                                                                                                            <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_' +
                        i +
                        '[]" multiple>\
                                                                                                                                                                                                                                                ' +
                        obj +
                        '\
                                                                                                                                                                                                                                            </select>\
                                                                                                                                                                                                                                        </div>\
                                                                                                                                                                                                                                    </div>'
                    );
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            });


        }

        $('input[name="colors_active"]').on('change', function() {
            if (!$('input[name="colors_active"]').is(':checked')) {
                $('#colors').prop('disabled', true);
                AIZ.plugins.bootstrapSelect('refresh');
            } else {
                $('#colors').prop('disabled', false);
                AIZ.plugins.bootstrapSelect('refresh');
            }
            update_sku();
        });

        $(document).on("change", ".attribute_choice", function() {
            update_sku();
        });

        $('#colors').on('change', function() {
            update_sku();
        });

        function delete_row(em) {
            $(em).closest('.form-group').remove();
            update_sku();
        }

        function delete_variant(em) {
            $(em).closest('.variant').remove();
        }

        function update_sku() {
            $.ajax({
                type: "POST",
                url: '{{ route('products.sku_combination_edit') }}',
                data: $('#choice_form').serialize(),
                success: function(data) {
                    $('#sku_combination').html(data);
                    AIZ.uploader.previewGenerate();
                    AIZ.plugins.fooTable();
                    if (data.length > 1) {
                        $('#show-hide-div').hide();
                    } else {
                        $('#show-hide-div').show();
                    }
                }
            });
        }

        AIZ.plugins.tagify();

        $(document).ready(function() {
            update_sku();

            $('.remove-files').on('click', function() {
                $(this).parents(".col-md-4").remove();
            });
        });

        $('#choice_attributes').on('change', function() {
            $.each($("#choice_attributes option:selected"), function(j, attribute) {
                flag = false;
                $('input[name="choice_no[]"]').each(function(i, choice_no) {
                    if ($(attribute).val() == $(choice_no).val()) {
                        flag = true;
                    }
                });
                if (!flag) {
                    add_more_customer_choice_option($(attribute).val(), $(attribute).text());
                }
            });

            var str = @php echo $product->attributes @endphp;

            $.each(str, function(index, value) {
                flag = false;
                $.each($("#choice_attributes option:selected"), function(j, attribute) {
                    if (value == $(attribute).val()) {
                        flag = true;
                    }
                });
                if (!flag) {
                    $('input[name="choice_no[]"][value="' + value + '"]').parent().parent().remove();
                }
            });

            update_sku();
        });
    </script>
@endsection
