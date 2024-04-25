@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Change Password</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Change Password</li>
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
                    <form method="post" id="regForm" enctype="multipart/form-data" action="{{route('vendor.dashboard.update')}}">
                        @csrf
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Enter Current Password</label>
                                <input type="password" name="current_password" id="current_password" data-title="Current Password" class="form-control regInputs" placeholder="Enter Current Password">
                                <p class="errorPrint" id="current_passwordError"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" id="new_password" data-title="New Password" class="form-control regInputs password" placeholder="New Password">
                                <p class="errorPrint" id="new_passwordError"></p>
                            </div>  
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="set_password" id="password" data-title="Confirm Password" class="form-control regInputs " placeholder="Confirm Password">
                                <p class="errorPrint" id="passwordError"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <button type="submit" id="submitBtn" onclick="saveData(this);" class="btn btn-primary waves-effect waves-light">
                                    Change Password
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
    $(document).ready(function () {        
        $('#regForm').validate({// initialize the plugin
            rules: {
                current_password: {
                    required: true
                },
                new_password: {
                    required: true,
                    minlength: 8
                },
                set_password: {
                    required: true,
                    equalTo: "#new_password"
                }
            }
        });

    });
</script>
@endsection
