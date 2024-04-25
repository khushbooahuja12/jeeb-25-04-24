@extends('vendor.layouts.dashboard_layout')
@section('content')
    <link href="{{ asset('assets/plugins/owl-carousel/owl.carousel.css') }}" rel="stylesheet" />
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Product Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item">
                            <a href="<?= url('vendor/dashboard') ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= url('vendor/orders/detail') . '/' . base64url_encode($op->fk_order_id) ?>">Order
                                detail (<?= $op->getOrder->orderId ?>)
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        <div class="row">
                            <div class="col-md-12 mt-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Image</th>
                                        <td><img src="{{ $op->product_image_url }}" width="150" height="150" /></td>
                                    </tr>
                                    <tr>
                                        <th>Quantity</th>
                                        <td>{{ $op->product_quantity }}</td>
                                    </tr>
                                </table>
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Itemcode</th>
                                        <td>{{ $op->itemcode }}</td>
                                        <th>Barcode(En) </th>
                                        <td>{{ $op->barcode ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Name (En) </th>
                                        <td>{{ $op->product_name_en }}</td>
                                        <th>Product Name (Ar) </th>
                                        <td>{{ $op->product_name_ar }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Category </th>
                                        <td>{{ $op->category_name }}</td>
                                        <th>Product Sub Category </th>
                                        <td>{{ $op->sub_category_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Brand </th>
                                        <td>{{ $op->brand_name ?? 'N/A' }}</td>
                                        <th>Weight/Unit</th>
                                        <td>{{ $op->product_weight && $op->product_unit ? $op->product_weight . ' ' . $op->product_unit : $op->unit }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Distributor Price</th>
                                        <td>{{ 'QR' . $op->distributor_price }} </td>
                                        <th>Unit Price</th>
                                        <td>{{ 'QR ' . $op->single_product_price }} </td>
                                    </tr>
                                    <tr>
                                        <th>Total Price</th>
                                        <td>{{ 'QR ' . $op->total_product_price }} </td>
                                        <th>Margin </th>
                                        <td>{{ $op->margin ? $op->margin . ' %' : $op->margin }}</td>
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
