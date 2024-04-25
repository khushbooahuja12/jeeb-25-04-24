@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
.loader {
    background: url("{{ asset('assets_v3/img/loader.gif') }}") center no-repeat;
    width: 100%;
    height: 35px;
}
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Bulk upload stock</h4>
                    <a href="<?= url('admin/base_products/stock_update_stores/new_products') ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add New Products From Stock Update
                    </a>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/base_products/stock_update_stores') ?>">Stock Update</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        <div class="">
                            Total products stock found: {{ $counts['all_stocks_count']; }}<br/>
                            Total completed checking: <span id="stock_completed">{{ $counts['all_stocks_checked']; }}</span><br/>
                            Total completed matching: <span id="stock_matched">{{ $counts['all_stocks_matched']; }}</span><br/>
                            Total completed updating: <span id="stock_updated">{{ $counts['all_stocks_updated']; }}</span><br/>
                            Total created new products: <span id="stock_added_new_product">{{ $counts['all_stocks_added_new_product']; }}</span><br/>
                            <br/>
                            {{-- <button type="submit" class="btn btn-success waves-effect waves-light update_all_stocks">
                                Update all stocks
                            </button> --}}
                            <button type="submit" class="btn btn-success waves-effect waves-light update_all_stocks_in_server">
                                Update all stocks in server
                            </button>
                            <br clear="all"/><br/>
                            <div id="success_message"></div>
                        </div>
                        <br clear="all"/><br/>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th class="nosort">Itemcode</th>
                                    <th class="nosort">Barcode</th>
                                    <th class="nosort">Packing</th>
                                    {{-- <th class="nosort">Action</th> --}}
                                    <th class="nosort">RSP</th>
                                    <th class="nosort">Stock</th>
                                    <th class="nosort">Store No</th>
                                    <th class="nosort">Company ID</th>
                                    <th class="nosort">Checked</th>
                                    <th class="nosort">Matched</th>
                                    <th class="nosort">Matched base_product_store ID</th>
                                    <th class="nosort">Matched base_product ID</th>
                                    <th class="nosort">Updated</th>
                                    <th class="nosort">Added as new product</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $stock_array="";
                                @endphp
                                @if ($all_stocks)
                                    @foreach ($all_stocks as $key => $row)
                                        @php
                                        if ($row->checked == 0) {
                                            $stock_array .= $row->id.",";
                                        }
                                        @endphp
                                        <tr class="product<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->itemcode }}</td>
                                            <td>{{ $row->barcode }}</td>
                                            {{-- <td><button type="submit" id="{{ $row->id }}" class="btn btn-success waves-effect waves-light update_single">Update</button></td> --}}
                                            <td>{{ $row->packing }}</td>
                                            <td>{{ $row->rsp }}</td>
                                            <td>{{ $row->stock }}</td>
                                            <td>{{ $row->store_no }}</td>
                                            <td>{{ $row->company_id }}</td>
                                            <td><span id="stock_status_{{ $row->id }}">{{ ($row->checked==1) ? 'completed' : $row->checked }}</span></td>
                                            <td><span id="stock_status_matched_{{ $row->id }}">{{ $row->matched }}</span></td>
                                            <td><span id="stock_status_matched_bps_id_{{ $row->id }}">{{ $row->base_product_store_id }}</span></td>
                                            <td><span id="stock_status_matched_bp_id_{{ $row->id }}"><a href="/admin/base_products/show/{{ $row->base_product_id }}" target="_blank">{{ $row->base_product_id }}</a></span></td>
                                            <td><span id="stock_status_updated_{{ $row->id }}">{{ $row->updated }}</span></td>
                                            <td><span id="stock_status_added_new_product_{{ $row->id }}">{{ $row->added_new_product }}</span></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <input type="hidden" style="display: none;" id="stock_array" value="{{$stock_array}}">
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $all_stocks->count() }} of {{ $all_stocks->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $all_stocks->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert_msg"></div>
            </div>
        </div>
    </div>
    </div>
    <script>
        // Update all stocks in the server
        $('.update_all_stocks_in_server').on('click',function(e){
            e.preventDefault();
            $("#success_message").html('<div class="loader"></div>');
            
            let token = "{{ csrf_token() }}";
            let formData = new FormData();
            formData.append("_token",token);
            formData.append("id","{{ $batchId }}");
            formData.append("store_id","{{ $row->store_no }}");
            $.ajax({
            url: "/admin/base_products/post_stock_update_one_by_one_bulk_v3",
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function(response){
                console.log(response);
                $("#success_message").html('<div class="alert alert-primary text-center">'+response.message+'</div>');
                alert(response.message);
            },
            error: function (error) {
                console.log(error);
                $("#success_message").html('<div class="alert alert-danger text-center">Error occured while performing this action</div>');
            }
            });
        });
        // Update all stocks
        var stock_completed = $("#stock_completed").html();
        var stock_matched = $("#stock_matched").html();
        var stock_updated = $("#stock_updated").html();
        var stock_added_new_product = $("#stock_added_new_product").html();
        $('.update_all_stocks').on('click',function(e){
            e.preventDefault();
            $("#success_message").html('<div class="loader"></div>');
            let stocks = $("#stock_array").val();
            if (stocks!="") {
                let stocks_array = new Array();
                stocks_array = stocks.split(',');
                update_stock(stocks_array);
            }
        });
        const update_stock = (stocks_array, i=0) => {
            let form_id = stocks_array[i];
            let total_stocks = stocks_array.length;
            let form = $("#"+form_id);
            let stock_status = $("#stock_status_"+form_id).html();
            if (stock_status=="0") {

                let token = "{{ csrf_token() }}";
                let formData = new FormData();
                formData.append("_token",token);
                formData.append("id",form_id);
                formData.append("status",stock_status);
                $.ajax({
                url: "/admin/base_products/post_stock_update_one_by_one",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success: function(response){
                    console.log(response);
                    $("#stock_status_"+form_id).html(response.message);
                    if (response.message=='completed') {
                        stock_completed++;
                        $("#stock_completed").html(stock_completed);
                    }
                    if (response.matched==1) {
                        $("#stock_status_matched_"+form_id).html(response.matched);
                        stock_matched++;
                        $("#stock_matched").html(stock_matched);
                    }
                    if (response.updated==1) {
                        $("#stock_status_updated_"+form_id).html(response.updated);
                        stock_updated++;
                        $("#stock_updated").html(stock_updated);
                    }
                    if (response.added_new_product==1) {
                        $("#stock_status_added_new_product_"+form_id).html(response.added_new_product);
                        stock_added_new_product++;
                        $("#stock_added_new_product").html(stock_added_new_product);
                    }
                    if (response.base_product_store_id) {
                        $("#stock_status_matched_bps_id_"+form_id).html(response.base_product_store_id);
                    }
                    if (response.base_product_id) {
                        $("#stock_status_matched_bp_id_"+form_id).html(response.base_product_id);
                    }
                    if (i<total_stocks) {
                        update_stock(stocks_array, (i+1));
                    } else {
                        $("#success_message").html('<div class="alert alert-primary text-center">Completed all!</div>');
                    }
                },
                error: function (error) {
                    console.log(error);
                    $("#success_message").html('<div class="alert alert-danger text-center">Error occured while performing this action</div>');
                }
                });

            } else {

                if (i<total_stocks) {
                    update_stock(stocks_array, (i+1));
                }

            }
        }
        // Update single stock
        $('.update_single').on('click',function(e){
            e.preventDefault();
            $("#success_message").html('<div class="loader"></div>');
            let stock_id = $(this).attr("id");
            if (stock_id!="") {
                update_stock_single(stock_id);
            }
        });
        const update_stock_single = (stock_id) => {
            let form_id = stock_id;
            let form = $("#"+form_id);
            let stock_status = $("#stock_status_"+form_id).html();
            // Stock update
            let token = "{{ csrf_token() }}";
            let formData = new FormData();
            formData.append("_token",token);
            formData.append("id",form_id);
            formData.append("status",stock_status);
            $.ajax({
            url: "/admin/base_products/post_stock_update_one_by_one",
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function(response){
                console.log(response);
                $("#stock_status_"+form_id).html(response.message);
                if (response.message=='completed') {
                    stock_completed++;
                    $("#stock_completed").html(stock_completed);
                }
                if (response.matched==1) {
                    $("#stock_status_matched_"+form_id).html(response.matched);
                    stock_matched++;
                    $("#stock_matched").html(stock_matched);
                }
                if (response.updated==1) {
                    $("#stock_status_updated_"+form_id).html(response.updated);
                    stock_updated++;
                    $("#stock_updated").html(stock_updated);
                }
                if (response.added_new_product==1) {
                    $("#stock_status_added_new_product_"+form_id).html(response.added_new_product);
                    stock_added_new_product++;
                    $("#stock_added_new_product").html(stock_added_new_product);
                }
                if (response.base_product_store_id) {
                    $("#stock_status_matched_bps_id_"+form_id).html(response.base_product_store_id);
                }
                if (response.base_product_id) {
                    $("#stock_status_matched_bp_id_"+form_id).html(response.base_product_id);
                }
            },
            error: function (error) {
                console.log(error);
                $("#success_message").html('<div class="alert alert-danger text-center">Error occured while performing this action</div>');
            }
            });
        }
    </script>
@endsection
