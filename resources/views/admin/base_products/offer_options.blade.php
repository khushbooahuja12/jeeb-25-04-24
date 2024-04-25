@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Offer Options
                        <a href="<?= url('admin/base_products/create_offer_option') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Offer Options Name"
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
                                    <th>@sortablelink('name', 'Name')</th>
                                    <th>Base Price Percentage (%)</th>
                                    <th>Dicount Percentage (%)</th>
                                    <th>Status (Active)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($offer_options)
                                    @foreach ($offer_options as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->base_price_percentage}}</td>
                                            <td>{{ $row->discount_percentage}}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input set_status<?= $row->id ?>"
                                                            type="checkbox" value="{{ $row->status }}"
                                                            <?= $row->status == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkStatus(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <a class="btn-sm btn-primary" title="edit"
                                                    href="<?= url('admin/base_products/edit_offer_option/' . base64url_encode($row->id)) ?>"> Edit
                                                </a>&ensp;
                                                <a class="btn-sm btn-primary" title="edit"
                                                    href="<?= url('admin/base_products/product_offers/?fk_offer_option_id=' .$row->id) ?>"> View Products
                                                </a>&ensp;
                                                {{-- <a class="btn-sm btn-danger" title="delete" onclick="return confirm('Are you sure you want to deleted this offer option')"
                                                    href="<?= url('admin/base_products/delete_offer_option/' . base64url_encode($row->id)) ?>"> Delete
                                                </a> --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $offer_options->count() }} of {{ $offer_options->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $offer_options->links() }}
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
