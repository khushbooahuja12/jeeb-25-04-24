@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Banner Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/banners'); ?>">Banners</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">                
                <div class="card-body">
                    <table class="table table-bordered dt-responsive nowrap">
                        <thead>                            
                            <tr>
                                <th>Heading (Eng)</th>
                                <td>{{$banner->heading_en}}</td>                            
                            </tr>
                            <tr>
                                <th>Heading (Arb)</th>
                                <td>{{$banner->heading_ar}}</td>                            
                            </tr>
                            <tr>
                                <th>Description (Eng)</th>
                                <td>{{$banner->description_en}}</td>                            
                            </tr>
                            <tr>
                                <th>Description (Arb)</th>
                                <td>{{$banner->description_ar}}</td>                            
                            </tr>   
                            <tr>
                                <th>Name</th>
                                <td>{{$banner->banner_name}}</td>                            
                            </tr>  
                            <tr>
                                <th>Redirect Type</th>
                                <td>{{$banner->redirect_type == '1' ? 'Offer' : 'Product' }}</td>                            
                            </tr> 
                            @if($banner->redirect_type==3)
                            <tr>
                                <th>Classification</th>
                                <td>{{$banner->getClassification?$banner->getClassification->name_en:'' }}</td>                            
                            </tr> 
                            @endif
                            <tr>
                                <th style="vertical-align: middle">Image</th>
                                <td><img src="{{!empty($banner->getBannerImage)?asset('images/banner_images').'/'.$banner->getBannerImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                            </tr>    
                        </thead>                       
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#assignDriverForm').validate({
        rules: {
            driver_id: {
                required: true
            }
        }
    });
</script>
@endsection