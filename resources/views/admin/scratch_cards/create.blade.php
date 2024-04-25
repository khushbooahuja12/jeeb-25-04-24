@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add New Scratch Card</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/scratch_cards'); ?>">Scratch Cards</a></li>
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
                    <form method="post" id="regForm" enctype="multipart/form-data" action="{{route('admin.scratch_cards.store')}}">
                        @csrf
                        <div class="row" style="background: #f7f2c6; padding-top: 10px;">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Apply on</label>
                                    <select name="apply_on" id="apply_on" class="form-control">
                                        <option value="">Select</option>
                                        <option value="register">Register First Time</option>
                                        <option value="order">New Order</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Minimum Order Amount</label>
                                    <input type="text" name="apply_on_min_amount" id="apply_on_min_amount" value="{{old('apply_on_min_amount')}}" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <br clear="all"/><br/>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Scratch Card Image</label>
                                    <input type="file" name="image" class="form-control dropify" accept="image/*">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Scratch Card Image (Ar)</label>
                                    <input type="file" name="image_ar" class="form-control dropify" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Scratch Card Title</label>
                                    <input type="text" name="title_en" id="email" value="{{old('title_en')}}" class="form-control" placeholder="Scratch Card Title">
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Scratch Card Title (Ar)</label>
                                    <input type="text" name="title_ar" id="mobile" value="{{old('title_ar')}}" class="form-control" placeholder="Scratch Card Title (Ar)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Scratch Card Description</label>
                                    <input type="text" name="description_en" id="description_en" value="{{old('description_en')}}" class="form-control" placeholder="Scratch Card Description">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Scratch Card Description (Ar)</label>
                                    <input type="text" name="description_ar" id="description_ar" value="{{old('description_ar')}}" class="form-control" placeholder="Scratch Card Description (Ar)">                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Scratch Card Type</label>
                                    <select name="scratch_card_type" id="scratch_card_type" class="form-control" onchange="setScratchCardType(this);">
                                        <option value="">Select Offer On</option>
                                        <option value="0">Onspot Reward</option>
                                        <option value="1">Coupon</option> 
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Offer By</label>
                                    <select name="type" id="type" class="form-control" onchange="setDiscountType(this);">
                                        <option value="">Select Offer Type</option>
                                        <option value="1">Offer By Percentage</option>
                                        <option value="2">Offer By Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label id="discount_type_id">Offer</label>
                                    <input type="text" name="discount" id="discount" value="{{old('discount')}}" class="form-control" placeholder="Offer Amount">
                                </div>
                            </div>
                            <div class="col-md-4" id="minimumAmountDiv" style="display:none">
                                <div class="form-group">
                                    <label>Apply On Minimum Amount</label>
                                    <input type="text" name="min_amount" id="min_amount"  value="{{old('min_amount')}}" class="form-control" placeholder="Apply On Minimum Amount">
                                </div>
                            </div>
                            <div class="col-md-4" id="useLimitDiv" style="display:none">
                                <div class="form-group">
                                    <label>Use Limit</label>
                                    <input type="text" name="uses_limit" id="uses_limit" value="{{old('uses_limit')}}" class="form-control" placeholder="Use Limit">
                                </div>
                            </div>
                            <div class="col-md-4" id="expiryDiv" style="display:none">
                                <div class="form-group">
                                    <label>Expiry In (Days)</label>
                                    <input type="number" name="expiry_in" id="expiry_in"  data-title="Expiry In"  value="{{old('expiry_in')}}" class="form-control" placeholder="Expiry In">
                                </div>
                            </div>
                            {{-- <div class="col-md-4" id="categoryDiv" style="display:none">
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
                            </div>  --}}
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
                rules: {
                    scratch_card_type: {
                        required: true
                    },
                    type: {
                        required: true
                    },
                    title_en: {
                        required: true
                    },
                    title_ar: {
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
                    }
                }
            }
        });

    });

    function setScratchCardType(obj) {
        var selected = $(obj).val();
        if (selected == 1) {
            $('#categoryDiv').show();
            $('#brandDiv').show();
            $('#minimumAmountDiv').show();
            $('#useLimitDiv').show();
            $('#expiryDiv').show();
        } else {
            $('#categoryDiv').hide();
            $('#brandDiv').hide();
            $('#minimumAmountDiv').hide();
            $('#useLimitDiv').hide();
            $('#expiryDiv').hide();
        }
    }

    function setDiscountType(obj) {
        var selected = $(obj).val();
        if (selected == 1) {
            $('#discount_type_id').html('Offer %');
        } else if (selected == 2) {
            $('#discount_type_id').html('Offer Amount');
        } else {
            $('#discount_type_id').html('Offer');
        }
    }

</script>
@endsection