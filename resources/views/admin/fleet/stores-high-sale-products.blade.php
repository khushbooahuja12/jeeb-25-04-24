@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Store High Sale Products</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/fleet/stores/sales/high_sale_products') ?>">High Sale Products</a></li>
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
                            <a href="<?= url('admin/fleet/stores/stores_high_sale_products') ?>">
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
                                <th>Store</th>
                                <th>Company Name</th>
                                <th>Products</th>
                              </tr>
                            </thead>
                            <tbody>
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->getCompany ? $row->getCompany->name : '' }}</td>
                                        <td>
                                        @if(count($row->getOrders()->get()) > 0)
                                        <table class="table table-bordered dt-responsive nowrap"
                                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                            <tr>
                                                <th style="width: 50px;">S.No.</th>
                                                <th style="width: 50px;">ID</th>
                                                <th>Name</th>
                                                <th>Sales Count</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($row->getOrders()->join('orders','order_products.fk_order_id','=','orders.id')->join('base_products','order_products.fk_product_id','=','base_products.id')->where('orders.status','=',7)->where('orders.test_order','=',0)->select('base_products.*',DB::raw('COUNT(fk_order_id) as count'))->orderBy('count','desc')->groupBy('fk_product_id')->limit(10)->get() as $key => $product)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $product->id }}</td>
                                                <td>{{ $product->product_name_en }}</td>
                                                <td>{{ $product->count }}</td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        @endif
                                        </td>
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
