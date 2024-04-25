@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Product</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/products') ?>">Products</a></li>
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
                            action="{{ route('admin.products.update', [base64url_encode($product->id)]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <select name="fk_company_id" id="fk_company_id"
                                            data-title="Company" class="form-control regInputs select2">
                                            <option value="">Select Company</option>
                                            @if ($companies)
                                                @foreach ($companies as $row)
                                                    <option value="{{ $row->id }}" class="company{{ $row->id }}"
                                                        <?= old('fk_company_id') ? (old('fk_company_id') == $row->id ? 'selected' : '') : ($product->fk_company_id == $row->id ? 'selected' : '') ?>>
                                                        {{ $row->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="errorPrint" id="fk_company_idError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Category</label>
                                        <select name="fk_category_id" id="fk_category_id" onchange="getSubCategory(this);"
                                            data-title="Product Category" class="form-control select2">
                                            <option value="">Select Category</option>
                                            @if ($categories)
                                                @foreach ($categories as $row)
                                                    <option value="{{ $row->id }}" class="category{{ $row->id }}"
                                                        <?= old('fk_category_id') ? (old('fk_category_id') == $row->id ? 'selected' : '') : ($product->fk_category_id == $row->id ? 'selected' : '') ?>>
                                                        {{ $row->category_name_en }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="errorPrint" id="fk_category_idError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Brand</label>
                                        <select name="fk_brand_id" id="fk_brand_id" data-title="Product Brand"
                                            class="form-control regInputs select2">
                                            <option value="">Select Brand</option>
                                            @if ($brands)
                                                @foreach ($brands as $key => $row)
                                                    <option value="{{ $row->id }}"
                                                        <?= $product->fk_brand_id == $row->id ? 'selected' : '' ?>>
                                                        {{ $row->brand_name_en }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="errorPrint" id="fk_brand_idError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sub Category</label>
                                        <select name="fk_sub_category_id" id="fk_sub_category_id"
                                            data-title="Product Sub Category" class="form-control regInputs select2">
                                            <option value="">Select Sub Category</option>
                                            @if ($sub_categories)
                                                @foreach ($sub_categories as $key => $row)
                                                    <option value="{{ $row->id }}"
                                                        <?= $product->fk_sub_category_id == $row->id ? 'selected' : '' ?>>
                                                        {{ $row->category_name_en }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name (En)</label>
                                        <input type="text" name="product_name_en" id="product_name_en"
                                            value="{{ old('product_name_en', $product->product_name_en) }}"
                                            data-title="Product Name (En)" class="form-control regInputs"
                                            placeholder="Product Name (En)">
                                        <p class="errorPrint" id="product_name_enError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name (Ar)</label>
                                        <input type="text" name="product_name_ar" id="product_name_ar"
                                            value="{{ old('product_name_ar', $product->product_name_ar) }}"
                                            data-title="Product Name (Ar)" class="form-control regInputs"
                                            placeholder="Product Name (Ar)">
                                        <p class="errorPrint" id="nameError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type="text" name="unit" id="unit" data-title="Unit"
                                            value="{{ old('unit', $product->unit) }}" class="form-control regInputs"
                                            placeholder="Unit">
                                        <p class="errorPrint" id="unitError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Distributor Price (QAR)</label>
                                        <input type="text" name="distributor_price" id="distributor_price"
                                            data-title="Distributor Price"
                                            value="{{ old('distributor_price', $product->distributor_price) }}"
                                            class="form-control regInputs" placeholder="Distributor Price">
                                        <p class="errorPrint" id="distributor_priceError"></p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Recalculate margin by Formula ?</label><br>
                                        <input type="radio" name="allow_margin" value="1"
                                            <?= $product->allow_margin == 1 ? 'checked' : '' ?>> Yes &ensp;
                                        <input type="radio" name="allow_margin" value="0"
                                            <?= $product->allow_margin == 0 ? 'checked' : '' ?>> No
                                    </div>
                                </div>
                                <div class="col-md-4 sellingPriceInput"
                                    style="display:<?= $product->allow_margin == 1 ? 'none' : 'block' ?>">
                                    <div class="form-group">
                                        <label>Selling Price (QAR)</label>
                                        <input type="text" name="product_price" id="product_price"
                                            data-title="Selling Price"
                                            value="{{ old('product_price') ? old('product_price') : $product->product_price }}"
                                            class="form-control regInputs" placeholder="Distributor Price">
                                        <p class="errorPrint" id="product_priceError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Distributor ID</label>
                                        <input type="text" name="distributor_id"
                                            value="{{ old('distributor_id', $product->distributor_id) }}"
                                            class="form-control regInputs" placeholder="Distributor ID">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tags</label>
                                        {{-- <input type="text" name="_tags"
                                            value="{{ $product->_tags }}"
                                            class="form-control" placeholder="Tags"> --}}
                                            
                                            @php
                                            $product_tags = explode(",",$product->_tags);
                                            @endphp
                                            <select name="_tags[]" data-title="Tags" class="form-control regInputs select2" multiple>
                                                <option value="" disabled>Select Tags</option>
                                                @if ($tags)
                                                    @foreach ($tags as $row)
                                                        <option value="{{ $row->id }}"
                                                            <?= in_array($row->title_en, $product_tags) ? 'selected' : '' ?>>
                                                            {{ $row->title_en }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tag Bundle</label>
                                    
                                            <select name="fk_tag_bundle_id" data-title="Bundle" class="form-control regInputs select2">
                                                <option value="">Select Bundle</option>
                                                @if ($tag_bundles)
                                                    @foreach ($tag_bundles as $row)
                                                        <option value="{{ $row->id }}"
                                                            <?= $row->id == $product->fk_tag_bundle_id ? 'selected' : '' ?>>
                                                            {{ $row->name_en }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Itemcode</label>
                                        <input type="text" name="itemcode"
                                            value="{{ old('itemcode') ? old('itemcode') : $product->itemcode }}"
                                            class="form-control" placeholder="Itemcode">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Barcode</label>
                                        <input type="text" name="barcode"
                                            value="{{ old('barcode') ? old('barcode') : $product->barcode }}"
                                            class="form-control" placeholder="Barcode">
                                    </div>
                                </div>
                                @if ($product->fk_category_id == 16)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Min Scale</label>
                                            <input type="text" name="min_scale"
                                                value="{{ old('min_scale') ? old('min_scale') : $product->min_scale }}"
                                                class="form-control" placeholder="Min Scale">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Max Scale</label>
                                            <input type="text" name="max_scale"
                                                value="{{ old('max_scale') ? old('max_scale') : $product->max_scale }}"
                                                class="form-control" placeholder="Max Scale">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <label>Manage Stock</label>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Store Name</th>
                                                    <th>Stock</th>
                                                    <th>Distributor Price</th>
                                                    <th>Selling Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Store 1</td>
                                                    <td>
                                                        <input type="text" name="store1"
                                                        value="{{ old('store1') ? old('store1') : $product->store1 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store1_distributor_price"
                                                            value="{{ old('store1_distributor_price') ? old('store1_distributor_price') : $product->store1_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store1_price"
                                                            value="{{ old('store1_price') ? old('store1_price') : $product->store1_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 2</td>
                                                    <td>
                                                        <input type="text" name="store2"
                                                        value="{{ old('store2') ? old('store2') : $product->store2 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store2_distributor_price"
                                                            value="{{ old('store2_distributor_price') ? old('store2_distributor_price') : $product->store2_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store2_price"
                                                            value="{{ old('store2_price') ? old('store2_price') : $product->store2_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 3</td>
                                                    <td>
                                                        <input type="text" name="store3"
                                                        value="{{ old('store3') ? old('store3') : $product->store3 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store3_distributor_price"
                                                            value="{{ old('store3_distributor_price') ? old('store3_distributor_price') : $product->store3_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store3_price"
                                                            value="{{ old('store3_price') ? old('store3_price') : $product->store3_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 4</td>
                                                    <td>
                                                        <input type="text" name="store4"
                                                        value="{{ old('store4') ? old('store4') : $product->store4 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store4_distributor_price"
                                                            value="{{ old('store4_distributor_price') ? old('store4_distributor_price') : $product->store4_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store4_price"
                                                            value="{{ old('store4_price') ? old('store4_price') : $product->store4_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 5</td>
                                                    <td>
                                                        <input type="text" name="store5"
                                                        value="{{ old('store5') ? old('store5') : $product->store5 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store5_distributor_price"
                                                            value="{{ old('store5_distributor_price') ? old('store5_distributor_price') : $product->store5_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store5_price"
                                                            value="{{ old('store5_price') ? old('store5_price') : $product->store5_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 6</td>
                                                    <td>
                                                        <input type="text" name="store6"
                                                        value="{{ old('store6') ? old('store6') : $product->store6 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store6_distributor_price"
                                                            value="{{ old('store6_distributor_price') ? old('store6_distributor_price') : $product->store6_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store6_price"
                                                            value="{{ old('store6_price') ? old('store6_price') : $product->store6_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 7</td>
                                                    <td>
                                                        <input type="text" name="store7"
                                                        value="{{ old('store7') ? old('store7') : $product->store7 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store7_distributor_price"
                                                            value="{{ old('store7_distributor_price') ? old('store7_distributor_price') : $product->store7_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store7_price"
                                                            value="{{ old('store7_price') ? old('store7_price') : $product->store7_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 8</td>
                                                    <td>
                                                        <input type="text" name="store8"
                                                        value="{{ old('store8') ? old('store8') : $product->store8 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store8_distributor_price"
                                                            value="{{ old('store8_distributor_price') ? old('store8_distributor_price') : $product->store8_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store8_price"
                                                            value="{{ old('store8_price') ? old('store8_price') : $product->store8_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 9</td>
                                                    <td>
                                                        <input type="text" name="store9"
                                                        value="{{ old('store9') ? old('store9') : $product->store9 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store9_distributor_price"
                                                            value="{{ old('store9_distributor_price') ? old('store9_distributor_price') : $product->store9_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store9_price"
                                                            value="{{ old('store9_price') ? old('store9_price') : $product->store9_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Store 10</td>
                                                    <td>
                                                        <input type="text" name="store10"
                                                        value="{{ old('store10') ? old('store10') : $product->store10 }}"
                                                        class="form-control" placeholder="Stock">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store10_distributor_price"
                                                            value="{{ old('store10_distributor_price') ? old('store10_distributor_price') : $product->store10_distributor_price }}"
                                                            class="form-control" placeholder="Store Price">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="store10_price"
                                                            value="{{ old('store10_price') ? old('store10_price') : $product->store10_price }}"
                                                            class="form-control" placeholder="Selling Price">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Product Main Image</label>
                                    <div class="form-group">
                                        <input type="file" accept="image/*" class="dropify" data-show-remove="false"
                                            id="image" name="image"
                                            data-default-file="{{ !empty($product->getProductImage) ? asset('images/product_images') . '/' . $product->getProductImage->file_name : '' }}" />
                                        <p class="errorPrint" id="imageError"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Country Icon</label>
                                    <div class="form-group">
                                        <input type="file" accept="image/*" class="dropify regInputs"
                                            id="country_icon"
                                            data-default-file="{{ $product->country_icon ? $product->country_icon : asset('assets/images/dummy-product-image.jpg') }}"
                                            name="country_icon" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Country Code</label>
                                        <input type="text" name="country_code" class="form-control"
                                            value="{{ old('country_code') ? old('country_code') : $product->country_code }}"
                                            placeholder="exp: QAR">
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
                    fk_category_id: {
                        required: true
                    },
                    product_name_en: {
                        required: true
                    },
                    product_name_ar: {
                        required: true
                    },
                    distributor_price: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    product_price: {
                        required: true,
                        number: true,
                        min: 0
                    }
                }
            });

        });

        function getSubCategory(obj) {
            var id = $(obj).val();
            $('#fk_sub_category_id').empty();
            var html = "<option value='' disabled selected>Select Sub Category</option>";
            if (id) {
                $.ajax({
                    url: "<?= url('admin/products/get_sub_category') ?>",
                    type: 'post',
                    data: 'fk_category_id=' + id + '&_token=<?= csrf_token() ?>',
                    success: function(data) {
                        if (data.error_code == "200") {
                            html += data.data;
                        } else {
                            alert(data.message);
                        }
                        $('#fk_sub_category_id').append(html);
                    }
                });
            } else {
                alert("Something went wrong");
            }
        }
    </script>
@endsection
