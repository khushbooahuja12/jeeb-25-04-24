@extends('admin.layouts.dashboard_layout_for_affiliate_admin')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Affiliates Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/affiliates'); ?>">Affiliates</a></li>
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
                                <td>{{ $affiliates->name}}</td>                            
                            </tr>  
                            <tr>
                                <th>Email</th>
                                <td>{{ $affiliates->email }}</td>                            
                            </tr>  
                            <tr>
                                <th>Mobile</th>
                                <td>{{ $affiliates->mobile }}</td>                            
                            </tr>   
                            <tr>
                                <th>Ref Code</th>
                                <td>{{ $affiliates->code }}</td>                            
                            </tr>   
                            <tr>
                                <th>QR Code Image</th>
                                <td>
                                    @if ($affiliates->qr_code_image_url) 
                                        <img src="{{ '/storage/'.$affiliates->qr_code_image_url }}" width="auto" height="50"/>
                                    @endif
                                </td>                            
                            </tr>   
                            <tr>
                                <th>Ref URL</th>
                                <td>{{ $ref_url_base }}{{ $affiliates->code}}</td>                            
                            </tr>    
                            <tr>
                                <th>Status</th>
                                <td>{{ $affiliates->status == '1' ? 'Active' : 'Inactive' }}</td>                            
                            </tr>                        
                        </thead>                       
                    </table>

                    <br/>
                    <h4>Refered Users</h4>
                    <br/>
                    <h6>Total registered users: {{ $affiliates->userRegisteredMobiles->count() }}</h6>
                    <br/>
                    <table class="table table-bordered dt-responsive nowrap">
                        <tbody>
                            <tr>
                                <th>Phone Number</th>
                                <th>User registered</th>
                                <th>OTP verified</th>
                                <th>User Coupon</th>
                                <th>Coupon Used</th>
                            </tr> 
                            @foreach ($affiliates->userMobiles as $item) 
                                <tr>
                                    <td>{{ $item->user_mobile }}</td>
                                    <td>{{ $item->user_registered ? 'yes' : 'no' }}</td>
                                    <td>{{ $item->otp_verified ? 'yes' : 'no' }}</td>
                                    <td>{{ $item->user_coupon }}</td>
                                    <td>{{ $item->user_coupon_used ? 'yes' : 'no' }}</td>
                                </tr> 
                            @endforeach                      
                        </tbody>                       
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection