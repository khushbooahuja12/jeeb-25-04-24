@extends('vendor.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Product</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/all_products') ?>">Products</a></li>
                        <li class="breadcrumb-item active">Add</li>
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
                            action="{{ route('vendor.products.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Category</label>
                                        <select name="fk_category_id" id="fk_category_id" onchange="getSubCategory(this);"
                                            data-title="Product Category" class="form-control regInputs select2">
                                            <option value="">--Select--</option>
                                            @if ($categories)
                                                @foreach ($categories as $row)
                                                    <option value="{{ $row->id }}" class="category{{ $row->id }}"
                                                        <?= old('fk_category_id') ? (old('fk_category_id') == $row->id ? 'selected' : '') : '' ?>>
                                                        {{ $row->category_name_en }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="errorPrint" id="fk_category_idError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sub Category</label>
                                        <select name="fk_sub_category_id" id="fk_sub_category_id"
                                            data-title="Product Sub Category" class="form-control regInputs">
                                            <option value="">--Select--</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Brand</label>
                                        <select name="fk_brand_id" id="fk_brand_id" data-title="Product Brand"
                                            class="form-control regInputs select2">
                                            <option value="">--Select--</option>
                                            @if ($brands)
                                                @foreach ($brands as $row)
                                                    <option value="{{ $row->id }}"
                                                        <?= old('fk_brand_id') ? (old('fk_brand_id') == $row->id ? 'selected' : '') : '' ?>>
                                                        {{ $row->brand_name_en }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="errorPrint" id="fk_brand_idError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="text" name="quantity" id="quantity" data-title="Unit"
                                            value="{{ old('quantity') }}" class="form-control regInputs" placeholder="Quantity">
                                        <p class="errorPrint" id="quantityError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name (En)</label>
                                        <input type="text" name="product_name_en" id="product_name_en"
                                            value="{{ old('product_name_en') }}" data-title="Product Name (En)"
                                            class="form-control regInputs" placeholder="Product Name (En)">
                                        <p class="errorPrint" id="product_name_enError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name (Ar)</label>
                                        <input type="text" name="product_name_ar" id="product_name_ar"
                                            value="{{ old('product_name_ar') }}" data-title="Product Name (Ar)"
                                            class="form-control regInputs" placeholder="Product Name (Ar)">
                                        <p class="errorPrint" id="nameError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Price</label>
                                        <input type="text" name="price" id="price"
                                            value="{{ old('price') }}" data-title="price"
                                            class="form-control regInputs" placeholder="price">
                                        <p class="errorPrint" id="priceError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="width: 100px;">
                                        <label>Instock</label>
                                        <select name="instock" id="instock" >
                                            <option value="1">In Stock</option>
                                            <option value="0">Out of Stock</option>
                                        </select>
                                       
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Color</label>
                                        <input type="text" name="color" id="color"
                                            value="{{ old('color') }}" data-title="color"
                                            class="form-control regInputs" placeholder="color">
                                        <p class="errorPrint" id="colorError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>HSN</label>
                                        <input type="text" name="hsn" id="hsn"
                                            value="{{ old('hsn') }}" data-title="HSN"
                                            class="form-control regInputs" placeholder="HSN">
                                        <p class="errorPrint" id="hsnError"></p>
                                    </div>
                                </div>
                               
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Description (En)</label>
                                        <textarea class="form-control" id="desc_en" name="desc_en"></textarea>
                                      </div>
                                </div>
                              
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Product Main Image</label>
                                    <div class="form-group">
                                        <input type="file" accept="image/*" class="dropify regInputs"
                                            data-title="Image" id="image" name="image" multiple/>
                                        <p class="errorPrint" id="imageError"></p>
                                    </div>
                                </div>
                               
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5"
                                            onclick="cancelForm(1);">
                                            Cancel
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

            $('#regForm').validate({
                rules: {
                    fk_company_id: {
                        required: true
                    },
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
                    },
                    image: {
                        required: true
                    }
                }
            });

        });

        function getSubCategory(obj) {
            var id = $(obj).val();
            $('#fk_sub_category_id').empty();
            var html = "<option value=''>--Select--</option>";
            if (id) {
                if (id == 16) {
                    $("input[name=min_scale]").parent().parent().css('display', 'block');
                    $("input[name=max_scale]").parent().parent().css('display', 'block');
                }
                $.ajax({
                    url: "<?= url('vendor/products/get_sub_category') ?>",
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
