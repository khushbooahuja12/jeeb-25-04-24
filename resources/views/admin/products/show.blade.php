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
                        <li class="breadcrumb-item"><a href="<?= url('admin/products') ?>">Products</a></li>
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
                                                <img src="{{ $product->product_image }}" width="100%">
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <h3>Company: {{ $product->company_name }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-1 m-b-2">
                                <a href="<?= url('admin/products/edit/' . base64url_encode($product->id)) ?>"
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
                                        <td>{{ $product->barcode ?? 'N/A' }}</td>
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
                                        <td>{{ $product->brand_name ?? 'N/A' }}</td>
                                        <th>Unit </th>
                                        <td>{{ $product->unit }}</td>
                                    </tr>
                                    <tr style="border-top: 1px solid black">
                                        <td colspan="5">
                                            <table>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Store 1</th>
                                                    <th>Store 2</th>
                                                    <th>Store 3</th>
                                                    <th>Store 4</th>
                                                    <th>Store 5</th>
                                                    <th>Store 6</th>
                                                    <th>Store 7</th>
                                                    <th>Store 8</th>
                                                    <th>Store 9</th>
                                                    <th>Store 10</th>
                                                </tr>
                                                <tr style="border-top: 1px solid black">
                                                    <th>Distributor Price</th>
                                                    <td>{{ 'QR ' . $product->store1_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store2_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store3_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store4_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store5_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store6_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store7_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store8_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store9_distributor_price }} </td>
                                                    <td>{{ 'QR ' . $product->store10_distributor_price }} </td>
                                                </tr>
                                                <tr>
                                                    <th>Selling Price</th>
                                                    <td>{{ 'QR ' . $product->store1_price }} </td>
                                                    <td>{{ 'QR ' . $product->store2_price }} </td>
                                                    <td>{{ 'QR ' . $product->store3_price }} </td>
                                                    <td>{{ 'QR ' . $product->store4_price }} </td>
                                                    <td>{{ 'QR ' . $product->store5_price }} </td>
                                                    <td>{{ 'QR ' . $product->store6_price }} </td>
                                                    <td>{{ 'QR ' . $product->store7_price }} </td>
                                                    <td>{{ 'QR ' . $product->store8_price }} </td>
                                                    <td>{{ 'QR ' . $product->store9_price }} </td>
                                                    <td>{{ 'QR ' . $product->store10_price }} </td>
                                                </tr>
                                                <tr>
                                                    <th>In Stock ?</th>
                                                    <td>{{ $product->store1 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store2 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store3 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store4 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store5 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store6 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store7 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store8 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store9 == 1 ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $product->store10 == 1 ? 'Yes' : 'No' }}</td>
                                                </tr>
                                            </table>
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
                                    <tr>
                                        <th>Country Code </th>
                                        <td>{{ $product->country_code ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
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
