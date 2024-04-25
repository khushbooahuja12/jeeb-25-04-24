@extends('admin.layouts.login_layout')
@section('content')
<div class="row m-0">
  <div class="col-12 p-0">    
    <div class="login-card">
      <div>
        <div><a class="logo" href="<?= url('admin'); ?>"><img class="img-fluid for-light" src="{{ asset('assets_v4/images/logo/logo.webp')}}" width="15%" alt="loginpage"></a></div>
        <div class="login-main"> 
          <form class="theme-form" method="post" autocomplete="off" action="<?= url('admin/login_post'); ?>" id="adminLoginForm">
            @csrf
            <h4 class="text-center">Sign in to account</h4>
            <p class="text-center">Enter your email & password to login</p>
            @include('partials.errors')
            @include('partials.success')
            <div class="form-group">
              <label class="col-form-label">Email Address</label>
              <input class="form-control" type="email" name="email" value="{{old('email')}}" required="" placeholder="username@jeeb.tech">
            </div>
            <div class="form-group">
              <label class="col-form-label">Password</label>
              <div class="form-input position-relative">
                <input class="form-control" type="password" name="password" value="{{ old('password') }}" required="" placeholder="*********">
                <div class="show-hide"><span class="show"></span></div>
              </div>
            </div>
            <div class="form-group mb-0">
              <div class="checkbox p-0">
                <input id="checkbox1" type="checkbox">
                <label class="text-muted" for="checkbox1">Remember password</label>
              </div><a class="link" href="#">Forgot password?</a>
              <div class="text-end mt-3">
                <button class="btn btn-primary btn-block w-100" type="submit">Sign in</button>
              </div>
            </div>
            
            <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="#">Create Account</a></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
{{-- <div class="row">
  <div class="col-xl-7"><img class="bg-img-cover bg-center" src="https://jeeb.tech/assets_v3/img/area-of-operations-wider.webp" alt="loginpage"></div>
  <div class="col-xl-5 p-0">
    <div class="login-card">
      <div>
        <div><a class="logo" href="index.html"><img class="img-fluid for-light" src="{{ asset('assets_v4/images/logo/logo.webp')}}" width="15%" alt="loginpage"></a></div>
        <div class="login-main"> 
          <form class="theme-form" method="post" autocomplete="off" action="<?= url('admin/login_post'); ?>" id="adminLoginForm">
            @csrf
            <h4 class="text-center">Sign in to account</h4>
            <p class="text-center">Enter your email & password to login</p>
            @include('partials.errors')
            @include('partials.success')
            <div class="form-group">
              <label class="col-form-label">Email Address</label>
              <input class="form-control" type="email" name="email" value="{{old('email')}}" required="" placeholder="username@jeeb.tech">
            </div>
            <div class="form-group">
              <label class="col-form-label">Password</label>
              <div class="form-input position-relative">
                <input class="form-control" type="password" name="password" value="{{ old('password') }}" required="" placeholder="*********">
                <div class="show-hide"><span class="show"></span></div>
              </div>
            </div>
            <div class="form-group mb-0">
              <div class="checkbox p-0">
                <input id="checkbox1" type="checkbox">
                <label class="text-muted" for="checkbox1">Remember password</label>
              </div><a class="link" href="#">Forgot password?</a>
              <div class="text-end mt-3">
                <button class="btn btn-primary btn-block w-100" type="submit">Sign in</button>
              </div>
            </div>
            
            <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="#">Create Account</a></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div> --}}
@endsection