@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Brand</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/brands'); ?>">Brands</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.brands.update',[base64url_encode($brand->id)])}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Brand Name (En)</label>
                                    <input type="text" name="brand_name_en" value="{{$brand->brand_name_en}}" class="form-control" placeholder="Brand Name (Eng)">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Brand Name (Ar)</label>
                                    <input type="text" name="brand_name_ar" value="{{$brand->brand_name_ar}}" class="form-control" placeholder="Brand Name (Arb)">
                                </div> 
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Brand Image</label>
                                    <input type="file" accept="image/*"  name="brand_image" data-show-remove="false" data-default-file="{{!empty($brand->getBrandImage)?asset('images/brand_images').'/'.$brand->getBrandImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Brand Image 2</label>
                                    <input type="file" accept="image/*"  name="brand_image2" data-show-remove="false" data-default-file="{{!empty($brand->getBrandImage2)?asset('images/brand_images').'/'.$brand->getBrandImage2->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Update
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(2);">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div> <!-- end col -->       
    </div> <!-- end row -->      
</div>
<script>
    $(document).ready(function () {
        $('#addForm').validate({
            rules: {
                brand_name_en: {
                    required: true
                },
                brand_name_ar: {
                    required: true
                }
            }
        });

    });
</script>
@endsection