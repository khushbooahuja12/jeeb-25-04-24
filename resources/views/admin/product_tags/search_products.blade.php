@extends('admin.layouts.dashboard_layout')
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

    .product_select_con_panel_container {
        overflow-x: scroll;
        width: 100%;
    }
    .product_select_con_panel {
        width: 7200px;
    }
    .product_select_con {
        position: relative;
        display: flex;
        margin-bottom: 20px;
        float: left;
        min-width: 150px;
        max-width: 350px;
        word-break: break-all;
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
    .tags_arrange_panel {
        display: none;
    }
    .list-group-item {position: relative;}
    .remove_tag {
        position: absolute;
        background: #f77272;
        color: #ffffff;
        border-radius: 3px;
        float: right;
        padding: 2px;
        line-height: 12px;
        font-size: 12px;
        top: 10px;
        right: 10px;
    }
    .remove_tag:hover {
        background: #f97474;
        color: #ffffff;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Search Products</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Search Products</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body" style="position: relative; table-layout: fixed;">
                        <form action="" method="GET">
                            Search By Products Name:
                            @if($fk_product_id)
                                    @php $base_product = \App\Model\BaseProduct::find($fk_product_id); @endphp
                                    <select class="form-control search_base_products_ajax" name="fk_product_id" id="fk_product_id">
                                        <option value="" selected>--Select--</option>
                                        <option value="{{ $base_product->id }}" selected>{{ $base_product->product_name_en }}</option>
                                    </select>
                            @else
                                <select class="form-control search_base_products_ajax" name="fk_product_id" id="fk_product_id">
                                    <option value="" selected>--Select--</option>
                                </select>
                            @endif
                            <br/><br/>
                            Search Tags:
                            <input type="text" name="search_tags" class="form-control" value="{{ $search_tags_str }}"/>
                            (Limited to 20 search tags maximum at a time)
                            <br/><br/>
                            <input type="checkbox" name="without_stock" class="" value="1" {{ $without_stock ? "checked" : "" }}/> Without stock
                            <br/><br/>
                            <button type="submit"
                                class="btn btn-primary waves-effect waves-light button-prevent-multiple-submits">
                                Submit
                            </button>
                        </form>
                        <br/>
                        History of Search Tags: <br/>
                        <p class="old_search_tags">
                        @if ($old_search_tags)
                            @foreach ($old_search_tags as $old_tag)
                                {{ $old_tag->tag }},
                            @endforeach
                        @endif
                        </p>
                        <button type="button" id="1"
                            class="btn btn-primary waves-effect waves-light load_more">
                            Load more tags in history
                        </button>
                        <br/>
                        <br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%; table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th width="5%">S.No.</th>
                                    <th>Tag</th>
                                    <th width="85%">Results</th>
                                    {{-- <th class="nosort">Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @if ($search_tags)
                                    @foreach ($search_tags as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row['tag'] }}</td>
                                            <td>
                                                <div class="product_select_con_panel_container">
                                                    <div class="product_select_con_panel">
                                                        @if ($row['products'])
                                                            @foreach ($row['products'] as $product)
                                                                <div class="product_select_con">
                                                                    <div class="product_select">
                                                                        <form method="POST" action="" id="form_remove_{{$product['id']}}" class="form_remove form_remove_{{$product['id']}}">
                                                                            @csrf
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
                                                                            <p>Tags: <br/>
                                                                                {{ isset($product['_tags']) ? implode(',',$product['_tags']) : '' }}
                                                                            </p>
                                                                            <p>
                                                                                <input type="hidden" name="tag" value="{{$row['tag']}}"/>
                                                                                <input type="hidden" name="id" value="{{$product['id']}}"/>
                                                                                <button type="button" id="{{$product['id']}}" class="btn btn-dark mb-2 product_remove">Add Empty Tag</button>
                                                                                <button type="button" id="{{$product['id']}}" class="btn btn-dark mb-2 product_add">Remove Empty Tag</button>
                                                                            </p>
                                                                        </form>
                                                                        <form method="POST" action="" id="form_tags_arrange_{{$product['id']}}" class="form_tags_arrange form_tags_arrange_{{$product['id']}}">
                                                                            @csrf
                                                                            <input type="hidden" name="id" value="{{$product['id']}}"/>
                                                                            <button type="button" id="{{$product['id']}}" class="btn btn-dark mb-2 tags_arrange" style="width: 100%;">Edit Tags</button>
                                                                            <div class="tags_arrange_panel tags_arrange_panel_{{$product['id']}}">
                                                                                <button type="button" id="{{$product['id']}}" class="btn btn-danger mb-2 tags_arrange_save" style="width: 100%;">Save Edited Tags</button>
                                                                                <select name="new_tag" data-title="New Tags" class="form-control new_tag new_tag_{{$product['id']}}" id="{{$product['id']}}" style="width: 100%">
                                                                                    <option value="">Select Tags</option>
                                                                                </select>
                                                                                <p>
                                                                                    <div class="list-group col listTags listTags_{{$product['id']}}" style="border: 3px dotted rgb(0, 0, 0); padding: 50px;">
                                                                                    @foreach ($product['_tags'] as $key => $tag)
                                                                                        @php
                                                                                            if (str_contains($tag, 'App\Model')) { 
                                                                                                continue;
                                                                                            }
                                                                                        @endphp
                                                                                        <div class="list-group-item" id="{{ $tag }}"><h6>{{ $tag }}</h6><input type="hidden" name="arranged_tags[]" value="{{ $tag }}"><a href="javascript:void(0);" class="remove_tag">X</a></div>
                                                                                    @endforeach
                                                                                    </div>
                                                                                </p>
                                                                            </div>
                                                                        </form>
                                                                        <br>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- <td>                                                
                                                <a href="javascript:void(0);" class="btn btn-primary">Update</a>
                                            </td> --}}
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <script>
        // Edit tags
        $( ".listTags" ).sortable({
            connectWith: "#listLeft",
        });
        $(document).on('click','.tags_arrange',function(){
            let id = $(this).attr('id');
            $('.tags_arrange_panel_'+id).toggle();
        });
        // Remove tags from edit panel
        $(document).on('click','.remove_tag',function(){
            $(this).closest('.list-group-item').remove();
        });
        // Save rearranged tags
        $(document).on('click','.tags_arrange_save',function(){
            let id = $(this).attr('id');
            let formData  = $('.form_tags_arrange_'+id).serialize();
            $.ajax({
                url: '<?= url('admin/base_products/tags_arrange_save') ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                console.log(response);
                alert(response.message);
            });
        });
        // Remove empty tag
        $(document).on('click','.product_remove',function(){
            let id = $(this).attr('id');
            let formData  = $('.form_remove_'+id).serialize();
            formData += '&empty_add=1'
            $.ajax({
                url: '<?= url('admin/base_products/search_products_update') ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                console.log(response);
                alert(response.message);
            });
        });
        // Add empty tag
        $(document).on('click','.product_add',function(){
            let id = $(this).attr('id');
            let formData  = $('.form_remove_'+id).serialize();
            formData += '&empty_add=0'
            $.ajax({
                url: '<?= url('admin/base_products/search_products_update') ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                console.log(response);
                alert(response.message);
            });
        });
        // Load more history tags
        $(document).on('click','.load_more',function(){
            var load_more_btn = $(this);
            let id = parseInt(load_more_btn.attr('id'));
            $.ajax({
                url: '<?= url('admin/base_products/search_tags_load_more') ?>',
                type: 'POST',
                data: {'id':id},
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                console.log(response);
                if (response.error_code == 200) {
                    // location.reload();
                    $('.old_search_tags').append(response.data);
                    load_more_btn.attr('id',id+1);
                    // alert(response.data);
                } else {
                    alert('failed');
                }
            });
        });
        // Load new tags in select2
        $(document).on('change','.new_tag',function(){
            let id = $(this).attr('id');
            let value = $(this).find('option:selected').text();
            var list_panel = $('.listTags_'+id);
            var tag_arr = value.split(',');
            tag_arr.forEach(tag => {
                if (tag!='') {
                    let tag_element = '<div class="list-group-item" id="'+tag+'"><h6>'+tag+'</h6><input type="hidden" name="arranged_tags[]" value="'+tag+'"><a href="javascript:void(0);" class="remove_tag">X</a></div>';
                    list_panel.append(tag_element);
                }
            });
        });
    </script>
@endsection
