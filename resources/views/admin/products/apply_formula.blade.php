@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Apply Product Offer / Pricing Formula</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="#"> Offers & Pricing Formula</a></li>
                    <li class="breadcrumb-item active">Apply Formula</li>
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
                    <form method="POST" action="{{ route('admin.products.apply_formula_to_all_products') }}">
                        @csrf
                        <div class="row">
                            <div class="col-sm-3">
                                <h6>Stores</h6>
                                    <select class="form-control select2" id="price_formula_store" name="store_id">
                                        <option value="0">All Store</option>
                                        @foreach ( $stores as $store )
                                            <option value="{{ $store->id == 0 ? 'default' : $store->id }}" {{ $store_id == $store->id ? 'selected' : '' }} >{{ $store->name }} - {{{ $store->company_name }}}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-sm-3">
                                <h6>Subcategory</h6>
                                <select class="form-control select2" id="price_formula_subcategory" name="subcategory_id">
                                    <option value="0" selected>--Select--</option>
                                    @foreach ( $sub_categories as $sub_category )
                                        <option value="{{ $sub_category->id }}" {{ $subcategory_id == $sub_category->id ? 'selected' : '' }}>{{ $sub_category->category_name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <h6>Brand</h6>
                                <select class="form-control select2" id="price_formula_brand" name="brand_id">
                                    <option value="0" selected>--Select--</option>
                                    @foreach ( $brands as $brand )
                                        <option value="{{ $brand->id }}" {{ $brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->brand_name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <h6>Offer options</h6>
                                <select class="form-control select2" id="offer_formula" name="offer_id">
                                    <option selected disabled>--Select--</option>
                                    @foreach ( $offer_options as $offer_option ) 
                                        <option value="{{ $offer_option->id }}" {{ $offer_id == $offer_option->id ? 'selected' : ''}}>{{ $offer_option->name }} - Base Price Offer : ({{ $offer_option->base_price_percentage}} % ), Discount Offer : ({{ $offer_option->discount_percentage }} %)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br>
                        <button type="submit" name="base_products_update" value="base_products_update" class="btn btn-primary"><i class="fa fa-check"></i> Update all products prices</button>
                        <button type="submit" name="base_products_store_update" value="base_products_store_update" class="btn btn-primary"><i class="fa fa-check"></i> Update all products store prices</button>
                        <a href="<?= url('admin/products/apply_formula?store_id=0&subcategory_id=0&brand_id=0'); ?>" class="btn btn-warning"> Clear</a>
                        <br/><br/>
                        As per the current pricing / offers formula
                    </form>
                </div>
                
            </div>
        </div>
    </div>    

    <div class="row">
        <div class="col-md-12">
            <div class="card m-b-30">                               
                <div class="card-body">
                </div>
            </div>
        </div>
    </div>    
</div>
@endsection