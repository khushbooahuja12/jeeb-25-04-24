@extends('store.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Product List</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/instock_products') ?>">Products</a>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Brand Name"
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
                                    <th>Product Price</th>
                                    <th>Quantity </th>
                                    <th class="nosort">Product Image </th>
                                    <th class="nosort">Action</th>
                                    {{-- <th class="nosort">Offered ?</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                        <tr class="product<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ !empty($row->getProductCategory) ? $row->getProductCategory->category_name_en : 'N/A' }}
                                            </td>
                                            <td><a href="<?= url('admin/products/show/' . base64url_encode($row->id)) ?>">{{ $row->product_name_en }}
                                                    ({{ $row->id }})
                                                </a>
                                            </td>
                                            <td><?php
                                            $product_key = 'store' . $storeId . '_price';
                                            echo $row->$product_key;
                                            ?>
                                            </td>
                                            <td>{{ $row->unit }}</td>
                                            <td>
                                                <a href="javascript:void(0)" fk_product_id="{{ $row->id }}">
                                                    <img src="{{ $row->product_image_url ? $row->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                        width="75" height="50">
                                                </a>
                                            </td>
                                            <td>
                                                <a title="view details"
                                                    href="<?= url('store/' . $storeId . '/products/show/' . base64url_encode($row->id)) ?>"><i
                                                        class="fa fa-eye"></i>
                                                </a>&ensp;
                                                <a title="edit"
                                                    href="<?= url('store/' . $storeId . '/products/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i>
                                                </a>
                                            </td>
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
@endsection
