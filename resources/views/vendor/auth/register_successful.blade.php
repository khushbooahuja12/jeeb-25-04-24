@extends('vendor.layouts.login_layout')
@section('content')
<style>
    .wrapper-page {
        width: 600px;
    }
</style>
<!--<div class="card card-pages shadow-none">-->
    <div class="card-body">
        <div class="text-center m-t-0 m-b-15 text-white">
            
            <h2><a class="logo logo-vendor">JEEB</a></h2>
            <p class="mt-5"><i class="fa fa-check-circle fa-7x"></i></p>
            <h3>Your registration is successful! </h3>
            <h6>Please wait while your profile is under verification</h6>
            <a href="<?= url('vendor'); ?>" class="text-white"><u> Back to login</u></a>
        </div>
        
    </div>
<!--</div>-->
@endsection