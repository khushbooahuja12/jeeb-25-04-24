@extends('vendor.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Profile Settings</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/profile') ?>">Profile</a></li>
                        
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
                        <form method="post" id="regForm" enctype="multipart/form-data"
                            action="{{ route('update_profile') }}">
                            @csrf
                            <div class="row">
                               
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" id="name"
                                            value="{{ $user_data->name??'' }}" class="form-control"
                                            placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Store Name</label>
                                        <input type="text" name="store" id="store"
                                            value="{{ $store->name??'' }}" class="form-control"
                                            placeholder="Store Name" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label id="discount_type_id">Company Name</label>
                                        <input type="text" name="company" id="company"
                                            value="{{ $store->company_name??'' }}" class="form-control"
                                            placeholder="Company Name" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" id="email"
                                            value="{{ $user_data->email??'' }}" class="form-control"
                                            placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" name="password" id="password"
                                            value="12345678" class="form-control"
                                            placeholder="Password" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="confirm_password" id="confirm_password"
                                        value="12345678" class="form-control" placeholder="Password" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mobile</label>
                                        <input type="number" name="mobile" id="mobile"
                                            value="{{ $user_data->mobile??'' }}" class="form-control"
                                            placeholder="mobile">
                                    </div>
                                </div>
                               
                            </div>
                            <div class="form-group">
                                <div>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#confirm_password').on('keyup',function(){

            let pass = $('#password').val();
            if($(this).val() !== pass){


            }
        })
    </script>
    <script>
        $(document).ready(function() {
            $('#regForm').validate({ // initialize the plugin
                rules: {
                    offer_type: {
                        required: true
                    },
                    type: {
                        required: true
                    },
                    coupon_code: {
                        required: true
                    },
                    title_en: {
                        required: true
                    },
                    title_ar: {
                        required: true
                    },
                    description_en: {
                        required: true
                    },
                    description_ar: {
                        required: true
                    },
                    discount: {
                        required: true,
                        number: true
                    },
                    min_amount: {
                        required: true,
                        number: true
                    },
                    uses_limit: {
                        required: true,
                        digits: true
                    },
                    expiry_date: {
                        required: true
                    }
                }
            });
        });


    </script>
@endsection
