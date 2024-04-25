@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Product Price Formula</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="#"> Offers & Pricing Formula</a></li>
                    <li class="breadcrumb-item active">Price Formula</li>
                </ol>
            </div>
            <div class="col-sm-12">
                <a type="submit" href="{{ route('offer_formula') }}" class="btn btn-primary float-right"><i class="fa fa-back"></i> Go Back</a>
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
                    <form method="GET" action="{{ route('filter-price-formula')}}">
                        <div class="row">
                                <div class="col-sm-4">
                                    <h6>Stores</h6>
                                        <select class="form-control select2" id="price_formula_store" name="store_id">
                                            <option value="0">All Store</option>
                                            @foreach ( $stores as $store )
                                                <option value="{{ $store->id == 0 ? 'default' : $store->id }}" {{ $store_id == $store->id ? 'selected' : '' }} >{{ $store->name }} - {{{ $store->company_name }}}</option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="col-sm-4">
                                    <h6>Subcategory</h6>
                                    <select class="form-control select2" id="price_formula_subcategory" name="subcategory_id">
                                        <option value="0" selected>--Select--</option>
                                        @foreach ( $sub_categories as $sub_category )
                                            <option value="{{ $sub_category->id }}" {{ $subcategory_id == $sub_category->id ? 'selected' : '' }}>{{ $sub_category->category_name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <h6>Brand</h6>
                                    <select class="form-control select2" id="price_formula_brand" name="brand_id">
                                        <option value="0" selected>--Select--</option>
                                        @foreach ( $brands as $brand )
                                            <option value="{{ $brand->id }}" {{ $brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->brand_name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        <a href="<?= url('admin/products/filter_price_formula?store_id=0&subcategory_id=0&brand_id=0'); ?>" class="btn btn-warning"> Clear</a>
                    </form>
                </div>
                
            </div>
        </div>
    </div>    
    <div class="row">
        <div class="col-md-12">
            <div class="card m-b-30">                               
                <div class="card-body">
                    <a href="<?= url('admin/products/create-formula/'.$store_id.'/'.$subcategory_id.'/'.$brand_id); ?>" class="btn btn-primary m-b-20">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                    @if($store_id!=0)
                    <a href="<?= url('admin/products/copy-default-formula/'.$store_id.'/'.$subcategory_id.'/'.$brand_id); ?>" onclick="return confirm('Would you like to copy default store price formula to this store? it will delete your existing price formula')" class="btn btn-primary m-b-20">
                        <i class="fa fa-copy"></i> Copy from all store
                    </a>
                    <a href="<?= url('admin/products/delete-store-price-formula/'.$store_id.'/'.$subcategory_id.'/'.$brand_id); ?>" onclick="return confirm('Are you sure you want to delete this pricing formula combination ?')" class="btn btn-danger m-b-20">
                         Delete all current formula
                    </a>
                    @endif
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>X1</th>
                                <th>X2</th>
                                <th>X3</th>
                                <th>X4</th>                              
                                <th>Action</th>                               
                            </tr>
                        </thead>
                        <tbody>
                            @if($result)
                            @foreach($result as $key=>$value)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$value->x1}}</td>
                                <td>{{$value->x2}}</td>                               
                                <td>{{$value->x3}}</td>
                                <td>{{$value->x4}}</td>                                
                                <td>                                    
                                    <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                    @if($value->fk_store_id != 0)
                                    <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-price-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this pricing formula ?')">Delete</a>
                                    @endif
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