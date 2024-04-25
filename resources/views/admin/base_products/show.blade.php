@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Product Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/base_products') ?>">Products</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="row">
                            <div class=" offset-11 col-md-1">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-5">
                                <div class="eventrow">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="imgDiv">
                                                <img src="{{ $product->product_image_url }}" width="100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-1 m-b-2">
                                <a href="<?= url('admin/base_products/edit/' . $product->id) ?>"
                                    class="btn btn-primary waves-effect waves-light float-right">
                                    <i class="fa fa-edit"></i> Edit Product
                                </a>
                            </div>
                            <div class="col-md-12 mt-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Product Name (En) </th>
                                        <td>{{ $product->product_name_en }}</td>
                                        <th>Product Name (Ar) </th>
                                        <td>{{ $product->product_name_ar }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Category </th>
                                        <td>{{ $product->category_name }}</td>
                                        <th>Product Sub Category </th>
                                        <td>{{ $product->sub_category_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Brand </th>
                                        <td>{{ $product->brand_name ?? 'N/A' }}</td>
                                        <th>Unit </th>
                                        <td>{{ $product->unit }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Tags </th>
                                        <td>{{ $product->_tags ?? 'N/A' }}</td>
                                        <th>Base Price </th>
                                        <td>{{ $product->base_price }}</td>
                                    </tr>
                                    <tr style="border-top: 1px solid black">
                                        <td colspan="5">
                                            <h5>Store Information</h5>
                                            <table style="width: 100%;">
                                                <tr>
                                                    <th>#</th>
                                                    <th>ID</th>
                                                    <th>Store</th>
                                                    <th>Other Names</th>
                                                    <th>Itemcode</th>
                                                    <th>Barcode</th>
                                                    <th>Unit</th>
                                                    <th>Store Price</th>
                                                    <th>Selling Price</th>
                                                    <th>Margin</th>
                                                    <th>Stock</th>
                                                </tr>
                                                @if ($product->stocks)
                                                    @foreach ($product->stocks as $key => $row)
                                                        <tr class="product<?= $row->id ?>">
                                                            <td>{{ $key + 1 }}</td>
                                                            <td>{{ $row->id }}</td>
                                                            <td>{{ !empty($row->getStore) ? $row->getStore->name : 'N/A' }}</td>
                                                            <td>{{ $row->other_names ? $row->other_names : "-" }}</td>
                                                            <td>{{ $row->itemcode }}</td>
                                                            <td>{{ $row->barcode }}</td>
                                                            <td>{{ $row->unit }}</td>
                                                            <td>{{ $row->distributor_price }}</td>
                                                            <td>{{ $row->product_price }}</td>
                                                            <td>{{ $row->margin ? $row->margin." %" : "" }}</td>
                                                            <td>{{ $row->stock }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="border-top: 1px solid black"></tr>
                                    <tr>
                                        <th>Offered </th>
                                        <td>{{ $product->offered == 1 ? 'Yes' : 'No' }}</td>
                                        <th>Frequently Bought Together? </th>
                                        <td>{{ $product->frequently_bought_together == 1 ? 'Yes' : 'No' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Country Code </th>
                                        <td>{{ $product->country_code ?? 'N/A' }}</td>
                                        <th>Country Icon </th>
                                        <td><img src="{{ $product->country_icon ? $product->country_icon : asset('assets/images/dummy-product-image.jpg') }}"
                                                width="50" height="50"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
