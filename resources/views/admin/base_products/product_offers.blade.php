@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Base product offers</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item">Daily / Weekly Offers</li>
                        <li class="breadcrumb-item active">Offer Options</li>
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
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <select class="form-control" name="fk_offer_option_id">
                                    <option value="0" selected>--select--</option>
                                    @foreach ($offer_options as $key => $value)
                                        <option value="{{ $value->id }}" {{ $value->id == $fk_offer_option_id ? 'selected' : '' }}>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control" name="filter" placeholder="Search Products"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>@sortablelink('name', 'Product Name')</th>
                                    <th>Store Price</th>
                                    <th>Base Price</th>
                                    <th>Selling Price</th>
                                    <th>Stock</th>
                                    <th>Offer options</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                    <form method="POST" action="{{ route('admin.base_products.update_product_offer', ['id' => $row->id]) }}">
                                        @csrf
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td >{{ $row->product_name_en }} ({{ $row->unit }})</td>
                                            <td >{{ $row->product_distributor_price }}</td>
                                            <td >{{ $row->base_price }}</td>
                                            <td >{{ $row->product_store_price }}</td>
                                            <td >{{ $row->product_store_stock }}</td>
                                            <td style="width:50%">
                                                <select class="form-control select2" name="fk_offer_option_id">
                                                    <option value="0" selected>--select--</option>
                                                    @foreach ($offer_options as $key => $value)
                                                        <option value="{{ $value->id }}" {{ $value->id == $row->fk_offer_option_id ? 'selected' : '' }}>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</option>
                                                    @endforeach
                                                    
                                                </select>
                                            </td>
                                            <td>
                                                <button type="submit" class="btn-sm btn-primary" title="update" href=""> Update</button>&ensp;
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
    <script>
        function checkStatus(obj) {
            var id = $(obj).attr("id");

            var checked = $(obj).is(':checked');
            if (checked == true) {
                var status = 1;
                var statustext = 'activate';
            } else {
                var status = 0;
                var statustext = 'block';
            }

            if (confirm("Are you sure, you want to " + statustext + " this offer option ?")) {
                $.ajax({
                    url: '<?= url('admin/change_status') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        method: 'changeOfferOptionStatus',
                        status: status,
                        id: id
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Status updated successfully</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);

                        $('.vendor' + id).find("p").text(response.result.status);
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Some error found</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }
                });
            } else {
                if (status == 0) {
                    $(".set_status" + id).prop('checked', true);
                } else {
                    $(".set_status" + id).prop('checked', false);
                }
            }
        }
    </script>
@endsection
