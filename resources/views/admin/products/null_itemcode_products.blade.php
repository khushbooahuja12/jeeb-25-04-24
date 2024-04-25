@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Products
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/remaining-products'); ?>">Products</a></li>
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
                            <input type="text" class="form-control" name="filter" placeholder="Search Brand Name" value="{{$filter}}">
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
                                <th class="nosort">Product Image </th>
                                <th class="nosort">In Stock?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($products)
                            @foreach($products as $key=>$row)
                            <tr class="product<?= $row->id; ?>">
                                <td>{{$page>=2?($page-1)*50+($key+1):($key+1)}}</td>
                                <td>{{!empty($row->getProductCategory)?$row->getProductCategory->category_name_en:'N/A'}}</td>
                                <td><a target="_blank" href="<?= url('admin/products/show/' . base64url_encode($row->id)); ?>">{{$row->product_name_en}}</a></td>
                                <td>{{$row->unit}}</td>
                                <td>
                                    <a href="javascript:void(0)">                                       
                                        <img src="{{!empty($row->getProductImage) ? asset('images/product_images') . '/' . $row->getProductImage->file_name : asset('assets/images/dummy-product-image.jpg')}}" width="75" height="50">
                                    </a>
                                </td>

                                <td>                                    
                                    <div class="mytoggle">
                                        <label class="switch">
                                            <input class="switch-input set_status<?= $row->id; ?>" type="checkbox" value="{{$row->stock}}" <?= $row->stock == 1 ? 'checked' : ''; ?> 
                                                   id="<?= $row->id; ?>" 
                                                   onchange="checkInStock(this)">
                                            <span class="slider round"></span> 
                                        </label>
                                    </div>
                                    <a title="edit" href="<?= url('admin/products/edit/' . base64url_encode($row->id)); ?>"><i class="icon-pencil"></i></a>
                                </td>                                
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$products->count()}} of {{ $products->total() }} entries.
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
    function checkInStock(obj) {
        var id = $(obj).attr("id");

        var checked = $(obj).is(':checked');
        if (checked == true) {
            var stock = 1;
            var statustext = 'update the stock';
            var message = 'Stock updated';
        } else {
            var stock = 0;
            var statustext = 'update the stock';
            var message = 'Stock updated';
        }

//        if (confirm("Are you sure you want to " + statustext + "?")) {
        $.ajax({
            url: '<?= url('admin/products/update_product_stock'); ?>',
            type: 'post',
            dataType: 'json',
            data: {id: id, stock: stock},
            cache: false,
        }).done(function (response) {
            if (response.status_code === 200) {
                var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                        + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                        + '<strong>' + message + ' successfully</strong>.'
                        + '</div>';
                $(".ajax_alert").html(success_str);
            } else {
                var error_str = '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                        + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                        + '<strong>Some error found</strong>.'
                        + '</div>';
                $(".ajax_alert").html(error_str);
            }
        });
//        } else {
//            if (stock == 1) {
//                $(".set_status" + id).prop('checked', false);
//            } else if (stock == 0) {
//                $(".set_status" + id).prop('checked', true);
//            }
//        }
    }
</script>
@endsection