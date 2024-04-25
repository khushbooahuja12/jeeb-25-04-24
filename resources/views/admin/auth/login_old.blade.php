@extends('admin.layouts.login_layout')
@section('content')
<div class="card card-pages shadow-none">
    <div class="card-body">
        <div class="text-center m-t-0 m-b-15">
            <!--<a href="<?= url('admin'); ?>" class="logo logo-admin"><img src="{{asset('assets/images/logo-dark.png')}}" alt="" height="24"></a>-->
            <h2><a href="<?= url('admin'); ?>" class="logo logo-admin">JEEB</a></h2>
        </div>
        <h5 class="font-18 text-center">Sign in to continue to Jeeb.</h5>
        @include('partials.errors')
        @include('partials.success')
        <form method="post" autocomplete="off" id="adminLoginForm" action="<?= url('admin/login_post'); ?>" class="form-horizontal m-t-30">
            @csrf
            <div class="form-group">
                <div class="col-12">
                    <label>Email</label>
                    <input class="form-control" name="email" value="{{old('email')}}" autocomplete="new-password" type="email" required="" placeholder="Email">
                </div>
            </div>
            <div class="form-group">
                <div class="col-12">
                    <label>Password</label>
                    <input class="form-control" name="password" value="{{old('password')}}" autocomplete="new-password" type="password" required="" placeholder="Password">
                </div>
            </div>
<!--            <div class="form-group">
                <div class="col-12">
                    <div class="checkbox checkbox-primary">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="customCheck1">
                            <label class="custom-control-label" for="customCheck1"> Remember me</label>
                        </div>
                    </div>
                </div>
            </div>-->
            <div class="form-group text-center m-t-20">
                <div class="col-12">
                    <button class="btn btn-primary btn-block btn-lg waves-effect waves-light" type="submit">Log In</button>
                </div>
            </div>
            <div class="form-group row m-t-30 m-b-0">
                <div class="col-sm-7">
                    <a href="#" class="text-muted"><i class="fa fa-lock m-r-5"></i> Forgot your password?</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection