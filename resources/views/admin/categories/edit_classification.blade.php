@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Classification</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/classifications'); ?>">Classifications</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.categories.update_classification',[base64url_encode($classification->id)])}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Classification Name (En)</label>
                                    <input type="text" name="name_en" value="{{$classification->name_en}}" class="form-control" placeholder="Name (Eng)">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Classification Name (Ar)</label>
                                    <input type="text" name="name_ar" value="{{$classification->name_ar}}" class="form-control" placeholder="Name (Arb)">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Banner Image</label>
                                    <input type="file" accept="image/*"  name="banner_image" data-show-remove="false" data-default-file="{{!empty($classification->getBannerImage)?asset('images/classification_images').'/'.$classification->getBannerImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Stamp Image</label>
                                    <input type="file" accept="image/*"  name="stamp_image" data-show-remove="false" data-default-file="{{!empty($classification->getStampImage)?asset('images/classification_images').'/'.$classification->getStampImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
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

        $('#addForm').validate({// initialize the plugin
            rules: {
                classification_name_en: {
                    required: true
                },
                classification_name_ar: {
                    required: true
                }
            }
        });

    });
</script>
@endsection