@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Sub Classification</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/classification_detail') . '/' . base64url_encode($classification->parent_id); ?>">Classification detail</a></li>
                    <li class="breadcrumb-item active">Edit Sub Classification</li>
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
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.categories.update_classification',[base64url_encode($classification->id)])}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Classification</label>
                                    <select class="form-control" name="parent_id">
                                        <option value="" selected disabled>Select</option>
                                        @if($parent_classifications)
                                        @foreach($parent_classifications as $key=>$value)
                                        <option value="{{$value->id}}" <?= $value->id == $classification->parent_id ? "selected" : ""; ?>>{{$value->name_en}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Sub Classification Name (En)</label>
                                    <input type="text" name="name_en" value="{{old('name_en')?old('name_en'):$classification->name_en}}" class="form-control" placeholder="Name (Eng)">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Sub Classification Name (Ar)</label>
                                    <input type="text" name="name_ar" value="{{old('name_ar')?old('name_ar'):$classification->name_ar}}" class="form-control" placeholder="Name (Arb)">
                                </div>   
                            </div>
                            <div class="col-lg-6">

                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Banner Image</label>
                                    <input type="file" accept="image/*"  name="banner_image" data-default-file="{{!empty($classification->getBannerImage)?asset('images/classification_images').'/'.$classification->getBannerImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Stamp Image</label>
                                    <input type="file" accept="image/*"  name="stamp_image" data-default-file="{{!empty($classification->getStampImage)?asset('images/classification_images').'/'.$classification->getStampImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Update
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(1);">
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
                name_en: {
                    required: true
                },
                name_ar: {
                    required: true
                },
                parent_id: {
                    required: true
                }
            }
        });

    });
</script>
@endsection