@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Bulk upload images</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/products/images'); ?>">Product Images</a></li>
                    <li class="breadcrumb-item active">Bulk Upload</li>
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
                    <form method="post" id="createMultipleForm" enctype="multipart/form-data" action="{{route('admin.products.store_multiple_images')}}">
                        @csrf
                        <div class="row">                                                    
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Multiple Images</label>
                                    <input type="file" name="product_images[]" accept="image/*" class="form-control" multiple="multiple">                                    
                                </div>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Submit
                                    </button>
                                    <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(1);">
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
    $(document).ready(function () {
        $('#createMultipleForm').validate({
            rules: {
                brand_images: {
                    required: true
                }
            }
        });

    });
</script>
@endsection