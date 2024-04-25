@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add Product Variant</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/products'); ?>">Product</a></li>
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
                    <form method="post" id="productVariant" enctype="multipart/form-data" action="{{route('admin.products.store_subproduct')}}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Category</label>
                                    <select name="fk_category_id" id="fk_category_id" onchange="getSubCategory(this);" data-title="Product Category" class="form-control regInputs select2">
                                        <option value="">Select Category</option>
                                        @if($categories)
                                        @foreach($categories as $row)
                                        <option value="{{$row->id}}" class="category{{$row->id}}" <?= $row->id == $product->fk_category_id ? 'selected' : ''; ?>>{{$row->category_name_en}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <p class="errorPrint" id="fk_category_idError"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Brand</label>
                                    <select name="fk_brand_id" id="fk_brand_id"  data-title="Product Brand" class="form-control regInputs select2">
                                        <option value="">Select Brand</option>
                                        @if($brands)
                                        @foreach($brands as $row)
                                        <option value="{{$row->id}}" class="category{{$row->id}}" <?= $row->id == $product->fk_brand_id ? 'selected' : ''; ?>>{{$row->brand_name_en}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <p class="errorPrint" id="fk_brand_idError"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sub Category</label>
                                    <select name="fk_sub_category_id" id="fk_sub_category_id" data-title="Product Sub Category" class="form-control regInputs select2">
                                        <option value="">Select Sub Category</option>
                                        @if($sub_categories)
                                        @foreach($sub_categories as $key=>$value)
                                        <option value="{{$value->id}}" <?= $value->id == $product->fk_sub_category_id ? "selected" : ""; ?>>{{$value->category_name_en}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Name (En)</label>
                                    <input type="text" name="product_name_en" id="product_name_en" value="{{old('product_name_en')?old('product_name_en'):$product->product_name_en}}" data-title="Product Name (En)" class="form-control regInputs" placeholder="Product Name (En)">
                                    <p class="errorPrint" id="product_name_enError"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Name (Ar)</label>
                                    <input type="text" name="product_name_ar" id="product_name_ar" value="{{old('product_name_ar')?old('product_name_ar'):$product->product_name_ar}}" data-title="Product Name (Ar)" class="form-control regInputs" placeholder="Product Name (Ar)">
                                    <p class="errorPrint" id="nameError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="text" name="quantity" id="quantity" data-title="Quantity" value="{{old('quantity')}}" class="form-control regInputs" placeholder="Quantity">
                                    <p class="errorPrint" id="quantityError"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unit</label>
                                    <input type="text" name="unit" id="unit" data-title="Unit" value="{{old('unit')}}" class="form-control regInputs" placeholder="Unit">
                                    <p class="errorPrint" id="unitError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Distributor Price (QAR)</label>
                                    <input type="text" name="distributor_price" id="distributor_price" data-title="Distributor Price" value="{{old('distributor_price')}}" class="form-control regInputs" placeholder="Distributor Price">
                                    <p class="errorPrint" id="distributor_priceError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="text" name="price" id="price" data-title="Price" value="{{old('price')}}" class="form-control regInputs" placeholder="Price">
                                    <p class="errorPrint" id="priceError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Discount (%)</label>
                                    <input type="text" min="0" max="100" name="margin" id="margin" data-title="Discount (%)" value="{{old('margin')}}" class="form-control regInputs" placeholder="Discount (%)">
                                    <p class="errorPrint" id="marginError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Distributor Product ID</label>
                                    <input type="text" name="distributor_product_id" value="{{old('distributor_product_id')}}" class="form-control regInputs" placeholder="Distributor Product ID">                                    
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="text" name="stock" value="{{old('stock')}}" class="form-control regInputs" placeholder="Stock">                                   
                                </div>  
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label>Product Main Image</label>
                                <div class="eventrow">
                                    <div class="row m-t-2">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="file" accept="image/*" class="dropify" data-show-remove="false" data-title="Image" id="image" name="image" data-default-file="{{!empty($product->getProductImage) ? asset('images/product_images') . '/' . $product->getProductImage->file_name : ''}}"/>
                                                <p class="errorPrint" id="imageError"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <label>Product Images</label>
                                <div class="eventrow">
                                    <div class="row m-t-2">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="file" accept="image/*" class="dropify" data-title="Image-1" id="image1" name="product_image1" data-show-remove="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage1?'false':'true'):'true')}}" data-default-file="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage1?asset('images/product_images') . '/' . $product->getProductMoreImages->getImage1->file_name:''):'')}}"/>
                                                <p class="errorPrint" id="image1Error"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="file" accept="image/*" class="dropify" data-title="Image-2" id="image2" name="product_image2" data-show-remove="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage2?'false':'true'):'true')}}" data-default-file="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage2?asset('images/product_images') . '/' . $product->getProductMoreImages->getImage2->file_name:''):'')}}"/>
                                                <p class="errorPrint" id="image2Error"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="file" accept="image/*" class="dropify" data-title="Image-3" id="image3" name="product_image3" data-show-remove="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage3?'false':'true'):'true')}}" data-default-file="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage3?asset('images/product_images') . '/' . $product->getProductMoreImages->getImage3->file_name:''):'')}}"/>
                                                <p class="errorPrint" id="image3Error"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="file" accept="image/*" class="dropify" data-title="Image-4" id="image4" name="product_image4" data-show-remove="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage4?'false':'true'):'true')}}" data-default-file="{{($product->getProductMoreImages? ($product->getProductMoreImages->getImage4?asset('images/product_images') . '/' . $product->getProductMoreImages->getImage4->file_name:''):'')}}"/>
                                                <p class="errorPrint" id="image4Error"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                                              
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Submit
                                    </button>
                                    <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(2);">
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
    $('#productVariant').validate({
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
            price: {
                required: true,
                number: true,
                min: 1
            },
            quantity: {
                required: true,
                number: true,
                min: 1
            },
            margin: {
                number: true
            },
            unit: {
                required: true
            }
        }
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
                success: function (data) {
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