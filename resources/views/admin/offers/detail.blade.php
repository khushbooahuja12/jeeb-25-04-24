@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Offer Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/offers'); ?>">Offers</a></li>
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
                                <td>{{$offer->heading_en}}</td>                            
                            </tr>
                            <tr>
                                <th>Heading (Arb)</th>
                                <td>{{$offer->heading_ar}}</td>                            
                            </tr>
                            <tr>
                                <th>Description (Eng)</th>
                                <td>{{$offer->description_en}}</td>                            
                            </tr>
                            <tr>
                                <th>Description (Arb)</th>
                                <td>{{$offer->description_ar}}</td>                            
                            </tr>         
                            <tr>
                                <th style="vertical-align: middle">Image</th>
                                <td><img src="{{!empty($offer->getOfferImage)?asset('images/offer_images').'/'.$offer->getOfferImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
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