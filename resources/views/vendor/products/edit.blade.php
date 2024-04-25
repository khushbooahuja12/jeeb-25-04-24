@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Product</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('vendor/all_products'); ?>">Products</a></li>
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
                    <form method="post" id="regForm" enctype="multipart/form-data" action="{{route('vendor.products.update',[base64url_encode($product->id)])}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Name (En)</label>
                                    <input type="text" value="{{old('product_name_en',$product->product_name_en)}}" data-title="Product Name (En)" class="form-control regInputs" placeholder="Product Name (En)" readonly>
                                    <p class="errorPrint" id="product_name_enError"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Weight</label>
                                    <input type="text" data-title="Quantity" value="{{old('quantity',$product->quantity)}}" class="form-control regInputs" placeholder="Quantity" readonly>
                                    <p class="errorPrint" id="quantityError"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unit</label>
                                    <input type="text" data-title="Unit" value="{{old('unit',$product->unit)}}" class="form-control regInputs" placeholder="Unit" readonly>
                                    <p class="errorPrint" id="unitError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price (QAR)</label>
                                    <input type="text" name="distributor_price" id="distributor_price" data-title="Distributor Price" value="{{old('distributor_price',$product->distributor_price)}}" class="form-control regInputs" placeholder="Price">
                                    <p class="errorPrint" id="distributor_priceError"></p>
                                </div>  
                            </div>                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bar code</label>
                                    <input type="text" value="{{old('distributor_product_id',$product->distributor_product_id)}}" class="form-control regInputs" placeholder="Distributor Product ID" readonly>                                    
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="text" name="stock" value="{{old('stock',$product->stock)}}" class="form-control regInputs" placeholder="Stock">                                   
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
    $(document).ready(function () {
        $('#regForm').validate({
            rules: {
                distributor_price: {
                    required: true,
                    number: true,
                    min: 1
                },
                stock: {
                    number: true
                }
            }
        });
    });
</script>
@endsection