@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Brand Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('vendor/brands'); ?>">Brands</a></li>
                    <li class="breadcrumb-item active">Detail</li>
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
                    <div class="row">
                        <div class="col-md-6 mt-3">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Brand Name (En) </th>
                                    <td>{{$brand->brand_name_en}}</td>
                                </tr>
                                <tr>
                                    <th>Brand Name (Ar) </th>
                                    <td>{{$brand->brand_name_ar}}</td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle">Brand Image</th>
                                    <td><img src="{{!empty($brand->getBrandImage)?asset('images/brand_images').'/'.$brand->getBrandImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle">Brand Image 2</th>
                                    <td><img src="{{!empty($brand->getBrandImage2)?asset('images/brand_images').'/'.$brand->getBrandImage2->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                </tr> 
                                <tr>
                                    <th>Related Categories</th>
                                    <td>
                                        <?php
                                        if ($relatedCategories->count()) {
                                            foreach ($relatedCategories as $key => $value) {
                                                echo $value->getCategory->category_name_en . ', ';
                                            }
                                        } else {
                                            echo "N/A";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection