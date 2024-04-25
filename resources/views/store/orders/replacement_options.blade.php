@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Replacement Options</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/orders') ?>">Order Detail</a>
                        </li>
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
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Category</th>
                                    <th>@sortablelink('product_name_en', 'Product Name')</th>
                                    <th>Quantity </th>
                                    <th>@sortablelink('product_price', 'Product Price')</th>
                                    <th class="nosort">Product Image </th>
                                    <th class="nosort">Add/Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                        <tr class="product<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ !empty($row->getProductCategory) ? $row->getProductCategory->category_name_en : 'N/A' }}
                                            </td>
                                            <td><a
                                                    href="<?= url('admin/products/show/' . base64url_encode($row->id)) ?>">{{ $row->product_name_en }}</a>
                                            </td>
                                            <td>{{ $row->unit }}</td>
                                            <td>{{ $row->product_price }}</td>
                                            <td>
                                                <a href="javascript:void(0)" fk_product_id="{{ $row->id }}">
                                                    <img src="{{ !empty($row->product_image_url) ? $row->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                        width="75" height="50">
                                                </a>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'popular' ?>"
                                                            type="checkbox"
                                                            <?= !empty($row->existOnReplacedProduct) && $row->existOnReplacedProduct->fk_order_id == $order_id ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="updateReplacedProducts(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $products->count() }} of {{ $products->count() }} entries.
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
        function updateReplacedProducts(obj) {
            var id = $(obj).attr("id");

            $.ajax({
                url: '<?= url('admin/orders/add_remove_replaced_products') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    product_id: id,
                    order_id: '<?= $order_id ?>'
                },
                cache: false,
            }).done(function(response) {
                if (response.status_code === 200) {
                    // var success_str =
                    //     '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                    //     '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                    //     '<strong>' + response.message + '</strong>.' +
                    //     '</div>';
                    // $(".ajax_alert").html(success_str);
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
    </script>
@endsection
