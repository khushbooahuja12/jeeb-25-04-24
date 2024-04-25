@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Top searched products</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/products'); ?>">Products</a></li>
                    <li class="breadcrumb-item active">Top Searched</li>
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
                    <table id="datatable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>                               
                                <th>Product Name</th>
                                <th>Quantity </th>
                                <th>Product Image </th>
                                <th>No. of occurrence</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($top_searched_products)
                            @foreach($top_searched_products as $key=>$row)
                            <tr class="product<?= $row->id; ?>">
                                <td>{{$key+1}}</td>
                                <td>{{$row->product_name_en}}</td>
                                <td>{{$row->unit}}</td>
                                <td>
                                    <a href="javascript:void(0)" fk_product_id="{{$row->id}}">
                                        <img src="{{!empty($row->getProductImage) ? asset('images/product_images') . '/' . $row->getProductImage->file_name : asset('assets/images/dummy-product-image.jpg')}}" width="75" height="50">
                                    </a>
                                </td>
                                <td>{{$row->frequency}}</td>
                                <td>                                    
                                    <a title="view details" href="<?= url('admin/products/show/' . base64url_encode($row->product_id)); ?>"><i class="fa fa-eye"></i></a>
                                </td>                               
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
@endsection