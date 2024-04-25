@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Images 
                    <a href="<?= url('admin/brands/upload_images'); ?>"><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Upload Images</button></a>
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/brands'); ?>">Brands</a></li>
                    <li class="breadcrumb-item active">Images</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        @if($images)
        @foreach($images as $image)
        <div class="col-md-6 col-xl-3">
            <div class="card m-b-30">
                <img class="card-img-top img-fluid" src="{{!empty($image)?asset($image->file_path).'/'.$image->file_name:asset('assets/images/no_img.png')}}" style="height: 150px!important;width:225px!important">
                <h4 class="card-title font-16 mt-0">{{$image->id}}</h4>                   
            </div>
        </div>
        @endforeach
        @endif
    </div>   
</div>
@endsection