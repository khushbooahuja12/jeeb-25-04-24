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
                        <li class="breadcrumb-item"><a href="<?= url('store/'.$storeId.'/instock_products') ?>">Products</a></li>
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
                                <a href="<?= url('store/' . $storeId . '/products/edit/' . base64url_encode($product->id)) ?>"
                                    class="btn btn-primary waves-effect waves-light float-right">
                                    <i class="fa fa-edit"></i> Edit Product
                                </a>
                            </div>
                            <div class="col-md-12 mt-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Itemcode</th>
                                        <td>{{ $product->itemcode }}</td>
                                        <th>Barcode(En) </th>
                                        <td>{{ $product->barcode??'N/A' }}</td>
                                    </tr>
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
                                        <td>{{ $product->brand_name??'N/A' }}</td>
                                        <th>Unit </th>
                                        <td>{{ $product->unit }}</td>
                                    </tr>
                                    <tr style="border-top: 1px solid black">
                                        <th>#</th>
                                        <th>Store <?= $storeId ?></th>
                                    </tr>
                                    <tr style="border-top: 1px solid black">
                                        <th>Distributor Price</th>
                                        <td>
                                            <?php
                                            $store_key = 'store' . $storeId . '_distributor_price';
                                            echo 'QR ' . $product->$store_key;
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Selling Price</th>
                                        <td>
                                            <?php
                                            $store_key = 'store' . $storeId . '_price';
                                            echo 'QR ' . $product->$store_key;
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>In Stock ?</th>
                                        <td>
                                            <?php
                                            $store_key = 'store' . $storeId;
                                            echo $product->$store_key == 1 ? 'Yes' : 'No';
                                            ?>
                                        </td>
                                    </tr>
                                    <tr style="border-top: 1px solid black"></tr>
                                    <tr>
                                        <th>Margin </th>
                                        <td>{{ $product->margin ? $product->margin . ' %' : $product->margin }}</td>
                                    </tr>
                                    <tr>
                                        <th>Offered </th>
                                        <td>{{ $product->offered == 1 ? 'Yes' : 'No' }}</td>
                                        <th>Frequently Bought Together? </th>
                                        <td>{{ $product->frequently_bought_together == 1 ? 'Yes' : 'No' }}</td>
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
