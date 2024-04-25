@extends('vendor.layouts.login_layout')
@section('content')
<div class="card card-pages shadow-none">

    <div class="card-body">
        <div class="text-center m-t-0 m-b-15">
            <a href="<?= url('vendor/forgot_password'); ?>" class="logo logo-admin"><img src="assets/images/logo-dark.png" alt="" height="24"></a>
        </div>
        <h5 class="font-18 text-center">Reset Password</h5>
        @include('partials.errors')
        @include('partials.success')
        <form method="post" id="forgotPassForm" action="<?= url('vendor/send_password_reset_link'); ?>" autocomplete="off" class="form-horizontal m-t-30">
            @csrf
            <div class="col-12">
                <div class="alert alert-info alert-dismissible">
                    Enter your <b>Email</b> and instructions will be sent to you!
                </div>
            </div>
            <div class="form-group">
                <div class="col-12">
                    <label>Email</label>
                    <input class="form-control" name="email" value="{{old('email')}}" autocomplete="new-password" type="email" required="" placeholder="Email">
                </div>
            </div>
            <div class="form-group text-center m-t-20">
                <div class="col-12">
                    <button class="btn btn-primary btn-block btn-lg waves-effect waves-light" type="submit">Send Email</button>
                </div>
            </div>
            <div class="form-group row m-t-30 m-b-0">
                <div class="col-sm-6">
                    <a href="<?= url('vendor'); ?>" class="text-muted"><i class="fa fa-lock m-r-5"></i> Remember ? Login Now</a>
                </div>                
            </div>
        </form>
    </div>
</div>
@endsection