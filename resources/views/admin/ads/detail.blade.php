@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Ads Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/ads'); ?>">Ads</a></li>
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
                                <th>Name</th>
                                <td>{{$ads->name}}</td>                            
                            </tr>  
                            <tr>
                                <th>Redirect Type</th>
                                <td>{{$ads->redirect_type == '1' ? 'Offer' : 'Product' }}</td>                            
                            </tr>   
                            <tr>
                                <th style="vertical-align: middle">Image</th>
                                <td><img src="{{!empty($ads->getAdsImage)?asset('images/ads_images').'/'.$ads->getAdsImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                            </tr>                         
                        </thead>                       
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection