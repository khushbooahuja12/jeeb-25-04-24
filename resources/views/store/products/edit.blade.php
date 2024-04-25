@extends('store.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Product</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="<?= url('store/' . $storeId . '/instock_products') ?>">Products</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="regForm" enctype="multipart/form-data"
                            action="{{ route('store.products.update', [$storeId, base64url_encode($product->id)]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Itemcode</label>
                                        <input type="text" value="{{ $product->itemcode ?? 'N/A' }}" class="form-control"
                                            disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Barcode</label>
                                        <input type="text" value="{{ $product->barcode ?? 'N/A' }}" class="form-control"
                                            disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name (En)</label>
                                        <input type="text" value="{{ $product->product_name_en ?? 'N/A' }}"
                                            class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type="text" name="unit" id="unit" data-title="Unit"
                                            value="{{ $product->unit ?? 'N/A' }}" class="form-control regInputs" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Distributor Price (QAR)</label>
                                        <input type="text" name="store<?= $storeId ?>_distributor_price"
                                            data-title="Distributor Price" value="<?php
                                            $product_key = 'store' . $storeId . '_distributor_price';
                                            echo $product->$product_key;
                                            ?>"
                                            class="form-control regInputs" placeholder="Distributor Price">
                                        <p class="errorPrint" id="store<?= $storeId ?>_distributor_priceError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Stock</label>
                                        <select class="form-control" name="stock">
                                            <option value="1" <?= $product->$store_key == 1 ? 'selected' : '' ?>>In
                                                Stock
                                            </option>
                                            <option value="0" <?= $product->$store_key == 0 ? 'selected' : '' ?>>Out of
                                                Stock</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $("input[name=allow_margin]").on('click', function() {
            if ($(this).val() == 1) {
                $(".sellingPriceInput").css('display', 'none');
            } else if ($(this).val() == 0) {
                $(".sellingPriceInput").css('display', 'block');
            }
        })
        $(document).ready(function() {
            $('#regForm').validate({ // initialize the plugin
                rules: {
                    store<?= $storeId ?>_distributor_price: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    store<?= $storeId ?>_price: {
                        required: true,
                        number: true,
                        min: 0
                    }
                }
            });

        });
    </script>
@endsection
