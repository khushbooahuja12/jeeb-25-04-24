@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Product List</h4>
                <a href="<?= url('vendor/new_product') ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add New
                </a>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('vendor/all_products'); ?>">Products</a></li>
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
                            <input type="text" class="form-control" name="product_id" placeholder="Search Product ID" value="{{ $product_id }}">
                            {{-- <input type="text" class="form-control" name="filter" placeholder="Search Brand Name" value="{{$filter}}"> --}}
                            <input type="text" class="form-control" name="unit" placeholder="Search Unit" value="{{ $unit }}">
                             
                        </div>
                        <button type="submit" class="btn btn-dark">Filter</button>
                    </form>
                    <table class="table table-bordered">
                        <thead>
                            <tr> 
                                <th>S.No.</th>
                                <th>Brand</th>
                                <th width="30%">@sortablelink('product_name_en', 'Product Name')</th>
                                <th>Category</th>
                                <th>On Sale</th>
                                <th>Price (QAR)</th>
                                <th>Action</th>                                
                            </tr>
                        </thead>
                        <tbody>
                            @if($products)
                            @foreach($products as $key=>$row)
                            <tr class="product<?= $row->id; ?>">
                                <td>{{$row->id}}</td> 
                                <td>{{$row->getProductBrand ? $row->getProductBrand->brand_name_en : 'N/A'}}</td>
                                <td>{{$row->product_name_en}}</td>
                                <td>{{$row->getProductCategory ? $row->getProductCategory->category_name_en : 'N/A'}}</td>
                                <td><div class="mytoggle"> 
                                    <label class="switch"><input class="switch-input" type="checkbox" name="allow_margin" id="add_product_allow_margin_<?= $row->id ?>" onchange="changeAllowMarginOnAdd(<?= $row->id ?>)" value="1" checked>
                                    <span class="slider round"></span></label></div></td>
                                <td>{{$row->base_price}} QAR</td>
                                
                                <td>                                    
                                    <a title="view" href="<?= url('vendor/products/view/'.base64url_encode($row->id)); ?>"><i class="icon-eye "></i></a>
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
                                {{-- Showing {{ count($products) }} of {{ count($products) }} entries. --}}
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
@endsection