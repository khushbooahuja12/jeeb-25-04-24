@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Requested Products
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/base_products') ?>">Products</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-30">
                    <div class="card-body overflow-auto">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Product Name"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Store </th>
                                    <th style="width: 90%">Base Product</th>
                                    <th>Itemcode</th>
                                    <th>Barcode</th>
                                    <th>Product Name (EN)</th>
                                    <th>Product Name (AR)</th>
                                    <th>Unit</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Tags</th>
                                    <th>Product Image</th>
                                    <th>Country Code</th>
                                    <th>Country Flag</th>
                                    <th>Stock</th>
                                    <th>Store Price</th>
                                    @if(in_array('product-management',$permissions))
                                    <th class="nosort">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if ($products)
                                @foreach ($products as $key => $row)
                                <tr class="product{{ $row->id }}">
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $row->store_company_name }}-{{ $row->store_name }}</td>
                                    <td style="width: 90%">
                                        @if($row->fk_product_id)
                                                @php $base_product = \App\Model\BaseProduct::find($row->fk_product_id); @endphp

                                                <select class="form-control search_base_products_ajax" name="fk_product_id" id="fk_product_id_{{ $row->id }}">
                                                    <option value="" selected>--Select--</option>
                                                    <option value="{{ $base_product->id }}" selected>{{ $base_product->product_name_en }}</option>
                                                </select>
                                        @else
                                            <select class="form-control search_base_products_ajax" name="fk_product_id" id="fk_product_id_{{ $row->id }}">
                                                <option value="" selected>--Select--</option>
                                            </select>
                                        @endif
                                        <br><br>
                                        <!-- Button trigger modal -->
                                        @if(in_array('product-management',$permissions))
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#requestedProductModal{{ $row->id }}">
                                            Create Base Product
                                        </button>
                                        @endif
                                    </td>
                                    <td>{{ $row->itemcode }}</td>
                                    <td>{{ $row->barcode }}</td>
                                    <td>{{ $row->product_name_en }}</td>
                                    <td>{{ $row->product_name_ar }}</td>
                                    <td>{{ $row->unit }}</td>
                                    <td class="col-md-3">
                                        @php
                                            $product_subcategory = $row->getProductSubCategory;
                                            $product_subcategory_id = $product_subcategory ? $product_subcategory->id : false;
                                            $product_subcategory_name = $product_subcategory ? $product_subcategory->category_name_en : 'N/A';
                                            @endphp
                                            <div class="text_mode text_mode_{{ $row->id }} category_text_{{ $row->id }}" id="category_text">{{ $product_subcategory_name }} ({{ $product_subcategory_id }})</div>
                                    </td>
                                    <td class="col-md-3">
                                        @php
                                            $product_brand = $row->getProductBrand; 
                                            $product_brand_id = $product_brand ? $product_brand->id : false;
                                            $product_brand_name = $product_brand ? $product_brand->brand_name_en.'('.$product_brand_id.')' : 'N/A';
                                            @endphp
                                            <div class="text_mode text_mode_{{ $row->id }} brand_text_{{ $row->id }}" id="brand_text">{{ $product_brand_name }}</div>
                                    </td>
                                    <td>{{ $row->_tags }}</td>
                                    <td>@php
                                        $product_image_url = $row->product_image_url ?? $row->product_image_url;
                                        @endphp
                                        <a href="javascript:void(0)" data-src="{{ $product_image_url }}" fk_product_id="{{ $row->id }}"
                                            onclick="openImageModal(this)" style="max-width: 90%; height: 110px; display: block; background: #ffffff; border: 1px #dee2e6 solid; text-align: center;">
                                            <img class="product_image_change_{{ $row->id }}" src="{{ $product_image_url }}" height="100">
                                        </a>
                                    <td>{{ $row->country_code }}</td>
                                    <td>
                                        @php
                                            $country_flag_image_url = $row->country_icon ?? $row->country_icon 
                                        @endphp
                                    <img class="country_icon_change_{{ $row->id }}" src="{{ $country_flag_image_url }}" width="100%"></td>
                                    <td>{{ $row->stock }}</td>
                                    <td>{{ $row->base_price }}</td>
                                    @if(in_array('product-management',$permissions))
                                    <td>
                                        <button class="btn-sm btn-primary" onclick="addStoreProduct(<?= $row->id ?>)">add</button>
                                        &nbsp;
                                        <button class="btn-sm btn-danger" onclick="removeStoreProductt(<?= $row->id ?>)">remove</button>
                                    </td>
                                    @endif
                                </tr>
                                <!-- Modal -->
                                <div class="modal fade" id="requestedProductModal{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="requestedProductModal{{ $row->id }}Label" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                        <h5 class="modal-title" id="requestedProductModal{{ $row->id }}Label">Requested Product</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        </div>
                                        <form method="post" id="regForm" enctype="multipart/form-data"action="{{ route('admin.base_products.create_requested_base_product') }}">
                                            @csrf
                                            <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Product Category</label>
                                                                <select name="fk_category_id" id="fk_category_id" onchange="getSubCategory(this); "
                                                                    data-title="Product Category" class="form-control regInputs select2" style="width: 100%;">
                                                                    <option value="">--Select--</option>
                                                                    @if ($parent_categories)
                                                                        @foreach ($parent_categories as $parent_category)
                                                                            <option value="{{ $parent_category->id }}" class="category{{ $parent_category->id }}"
                                                                                <?= $row->getProductSubCategory->parent_id == $parent_category->id ? 'selected' : '' ?>>
                                                                                {{ $parent_category->category_name_en }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                                <p class="errorPrint" id="fk_category_idError"></p>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $sub_categories = \App\Model\Category::where('parent_id',$row->getProductSubCategory->parent_id)->get();
                                                        @endphp
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Sub Category</label>
                                                                <select name="fk_sub_category_id" id="fk_sub_category_id"
                                                                    data-title="Product Sub Category" class="form-control regInputs select2" style="width: 100%;">
                                                                    <option value="">--Select--</option>
                                                                    @if ($sub_categories)
                                                                        @foreach ($sub_categories as $sub_category)
                                                                            <option value="{{ $sub_category->id }}" class="category{{ $sub_category->id }}"
                                                                                <?= $product_subcategory_id == $sub_category->id ? 'selected' : '' ?>>
                                                                                {{ $sub_category->category_name_en }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Product Brand</label>
                                                                <select name="fk_brand_id" id="fk_brand_id" data-title="Product Brand"
                                                                    class="form-control regInputs">
                                                                    <option value="">--Select--</option>
                                                                    @if ($brands)
                                                                        @foreach ($brands as $brand)
                                                                            <option value="{{ $brand->id }}"
                                                                                <?= $product_brand_id == $brand->id ? 'selected' : '' ?>>
                                                                                {{ $brand->brand_name_en }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                                <p class="errorPrint" id="fk_brand_idError"></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Unit</label>
                                                                <input type="text" name="unit" id="unit" data-title="Unit"
                                                                    value="{{ old('unit', $row->unit) }}" class="form-control regInputs" placeholder="Unit">
                                                                <p class="errorPrint" id="unitError"></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Product Name (En)</label>
                                                                <input type="text" name="product_name_en" id="product_name_en"
                                                                    value="{{ old('product_name_en',$row->product_name_en) }}" data-title="Product Name (En)"
                                                                    class="form-control regInputs" placeholder="Product Name (En)">
                                                                <p class="errorPrint" id="product_name_enError"></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Product Name (Ar)</label>
                                                                <input type="text" name="product_name_ar" id="product_name_ar"
                                                                    value="{{ old('product_name_ar', $row->product_name_ar) }}" data-title="Product Name (Ar)"
                                                                    class="form-control regInputs" placeholder="Product Name (Ar)">
                                                                <p class="errorPrint" id="nameError"></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Main Tags</label>
                                                                <select name="main_tags[]" id="main_tags" data-title="Product Main Tags"
                                                                    class="form-control regInputs">
                                                                    <option value="">--Select--</option>
                                                                    @if ($product_tags)
                                                                        @foreach ($product_tags as $product_tag)
                                                                            <option value="{{ $product_tag->id }}"
                                                                                <?= old('main_tags') ? (old('main_tags') == $product_tag->id ? 'selected' : '') : '' ?>>
                                                                                {{ $product_tag->title_en }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Tags</label>
                                                                <br>
                                                                <select name="_tags[]" id="_tags" data-title="Product Tags"
                                                                    class="form-control regInputs select2" multiple style="width: 100%;">
                                                                    <option value="" disabled>Select Tags</option>
                                                                    @if ($product_tags)
                                                                        @foreach ($product_tags as $product_tag)
                                                                            <option value="{{ $product_tag->id }}"
                                                                                <?= old('_tags') ? (old('_tags') == $product_tag->id ? 'selected' : '') : '' ?>>
                                                                                {{ $product_tag->title_en }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6" style="display: none">
                                                            <div class="form-group">
                                                                <label>Min Scale</label>
                                                                <input type="text" name="min_scale" value="0" class="form-control"
                                                                    placeholder="Min Scale">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6" style="display: none">
                                                            <div class="form-group">
                                                                <label>Max Scale</label>
                                                                <input type="text" name="max_scale" value="0" class="form-control"
                                                                    placeholder="Max Scale">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Description (En)</label>
                                                                <textarea class="form-control" id="desc_en" name="desc_en"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Description (Ar)</label>
                                                                <textarea class="form-control" id="desc_ar" name="desc_ar"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Characteristics (En)</label>
                                                                <textarea class="form-control" name="characteristics_en" rows="6"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Characteristics (Ar)</label>
                                                                <textarea class="form-control" name="characteristics_ar" rows="6"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Search Filters</label>
                                                                <textarea class="form-control" name="search_filters" rows="6"></textarea>
                                                                </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Custom Tag Bundle</label>
                                                                <textarea class="form-control" name="custom_tag_bundle" rows="6"></textarea>
                                                                </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        @php
                                                        $product_image_url = $row->product_image_url ?? $row->product_image_url;
                                                        @endphp
                                                        <div class="col-md-4">
                                                            <label>Product Main Image</label>
                                                            <div class="form-group">
                                                                <input type="file" accept="image/*" class="dropify regInputs"
                                                                    data-title="Image" id="image" name="image" data-default-file="{{ $product_image_url }}" value="{{ $product_image_url }}"/>
                                                                <p class="errorPrint" id="imageError"></p>
                                                            </div>
                                                            <input type="hidden" name="image_default_file" value="{{ $product_image_url }}">
                                                        </div>
                                                        @php
                                                            $country_flag_image_url = $row->country_icon ?? $row->country_icon 
                                                        @endphp
                                                        <div class="col-md-4">
                                                            <label>Country Icon</label>
                                                            <div class="form-group">
                                                                <input type="file" accept="image/*" class="dropify regInputs"
                                                                    id="country_icon" name="country_icon" data-default-file="{{ $country_flag_image_url }}"/>
                                                            </div>
                                                            <input type="hidden" name="country_icon_default_file" value="{{ $country_flag_image_url }}">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Country Code</label>
                                                                <input type="text" name="country_code" value="{{ $row->country_code }}" class="form-control"
                                                                    placeholder="exp: QAR">
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                </div>
                                                <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $products->count() }} of {{ $products->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $products->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function addStoreProduct(row) {
            if (confirm("Are you sure you want to add this product?")) {

                var product_id = row;
                var fk_product_id = $('#fk_product_id_'+row+'').val();

                if(fk_product_id == null || fk_product_id == ""){
                    var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Please select a valid Base product</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                        return false;
                }

                $.ajax({
                    url: "<?= url('admin/base_products/add_requested_products_store') ?>",
                    type: 'post',
                    dataType: 'json',
                    data: {
                        product_id: product_id,
                        fk_product_id: fk_product_id
                    },
                    cache: false
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);
                        $(".product" + product_id).remove();
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }
                });
            }
        }

        function removeStoreProductt(row) {
            if (confirm("Are you sure you want to remove this product request?")) {

                var product_id = row;
                $.ajax({
                    url: "<?= url('admin/base_products/remove_store_requested_product') ?>",
                    type: 'post',
                    dataType: 'json',
                    data: {
                        product_id: product_id,
                    },
                    cache: false
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);
                        $(".product" + product_id).remove();
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }
                });
            }
        }

        $(document).ready(function() {
            $('#regForm').validate({
                rules: {
                    fk_company_id: {
                        required: true
                    },
                    fk_category_id: {
                        required: true
                    },
                    product_name_en: {
                        required: true
                    },
                    product_name_ar: {
                        required: true
                    },
                    distributor_price: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    product_price: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    image: {
                        required: true
                    }
                }
            });
        });

        function getSubCategory(obj) {
            var id = $(obj).val();
            $('#fk_sub_category_id').empty();
            var html = "<option value=''>--Select--</option>";
            if (id) {
                if (id == 16) {
                    $("input[name=min_scale]").parent().parent().css('display', 'block');
                    $("input[name=max_scale]").parent().parent().css('display', 'block');
                }
                $.ajax({
                    url: "<?= url('admin/products/get_sub_category') ?>",
                    type: 'post',
                    data: 'fk_category_id=' + id + '&_token=<?= csrf_token() ?>',
                    success: function(data) {
                        if (data.error_code == "200") {
                            html += data.data;
                        } else {
                            alert(data.message);
                        }
                        $('#fk_sub_category_id').append(html);
                    }
                });
            } else {
                alert("Something went wrong");
            }
        }

        function getSubCategory(obj) {
            var id = $(obj).val();
            $('#fk_sub_category_id').empty();
            var html = "<option value=''>--Select--</option>";
            if (id) {
                if (id == 16) {
                    $("input[name=min_scale]").parent().parent().css('display', 'block');
                    $("input[name=max_scale]").parent().parent().css('display', 'block');
                }
                $.ajax({
                    url: "<?= url('admin/products/get_sub_category') ?>",
                    type: 'post',
                    data: 'fk_category_id=' + id + '&_token=<?= csrf_token() ?>',
                    success: function(data) {
                        if (data.error_code == "200") {
                            html += data.data;
                        } else {
                            alert(data.message);
                        }
                        $('#fk_sub_category_id').append(html);
                    }
                });
            } else {
                alert("Something went wrong");
            }
        }
    </script>

@endsection
