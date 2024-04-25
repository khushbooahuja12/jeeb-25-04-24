@extends('admin.layouts.dashboard_layout_for_stock_update')
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
                        Product Sale Update - {{ $store ? $store->name.', '.$store->company_name : '' }}
                    </h4>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-md-12">
                  <nav>
                    <div class="nav nav-pills" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link " href="{{route('fleet-stock-update-base-products-panel',['id'=>$store->id,'category' => 0])}}" >Update Stock All Products</a>
                        <a class="nav-item nav-link " href="{{route('fleet-stock-update-store-update',['id'=>$store->id])}}" >Update Stock</a>
                        <a class="nav-item nav-link " href="{{route('fleet-stock-update-sale-update',['id'=>$store->id])}}">Update Sale</a>
                    </div>
                  </nav>
                  <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-vegetables-fruits" role="tabpanel" aria-labelledby="nav-vegetables-fruits-tab">
                        <div class="card m-b-30">
                            <div class="card-body">
                                <form class="form-inline" id="findProduct" method="GET" action="">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="barcode" id="barcode" placeholder="Barcode"
                                            value="{{ $barcode }}" autofocus >
                                    </div>
                                    <button type="submit" class="btn btn-dark">Go</button>
                                </form>
                                @if ($product)
                                    <hr/>
                                    <form name="form_<?= $product->product_store_id ?>" id="form_<?= $product->product_store_id ?>" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="card m-b-30">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-2">
                                                        <div class="" id="">ID: {{ $product->id }}</div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="product_name_en_text_{{ $product->product_store_id }}" id="product_name_en_text">{{ $product->product_name_en }}</div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="product_name_ar_text_{{ $product->product_store_id }}" id="product_name_ar_text">{{ $product->product_name_ar }}</div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="unit_text_{{ $product->product_store_id }}" id="unit_text">{{ $product->product_store_unit }}</div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="" id=""><strong>Store 1</strong> Price: <span class="store1_distributor_price_text_{{ $product->product_store_id }}" id="store1_distributor_price_text">{{ $product->product_store_distributor_price }}</span></div>
                                                        <div class="" id="">Stock: <span class="store1_text_{{ $product->product_store_id }}" id="store1_text">{{ $product->product_store_product_stock }}</span></div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        @php
                                                        $product_subcategory = $product->getProductSubCategory; 
                                                        $product_subcategory_id = $product->category_id ? $product->category_id : false;
                                                        $product_subcategory_name = $product->category_name_en ? $product->category_name_en : 'N/A';
                                                        @endphp
                                                        <div class="category_text_{{ $product->product_store_id }}" id="category_text">{{ $product_subcategory_name }} ({{ $product_subcategory_id }})</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label>Selling Quantity</label><br/>
                                                        <input type="number" class="form-control" type="text" name="product_store_product_stock" id="store1 numericOnly" min="0" value="1"/>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <button title="Saving" href="javascript:void(0);" id="{{ $product->product_store_id }}" class="product_btn product_saving product_saving_{{ $product->product_store_id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                        <button title="Save" href="javascript:void(0);" id="{{ $product->product_store_id }}" class="product_btn product_save product_save_{{ $product->product_store_id }} btn btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </form>
                                @endif
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
        $(function() {
            $("#barcode").select();
        });
        $(document).on('click', '#barcode', function() {
            $(this).select();
        });
        $(document).on('change', '#barcode', function() {
            $("#findProduct").submit();
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
                    url: "<?= url('admin/stock_update/store/'.$store->id.'/base_products/update_sale_save') ?>",
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
                                $('.store1_distributor_price_text_'+id).html(product.product_store_distributor_price);
                                $('.store1_price_text_'+id).html(product.product_store_product_price);
                                $('.store1_text_'+id).html(product.product_store_product_stock);
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
    </script>
@endsection
