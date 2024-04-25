@extends('admin.layouts.dashboard_layout_for_product_admin')
@section('content')
<style>
    .text_mode {
        display: block;
    }
    .edit_mode {
        display: none;
    }
    .product_btn {
        width: 70px;
        height: 55px;
    }
    .pm-hidden-row {
    padding: 0 !important;
    }
    .pm-loading-shimmer {
        animation: shimmer 2s infinite linear;
        background: linear-gradient(to right, #eff1f3 4%, #e2e2e2 25%, #eff1f3 36%);
        background-size: 1000px 100%;
    }

    .product_offerlist_panel {
        background: #e9ecef;
    }
    .offer_title {
        background: #354558;
        color: #ffffff;
        margin-bottom: 20px;
        padding: 10px;
    }
    .offer_message {
        background: #4bd352;
        color: #ffffff;
        margin-bottom: 20px;
        padding: 10px;
    }
    .offer_error {
        background: #f77272;
        color: #ffffff;
        margin-bottom: 20px;
        padding: 10px;
    }
    .offer_loading {
        background: url("{{ asset('assets_v3/img/loader.gif') }}") center no-repeat;
        width: 100%;
        height: 35px;
        color: #ffffff;
        margin-bottom: 20px;
        padding: 10px;
    }
    .product_select_con {
        position: relative;
        display: flex;
        margin-bottom: 20px;
    }
    .product_select {
        border: 1px #eeeeee solid;
        background: #ffffff;
        text-align: center;
        padding: 10px;
        height: 100%;
        flex-grow: 1;
    }
    .product_select img {
        display: block;
        margin: 5px auto 20px;
        max-width: 85%; 
        height: 120px;
    }
    .product_select p {
        display: block;
        margin: 5px auto;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Offers
                        {{-- <a href="<?= url('admin/base_products/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a> --}}
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/base_products') ?>">Products</a></li>
                        <li class="breadcrumb-item active">Offers</li>
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
                    <div class="card-body">
                        @if (!$offer_term)
                        Please select the offer type and date
                        @endif
                        <form class="" method="GET">
                            <div class="form-inline">
                                <div class="form-group mb-2">
                                    <select name="offer_type" class="form-control">
                                        <option value=""></option>
                                        <option value="daily" <?= isset($_GET['offer_type']) && $_GET['offer_type'] == 'daily' ? 'selected' : '' ?>>Daily</option>
                                        <option value="weekly" <?= isset($_GET['offer_type']) && $_GET['offer_type'] == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <input type="text" name="offer_date" class="form-control offer_date"
                                        autocomplete="off"
                                        value="<?= isset($_GET['offer_date']) && $_GET['offer_date'] != '' ? $_GET['offer_date'] : '' ?>"
                                        placeholder="mm_dd_yyyy" readonly>
                                </div>
                                <button type="submit" class="btn btn-dark mb-2">Select</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if ($offer_term)
        <div class="row">
            <div class="col-md-6">
                <div class="card m-b-30">
                    <div class="card-body product_offerlist_panel">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="offer_title">
                                    <h2>Offer Products List</h2>
                                    Filter by: {{$offer_term}}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="offer_message_box">
                                </div>
                            </div>
                            @if ($products)
                                @foreach ($products as $product)
                                <div class="col-md-6 product_select_con">
                                    <div class="product_select">
                                        <form method="POST" id="form_remove_{{$product['id']}}">
                                            @if (isset($product['product_image_url']) && $product['product_image_url']!='')
                                                <img src="{{ $product['product_image_url'] }}"/>
                                            @else
                                                <img src="https://jeeb.tech/assets/images/dummy-product-image.jpg"/>
                                            @endif
                                            <h4>{{ isset($product['product_name_en']) ? $product['product_name_en'] : '' }}</h4>
                                            <p>{{ isset($product['product_name_ar']) ? $product['product_name_ar'] : '' }}</p>
                                            <p>Regular price: {{isset($product['base_price']) ? $product['base_price'] : 0.00}}</p>
                                            <p>Selling price: {{isset($product['product_store_price']) ? $product['product_store_price'] : 0.00}}</p>
                                            <p>Stock: {{ isset($product['product_store_stock']) ? $product['product_store_stock'] : 0}}</p>
                                            <p>
                                                <input type="hidden" name="offer_term" value="{{$offer_term}}"/>
                                                <button type="submit" id="{{$product['id']}}" class="btn btn-dark mb-2 product_remove">Remove from offer list</button>
                                            </p>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card m-b-30">
                    <div class="card-body product_select_panel">
                        <div class="row">
                            <div class="col-md-12">
                                <form class="" method="GET">
                                    <div class="form-inline">
                                        <div class="form-group mb-2">
                                            <input type="hidden" name="offer_type" value="{{ $_GET['offer_type'] }}">
                                            <input type="hidden" name="offer_date" value="{{ $_GET['offer_date'] }}">
                                            <input type="text" class="form-control" name="filter" placeholder="Search Product"
                                                value="{{ $filter }}">
                                        </div>
                                        <button type="submit" class="btn btn-dark mb-2">Search</button>
                                    </div>
                                </form>
                            </div>
                            @if ($filter_products)
                                @foreach ($filter_products as $filter_product)
                                <div class="col-md-6 product_select_con">
                                    <div class="product_select">
                                        <form method="POST" id="form_save_{{$filter_product['id']}}">
                                            @if (isset($filter_product['product_image_url']) && $filter_product['product_image_url']!='')
                                                <img src="{{ $filter_product['product_image_url'] }}"/>
                                            @else
                                                <img src="https://jeeb.tech/assets/images/dummy-product-image.jpg"/>
                                            @endif
                                            <h4>{{ isset($filter_product['product_name_en']) ? $filter_product['product_name_en'] : '' }}</h4>
                                            <p>{{ isset($filter_product['product_name_ar']) ? $filter_product['product_name_ar'] : '' }}</p>
                                            <p>Regular price: {{isset($filter_product['base_price']) ? $filter_product['base_price'] : 0.00}}</p>
                                            <p>Selling price: {{isset($filter_product['product_store_price']) ? $filter_product['product_store_price'] : 0.00}}</p>
                                            <p>Stock: {{ isset($filter_product['product_store_stock']) ? $filter_product['product_store_stock'] : 0}}</p>
                                            <p>
                                                <input type="hidden" name="offer_term" value="{{$offer_term}}"/>
                                                <button type="submit" id="{{$filter_product['id']}}" class="btn btn-dark mb-2 product_save">Add to offer list</button>
                                            </p>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <script>
        $('.product_edit').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            
            $('.edit_mode_'+id).show();
            $('.text_mode_'+id).hide();
            $('.product_edit_'+id).hide();
            $('.product_save_'+id).show();
        });

        $('body').on('click','.product_save',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            $('.offer_message_box').html('');
            $('.offer_message_box').removeClass('offer_message');
            $('.offer_message_box').removeClass('offer_error');
            $('.offer_message_box').addClass('offer_loading');
            if (id) {
                let form = $("#form_save_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/base_products/product_edit_offer_save') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend:function(){
                        $('#product'+id).addClass('pm-loading-shimmer');
                    },
                    success: function(data) {
                        alert(data.message);
                        if (data.error_code == "200") {
                            console.log(data);
                            $('.offer_message_box').html(data.message);
                            $('.offer_message_box').addClass('offer_message');
                            $('.offer_message_box').removeClass('offer_error');
                            $('.offer_message_box').removeClass('offer_loading');
                            if (data.data) {
                                let product = data.data;
                                let product_html = '';
                                    product_html += '<div class="col-md-6 product_select_con">';
                                    product_html += '<div class="product_select"><form method="POST" id="form_remove_'+product.id+'">';
                                    product_html += '<img src="'+product.product_image_url+'"/>';
                                    product_html += '<h4>'+product.product_name_en+'</h4>';
                                    product_html += '<p>'+product.product_name_ar+'</p>';
                                    product_html += '<p>Regular price: '+product.base_price+'</p>';
                                    product_html += '<p>Selling price: '+product.product_store_price+'</p>';
                                    product_html += '<p>Stock: '+product.product_store_stock+'</p>';
                                    product_html += '<p>';
                                    product_html += '<input type="hidden" name="offer_term" value="{{$offer_term}}"/>';
                                    product_html += '<button type="submit" id="'+product.id+'" class="btn btn-dark mb-2 product_remove">Remove from offer list</button>';
                                    product_html += '</p>';
                                    product_html += '</form>';
                                    product_html += '</div>';
                                    product_html += '</div>';
                                $('.product_offerlist_panel .row').append(product_html);
                                console.log(product);
                            }
                        } else {
                            $('.offer_message_box').html(data.message);
                            $('.offer_message_box').addClass('offer_error');
                            $('.offer_message_box').removeClass('offer_message');
                            $('.offer_message_box').removeClass('offer_loading');
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
        });
        $('body').on('click','.product_remove',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            $('.offer_message_box').html('');
            $('.offer_message_box').removeClass('offer_message');
            $('.offer_message_box').addClass('offer_loading');
            if (id) {
                let form = $("#form_remove_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                var offer_product = $(this).closest('.product_select_con');
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/base_products/product_edit_offer_remove') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend:function(){
                        $('#product'+id).addClass('pm-loading-shimmer');
                    },
                    success: function(data) {
                        alert(data.message);
                        if (data.error_code == "200") {
                            console.log(data);
                            $('.offer_message_box').html(data.message);
                            $('.offer_message_box').addClass('offer_message');
                            $('.offer_message_box').removeClass('offer_error');
                            $('.offer_message_box').removeClass('offer_loading');
                            if (data.data) {
                                let product = data.data;
                                offer_product.remove();
                                console.log(product);
                            }
                        } else {
                            $('.offer_message_box').html(data.message);
                            $('.offer_message_box').addClass('offer_error');
                            $('.offer_message_box').removeClass('offer_message');
                            $('.offer_message_box').removeClass('offer_loading');
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
        });
        $('input.offer_date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM_DD_YYYY'));

            $.ajax({
                url: '<?= url('admin/orders/get_slots') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    date: $(this).val()
                },
                cache: false
            }).done(function(response) {
                if (response.status_code == 200) {
                    var html = '<option disabled selected>Select Slot</option>'
                    $.each(response.slots, function(i, v) {
                        html += '<option value=' + v.slot + '>' + v.slot + '</option>';
                    });
                } else {
                    var html = '<option disabled selected>No slot found</option>'
                }
                $("#slots").html(html);
            });
        });
    </script>
@endsection
