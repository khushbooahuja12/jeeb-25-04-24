@extends('admin.layouts.dashboard_layout_for_fleet_panel')
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
        display: block;
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
                    <h4 class="page-title">
                        Products Stock Update - {{ $store ? $store->name.', '.$store->company_name : '' }}
                    </h4>
                    <a class="btn btn-primary" href="{{route('fleet-orders',$store->id)}}">Orders</a>
                    <a class="btn btn-success" href="{{route('fleet-active-orders',$store->id)}}">Active Orders</a>
                    <a class="btn btn-secondary" href="{{route('fleet-drivers',$store->id)}}">Drivers</a>
                    <a class="btn btn-secondary" href="{{route('fleet-storekeepers',$store->id)}}">Storekeepers</a>
                    <a class="btn btn-warning" href="{{route('fleet-products-panel',$store->id)}}">Products</a>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="{{route('fleet')}}">Stores</a></li>
                        <li class="breadcrumb-item active">{{$store->name}}, {{$store->company_name}}</li>
                    </ol>
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{route('fleet')}}">Go back to all stores</a>
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
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered">
                            {{-- <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th style="min-width: 180px;">Product Name (EN)</th>
                                    <th style="min-width: 180px;">Product Name (AR)</th>
                                    <th>Quantity </th>
                                    <th>Category</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead> --}}
                            <tbody>
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                        <form name="form_<?= $row->id ?>" id="form_<?= $row->id ?>" enctype="multipart/form-data">
                                        <tr class="product<?= $row->id ?>">
                                            <td>{{ $starting_no + $key + 1 }}</td>
                                            {{-- <td>{{ !empty($row->getCompany) ? $row->getCompany->name : 'N/A' }}</td>
                                            <td>({{ $row->id }})</td> --}}
                                            <td>
                                                <div class="card m-b-30">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-lg-2">
                                                                <div class="" id="">ID: {{ $row->id }}</div>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <div class="product_name_en_text_{{ $row->id }}" id="product_name_en_text">{{ $row->product_name_en }}</div>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <div class="product_name_ar_text_{{ $row->id }}" id="product_name_ar_text">{{ $row->product_name_ar }}</div>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <div class="unit_text_{{ $row->id }}" id="unit_text">{{ $row->unit }}</div>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <div class="" id=""><strong>Store 1</strong> Price: <span class="store1_distributor_price_text_{{ $row->id }}" id="store1_distributor_price_text">{{ $row->store1_distributor_price }}</span></div>
                                                                <div class="" id="">Selling Price: <span class="store1_price_text_{{ $row->id }}" id="store1_price_text">{{ $row->store1_price }}</span></div>
                                                                <div class="" id="">Stock: <span class="store1_text_{{ $row->id }}" id="store1_text">{{ $row->store1 }}</span></div>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                @php
                                                                $product_subcategory = $row->getProductSubCategory; 
                                                                $product_subcategory_id = $product_subcategory ? $product_subcategory->id : false;
                                                                $product_subcategory_name = $product_subcategory ? $product_subcategory->category_name_en : 'N/A';
                                                                @endphp
                                                                <div class="category_text_{{ $row->id }}" id="category_text">{{ $product_subcategory_name }} ({{ $product_subcategory_id }})</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <button title="Edit" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn product_edit product_edit_{{ $row->id }} btn btn-primary waves-effect"><i class="icon-pencil"></i> Edit</button>
                                            </td>
                                        </tr>
                                        <tr class="product_edit_<?= $row->id ?> edit_mode edit_mode_{{ $row->id }}">
                                            <td colspan="3">
                                                <div class="card m-b-30">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label>Store 1 Price</label><br/>
                                                                    <input type="text" class="form-control" type="text" name="store1_distributor_price" id="store1_distributor_price numericOnly" value="{{ $row->store1_distributor_price }}"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label>Store 1 Stock</label><br/>
                                                                    <input type="number" class="form-control" type="text" name="store1" id="store1 numericOnly" value="{{ $row->store1 }}"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label>Product Name (En)</label><br/>
                                                                    <textarea class="form-control" type="text" name="product_name_en" id="product_name_en">{{ $row->product_name_en }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label>Product Name (Ar)</label><br/>
                                                                    <textarea class="form-control" type="text" name="product_name_ar" id="product_name_ar">{{ $row->product_name_ar }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label>Unit</label><br/>
                                                                    <input class="form-control" type="text" name="unit" id="unit" value="{{ $row->unit }}"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <label>Product Image</label><br/>
                                                                    @php
                                                                        $product_image_url = $row->product_image_url ? $row->product_image_url : asset('assets/images/dummy-product-image.jpg')
                                                                    @endphp
                                                                    <a href="javascript:void(0)" data-src="{{ $product_image_url }}" fk_product_id="{{ $row->id }}"
                                                                        onclick="openImageModal(this)" style="padding: 5px 10px; height: 110px; display: block; background: #ffffff; border: 1px #dee2e6 solid; text-align: left;">
                                                                        <img class="product_image_change_{{ $row->id }}" src="{{ $product_image_url }}" height="100">
                                                                    </a>
                                                                    <input class="form-control" type="file" name="image" id="image"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <button title="Saving" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn product_saving product_saving_{{ $row->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                                    <button title="Save" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn product_save product_save_{{ $row->id }} btn btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        </form>
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
            // alert(company_id);
            if (filter!='') {
                window.location.href = '/admin/products/edit_multiple/'+company_id+'?filter='+filter; 
            } else {
                window.location.href = '/admin/products/edit_multiple/'+company_id; 
            }
        });
        $('.product_edit').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            $('.edit_mode_'+id).toggle();
        });
        $('.product_save').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
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
                    url: "<?= url('admin/fleet/store/14/products/update_stock_multiple_save') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            // alert(data.message);
                            // location.reload();
                            $('.product_saving_'+id).hide();
                            $('.product_save_'+id).show();
                            if (data.data) {
                                let product = data.data;
                                $('.product_name_en_text_'+id).html(product.product_name_en);
                                $('.product_name_ar_text_'+id).html(product.product_name_ar);
                                $('.unit_text_'+id).html(product.unit);
                                $('.category_text_'+id).html(product.sub_category_name+' ('+product.fk_sub_category_id+')');
                                $('.unit_text_'+id).html(product.unit);
                                $('.store1_distributor_price_text_'+id).html(product.store1_distributor_price);
                                $('.store1_price_text_'+id).html(product.store1_price);
                                $('.store1_text_'+id).html(product.store1);
                                $('.product_image_change_'+id).prop('src', product.product_image_url);
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
