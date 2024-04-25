@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Store Catalog</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/fleet/stores/catalog') ?>">Catalog</a></li>
                        <li class="breadcrumb-item active">List</li>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Store Name"
                                    value="{{ $filter }}">
                            </div>
                            <a href="<?= url('admin/fleet/stores/catalog') ?>">
                                <button type="button" class="btn btn-warning">Clear</button>
                            </a>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>@sortablelink('name', 'Store Name')</th>
                                    <th>Company Name</th>
                                    <th>All Products</th>
                                    <th>In Stock Products</th>
                                    <th>Out Of Stock Products</th>
                                    <th>Displayed Products</th>
                                    <th>All Recipes</th>
                                    <th>Active Recipes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->getCompany ? $row->getCompany->name : '' }}</td>
                                            <td>{{ $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->where('base_products_store.deleted',0)->where('base_products.product_type','=','product')->distinct('base_products.id')->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->where('base_products_store.deleted',0)->where('base_products.product_type','=','product')->where('base_products_store.stock','>',0)->distinct('base_products.id')->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->where('base_products_store.deleted',0)->where('base_products.product_type','=','product')->where('base_products_store.stock','=',0)->distinct('base_products.id')->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProducts() ? $row->getAllBaseProducts()->where('deleted',0)->where('product_type','=','product')->where('product_store_stock','>',0)->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->where('base_products_store.deleted',0)->whereIn('base_products.product_type',['recipe','pantry_item'])->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id', '=', 'base_products.id')->where('base_products_store.deleted',0)->whereIn('base_products.product_type',['recipe','pantry_item'])->where('base_products_store.stock','>',0)->count() : '' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $stores->count() }} of {{ $stores->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $stores->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
