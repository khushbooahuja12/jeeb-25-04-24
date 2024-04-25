@extends('vendor.layouts.login_layout')
@section('content')
<div class="card card-pages shadow-none">
    <div class="card-body">
        <div class="text-center m-t-0 m-b-15">
            <a href="#" class="logo logo-admin"><img src="{{asset('assets/images/logo-dark.png')}}" alt="" height="24"></a>
        </div>
        <h5 class="font-18 text-center">Reset Password.</h5>
        <form method="post" id="resetPassForm" action="<?= url('vendor/set_new_password'); ?>" class="form-horizontal m-t-30">
            @csrf
            <input type="hidden" name="token" value="{{$token}}">
            <div class="form-group">
                <div class="col-12">
                    <label>New Password</label>
                    <input class="form-control" name="password" value="{{old('password')}}" type="password" id="exampleInputPassword1" required="" placeholder="New Password">
                </div>
            </div>
            <div class="form-group">
                <div class="col-12">
                    <label>Confirm Password</label>
                    <input class="form-control" name="cpassword" value="{{old('cpassword')}}" type="password" required="" placeholder="Confirm Password">
                </div>
            </div>            
            <div class="form-group text-center m-t-20">
                <div class="col-12">
                    <button class="btn btn-primary btn-block btn-lg waves-effect waves-light" type="submit">Reset Password</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection