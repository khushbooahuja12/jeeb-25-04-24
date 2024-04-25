@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Offer Option</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/news'); ?>">News</a></li>
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
                    <form method="post" id="editForm" enctype="multipart/form-data" action="{{route('admin.base_products.update_offer_option',['id' => $offer_option->id])}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" value="{{old('name',$offer_option->name)}}" class="form-control" placeholder="Name">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Base Price Percentage</label>
                                    <input type="text" name="base_price_percentage" value="{{old('base_price_percentage',$offer_option->base_price_percentage)}}" class="form-control" placeholder="Base Price Percentage">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Discount Percentage</label>
                                    <input type="text" name="discount_percentage" value="{{old('discount_percentage',$offer_option->discount_percentage)}}" class="form-control" placeholder="Discount Percentage">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>                                        
                                    </div>
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

        $('#editForm').validate({
            rules: {
                base_price_percentage: {
                    required: true
                },
                discount_percentage: {
                    required: true
                }
            }
        });

    });
</script>
@endsection