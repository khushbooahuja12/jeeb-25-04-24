@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Ads</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/ads'); ?>">Ads</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.ads.update',[base64url_encode($ads->id)])}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="ads_name" value="{{$ads->name}}" class="form-control" placeholder="Name">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Redirect Type</label>
                                    <select name="redirect_type" id="redirect_type"  data-title="Redirect Type" class="form-control regInputs">
                                        <option value="" disabled>Select Type</option>
                                        <option value="1" {{$ads->redirect_type  == '1' ? 'selected' : ''}} >Offer</option>
                                        <option value="2" {{$ads->redirect_type  == '2' ? 'selected' : ''}}>Product</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Image</label>
                                    <input type="file" name="ads_image" accept="image/*"  data-show-remove="false" data-default-file="{{!empty($ads->getAdsImage)?asset('images/ads_images').'/'.$ads->getAdsImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
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
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#addForm').validate({
            rules: {
                ads_name:{
                    required: true
                }
            }
        });
    });
</script>
@endsection