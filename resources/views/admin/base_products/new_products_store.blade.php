@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">New products store
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
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Product Name"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered accordion">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('product_name_en', 'Product Name')</th>
                                    <th>Store </th>
                                    <th>Itemcode</th>
                                    <th>Barcode</th>
                                    <th>Store Price</th>
                                    <th>Stock</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($products)
                                @foreach ($products as $key => $row)
                                <tr class="product{{ $row->id }}">
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ $row->store_company_name }}-{{ $row->store_name }}</td>
                                    <td>{{ $row->itemcode }}</td>
                                    <td>{{ $row->barcode }}</td>
                                    <td>{{ $row->distributor_price }}</td>
                                    <td>{{ $row->stock }}</td>
                                    <td><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="allow_margin" id="product_allow_margin_<?= $row->id ?>" onchange="addNewProductsStore(<?= $row->id ?>)" value="0"><span class="slider round"></span></label></div></td>
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
    
    <script>
       
        function addNewProductsStore(product_id) { console.log(product_id);
            if (confirm("Are you sure you want to move this product?")) {
                $.ajax({
                    url: '<?= url('admin/base_products/add_new_products_store') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        product_id: product_id
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
    </script>
@endsection
