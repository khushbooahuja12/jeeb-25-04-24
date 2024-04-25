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
                            {{-- <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Store Name"
                                    value="{{ $filter }}">
                            </div> 
                            <a href="<?= url('admin/fleet/stores/catalog') ?>">
                                <button type="button" class="btn btn-warning">Clear</button>
                            </a>
                            <button type="submit" class="btn btn-dark">Filter</button> --}}
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
                                    <th>All Recipes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $all_products = 0;
                                    $all_recipes = 0;
                                @endphp
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            @php
                                                $store_all_products = $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->where('base_products_store.deleted',0)->where('base_products.product_type','=','product')->distinct('base_products.id')->count() : 0;
                                                $store_all_recipes = $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->where('base_products_store.deleted',0)->whereIn('base_products.product_type',['recipe','pantry_item'])->count() : 0;
                                                $all_products = $all_products + $store_all_products;
                                                $all_recipes = $all_recipes + $store_all_recipes;
                                            @endphp
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->getCompany ? $row->getCompany->name : '' }}</td>
                                            <td>{{ $store_all_products }}</td>
                                            <td>{{ $store_all_recipes }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr class="vendorAll" style="background: #333333; color: #ffffff;">
                                    <td></td>
                                    <td></td>
                                    <td>Total</td>
                                    <td></td>
                                    <td>{{ $all_products }}</td>
                                    <td>{{ $all_recipes }}</td>
                                </tr>
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
