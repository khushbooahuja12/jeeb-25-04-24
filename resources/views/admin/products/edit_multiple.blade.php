@extends('admin.layouts.dashboard_layout_for_product_admin')
@section('content')
<style>
    .text_mode {
        display: block;
    }
    .edit_mode {
        display: none;
    }
    .product_edit {
        display: block;
    }
    .product_saving {
        display: none;
    }
    .product_save {
        display: none;
    }
    .product_btn {
        width: 70px;
        height: 55px;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Products Edit Multiple</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/products') ?>">Products</a></li>
                        <li class="breadcrumb-item active">Edit Multiple</li>
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
                    <div class="card-body" style="overflow-x: scroll">
                        <form class="form-inline searchForm" id="searchCompanyForm" method="POST" action="">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" id="filter" placeholder="Search Product Name"
                                    value="{{ $filter }}">
                            </div>
                            <div class="form-group mb-2">
                                <select name="filter_category" id="filter_category" class="form-control">
                                    @if ($categories)
                                        <option value="0">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $filter_category==$category->id ? 'selected="true"' : '' }}>{{ $category->category_name_en }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <select name="company_id" id="company_id" class="form-control">
                                    @if ($companies)
                                        <option value="0">All Companies</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}" {{ $company_id==$company->id ? 'selected="true"' : '' }}>{{ $company->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Company / Supplier</th>
                                    <th>Product ID</th>
                                    <th style="min-width: 180px;">Product Name (EN)</th>
                                    <th style="min-width: 180px;">Product Name (AR)</th>
                                    <th>Quantity </th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Tags</th>
                                    <th class="nosort">Product Image</th>
                                    <th>Country Code</th>
                                    <th class="nosort">Country Flag</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="visibility: hidden">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                        <tr class="product<?= $row->id ?>">
                                            <td>{{ $starting_no + $key + 1 }}</td>
                                            <form name="form_<?= $row->id ?>" id="form_<?= $row->id ?>" enctype="multipart/form-data">
                                                <td>{{ !empty($row->getCompany) ? $row->getCompany->name : 'N/A' }}</td>
                                                <td>({{ $row->id }})</td>
                                                <td>
                                                    <div class="text_mode text_mode_{{ $row->id }} product_name_en_text_{{ $row->id }}" id="product_name_en_text">{{ $row->product_name_en }}</div>
                                                    <textarea class="edit_mode edit_mode_{{ $row->id }}" type="text" name="product_name_en" id="product_name_en">{{ $row->product_name_en }}</textarea>
                                                </td>
                                                <td>
                                                    <div class="text_mode text_mode_{{ $row->id }} product_name_ar_text_{{ $row->id }}" id="product_name_ar_text">{{ $row->product_name_ar }}</div>
                                                    <textarea class="edit_mode edit_mode_{{ $row->id }}" type="text" name="product_name_ar" id="product_name_ar">{{ $row->product_name_ar }}</textarea>
                                                </td>
                                                <td>
                                                    <div class="text_mode text_mode_{{ $row->id }} unit_text_{{ $row->id }}" id="unit_text">{{ $row->unit }}</div>
                                                    <input class="edit_mode edit_mode_{{ $row->id }}" type="text" name="unit" id="unit" value="{{ $row->unit }}"/>
                                                </td>
                                                <td>
                                                    @php
                                                    $product_subcategory = $row->getProductSubCategory; 
                                                    $product_subcategory_id = $product_subcategory ? $product_subcategory->id : false;
                                                    $product_subcategory_name = $product_subcategory ? $product_subcategory->category_name_en : 'N/A';
                                                    @endphp
                                                    <div class="text_mode text_mode_{{ $row->id }} category_text_{{ $row->id }}" id="category_text">{{ $product_subcategory_name }} ({{ $product_subcategory_id }})</div>
                                                    <select class="edit_mode edit_mode_{{ $row->id }}" name="fk_sub_category_id" id="fk_sub_category_id">
                                                        @if ($categories)
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}" {{ $product_subcategory_id==$category->id ? 'selected="true"' : '' }}>{{ $category->category_name_en }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>
                                                <td>
                                                    @php
                                                    $product_brand = $row->getProductBrand; 
                                                    $product_brand_id = $product_brand ? $product_brand->id : false;
                                                    $product_brand_name = $product_brand ? $product_brand->brand_name_en.'('.$product_brand_id.')' : 'N/A';
                                                    @endphp
                                                    <div class="text_mode text_mode_{{ $row->id }} brand_text_{{ $row->id }}" id="brand_text">{{ $product_brand_name }}</div>
                                                    <select class="edit_mode edit_mode_{{ $row->id }}" name="fk_brand_id" id="fk_brand_id">
                                                        <option value="">NULL</option>
                                                        @if ($brands)
                                                            @foreach ($brands as $brand)
                                                                <option value="{{ $brand->id }}" {{ $product_brand_id==$brand->id ? 'selected="true"' : '' }}>{{ $brand->brand_name_en }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="text_mode text_mode_{{ $row->id }} tags_text_{{ $row->id }}" id="tags_text">{{ $row->_tags }}</div>
                                                    <textarea class="edit_mode edit_mode_{{ $row->id }}" type="text" name="_tags" id="_tags">{{ $row->_tags }}</textarea>
                                                </td>
                                                <td>
                                                    @php
                                                        $product_image_url = $row->product_image_url ? $row->product_image_url : asset('assets/images/dummy-product-image.jpg')
                                                    @endphp
                                                    <a href="javascript:void(0)" data-src="{{ $product_image_url }}" fk_product_id="{{ $row->id }}"
                                                        onclick="openImageModal(this)" style="max-width: 90%; height: 110px; display: block; background: #ffffff; border: 1px #dee2e6 solid; text-align: center;">
                                                        <img class="product_image_change_{{ $row->id }}" src="{{ $product_image_url }}" height="100">
                                                    </a>
                                                    <input class="edit_mode edit_mode_{{ $row->id }}" type="file" name="image" id="image"/>
                                                </td>
                                                <td>
                                                    <div class="text_mode text_mode_{{ $row->id }} country_code_text_{{ $row->id }}" id="country_code_text">{{ $row->country_code }}</div>
                                                    <textarea class="edit_mode edit_mode_{{ $row->id }}" type="text" name="country_code" id="country_code">{{ $row->country_code }}</textarea>
                                                </td>
                                                <td>
                                                    @php
                                                        $country_flag_image_url = $row->country_icon ? $row->country_icon : asset('assets/images/dummy-product-image.jpg')
                                                    @endphp
                                                    <img class="country_icon_change_{{ $row->id }}" src="{{ $country_flag_image_url }}" height="40">
                                                    <input class="edit_mode edit_mode_{{ $row->id }}" type="file" name="country_icon" id="country_icon"/>
                                                </td>
                                            </form>
                                            <td>
                                                <button title="Edit" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn product_edit product_edit_{{ $row->id }} btn btn-primary waves-effect"><i class="icon-pencil"></i> Edit</button>
                                                <button title="Saving" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn product_saving product_saving_{{ $row->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                <button title="Save" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn product_save product_save_{{ $row->id }} btn btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                            </td>
                                        </tr>
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
    <div class="modal fade imageModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0">Product Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="imageForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <img style="max-width: 100%;" class="product_image" src="{{ asset('assets/images/dummy-product-image.jpg') }}" alt="Product Image"/>
                            </div>
                        </div>
                        {{-- <input type="hidden" name="fk_product_id"> --}}
                        {{-- <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#searchCompanyForm').on('submit',function(e){
            e.preventDefault();
            let company_id = $('#company_id').val();
            let filter = $('#filter').val();
            let filter_category = $('#filter_category').val();
            window.location.href = '/admin/products/edit_multiple/'+company_id+'?filter='+filter+'&filter_category='+filter_category; 
        });
        $('.product_edit').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            
            $('.edit_mode_'+id).show();
            $('.text_mode_'+id).hide();
            $('.product_edit_'+id).hide();
            $('.product_saving_'+id).hide();
            $('.product_save_'+id).show();
        });
        $('.product_save').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            $('.product_edit_'+id).hide();
            $('.product_saving_'+id).show();
            $('.product_save_'+id).hide();
            if (id) {
                let form = $("#form_"+id);
                let token = "{{ csrf_token() }}";
                // let formData = form.serialize();
                // formData += formData+"&_token="+token;
                // formData += formData+"&id="+id;
                console.log(form);
                let formData = new FormData(form[0]);
                // var form_data = new FormData(document.getElementById("form_"+id));
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/products/product_edit_multiple_save') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            // alert(data.message);
                            // location.reload();
                            $('.edit_mode_'+id).hide();
                            $('.text_mode_'+id).show();
                            $('.product_edit_'+id).show();
                            $('.product_saving_'+id).hide();
                            $('.product_save_'+id).hide();
                            if (data.data) {
                                let product = data.data;
                                let brand_name = product.fk_brand_id!=0 ? product.brand_name+' ('+product.fk_brand_id+')' : 'N/A';
                                $('.product_name_en_text_'+id).html(product.product_name_en);
                                $('.product_name_ar_text_'+id).html(product.product_name_ar);
                                $('.unit_text_'+id).html(product.unit);
                                $('.category_text_'+id).html(product.sub_category_name+' ('+product.fk_sub_category_id+')');
                                $('.brand_text_'+id).html(brand_name);
                                $('.tags_text_'+id).html(product._tags);
                                $('.product_image_change_'+id).prop('src', product.product_image_url);
                                $('.country_code_text_'+id).html(product.country_code);
                                $('.country_icon_change_'+id).prop('src', product.country_icon);
                            }
                        } else {
                            alert(data.message);
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
        });
        function openImageModal(obj) {
            let product_id = $(obj).attr('fk_product_id');
            let product_image_url = $(obj).data('src');
            $('.product_image').prop('src', product_image_url);
            $(".imageModal").modal('show');
        }
    </script>
@endsection
