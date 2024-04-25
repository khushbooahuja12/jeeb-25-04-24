@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add New Hidden Coupon</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/coupons_hidden'); ?>">Hidden Coupons</a></li>
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
                    <form method="post" id="regForm" enctype="multipart/form-data" action="{{route('admin.coupons_hidden.store')}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Coupon Image</label>
                                    <input type="file" name="coupon_image" class="form-control dropify" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apply Offer On</label>
                                    <select name="offer_type" id="offer_type" class="form-control" onchange="setOfferType(this);">
                                        <option value="">Select Offer On</option>
                                        <option value="1">Minimum Purchase</option>
                                        <option value="2">Category</option> 
                                        <option value="3">Brand</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Coupon Type</label>
                                    <select name="type" id="type" class="form-control" onchange="setDiscountType(this);">
                                        <option value="">Select Discount Type</option>
                                        <option value="1">Discount By Percentage</option>
                                        <option value="2">Discount By Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4" id="categoryDiv" style="display:none">
                                <div class="form-group">
                                    <label>Select Category</label>
                                    <select name="fk_category_id" id="fk_category_id"  class="form-control">
                                        <option value="">Select Category</option>
                                        @if($category)
                                        @foreach($category as $row)
                                        <option value="{{$row->id}}" class="category{{$row->id}}" <?= old('fk_category_id') ? (old('fk_category_id') == $row->id ? 'selected' : '') : '' ?>>{{$row->category_name_en}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4" id="brandDiv" style="display:none">
                                <div class="form-group">
                                    <label>Select Brand</label>
                                    <select name="fk_brand_id" id="fk_brand_id" class="form-control">
                                        <option value="">Select Brand</option>
                                        @if($brands)
                                        @foreach($brands as $brand)
                                        <option value="{{$brand->id}}" class="brand{{$brand->id}}" <?= old('fk_brand_id') ? (old('fk_brand_id') == $brand->id ? 'selected' : '') : '' ?>>{{$brand->brand_name_en}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Coupon Code</label>
                                    <input type="text" name="coupon_code" id="coupon_code" value="{{old('coupon_code')}}" class="form-control" placeholder="Coupon Code">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Coupon Title</label>
                                    <input type="text" name="title_en" id="email" value="{{old('title_en')}}" class="form-control" placeholder="Coupon Title">
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Coupon Title (Ar)</label>
                                    <input type="text" name="title_ar" id="mobile" value="{{old('title_ar')}}" class="form-control" placeholder="Coupon Title (Ar)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Coupon Description</label>
                                    <input type="text" name="description_en" id="description_en" value="{{old('description_en')}}" class="form-control" placeholder="Coupon Description">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Coupon Description (Ar)</label>
                                    <input type="text" name="description_ar" id="description_ar" value="{{old('description_ar')}}" class="form-control" placeholder="Coupon Description (Ar)">                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label id="discount_type_id">Discount</label>
                                    <input type="text" name="discount" id="discount" value="{{old('discount')}}" class="form-control" placeholder="Discount">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apply On Minimum Amount</label>
                                    <input type="text" name="min_amount" id="min_amount"  value="{{old('min_amount')}}" class="form-control" placeholder="Apply On Minimum Amount">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Use Limit</label>
                                    <input type="text" name="uses_limit" id="uses_limit" value="{{old('uses_limit')}}" class="form-control" placeholder="Use Limit">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="date" name="expiry_date" id="expiry_date" min="<?= date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day')) ?>" data-title="Expiry Date"  value="{{old('expiry_date')}}" class="form-control" placeholder="Expiry Date">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Submit
                                </button>

                                <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(1);">
                                    Cancel
                                </button>
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

        $('#regForm').validate({// initialize the plugin
            rules: {
                offer_type: {
                    required: true
                },
                type: {
                    required: true
                },
                coupon_code: {
                    required: true
                },
                title_en: {
                    required: true
                },
                title_ar: {
                    required: true
                },
                description_en: {
                    required: true
                },
                description_ar: {
                    required: true
                },
                discount: {
                    required: true,
                    number: true
                },
                min_amount: {
                    required: true,
                    number: true
                },
                uses_limit: {
                    required: true,
                    digits: true
                },
                expiry_date: {
                    required: true
                }
            }
        });

    });


    function setOfferType(obj) {
        var selected = $(obj).val();
        if (selected == 2) {
            $('#categoryDiv').show();
            $('#brandDiv').hide();
        } else if (selected == 3) {
            $('#brandDiv').show();
            $('#categoryDiv').hide();
        } else {
            $('#categoryDiv').hide();
            $('#brandDiv').hide();
        }
    }

    function setDiscountType(obj) {
        var selected = $(obj).val();
        if (selected == 1) {
            $('#discount_type_id').html('Discount %');
        } else if (selected == 2) {
            $('#discount_type_id').html('Discount Amount');
        } else {
            $('#discount_type_id').html('Discount');
        }
    }

</script>
@endsection