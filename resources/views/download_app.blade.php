@extends('layouts.app_v3_empty')
@section('content')
<style>
body {
  background: url(assets_v3/img/affiliates/bg.png) top center rgba(15, 102, 223) repeat !important;
}
</style>
<main id="main">

    <!-- ======= Download Form Section ======= -->
    <section id="service" class="services pt-0">
      <div class="container" data-aos="fade-up">

        <div class="row gy-4">
  
          <div class="col-lg-2 col-md-12"></div>
          
          <div class="col-lg-8 col-md-12" data-aos="fade-up" data-aos-delay="100">
            <div class="card" style="margin-top: 20%; background: #ffffff; text-align: center; padding: 20px 10px; border-radius: 30px;">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/logo.jpg') }}" alt="" class="img-fluid">
              </div>
              <h3>get unexpected <strong style="color: #3884EE; font-size: 20px;">rewards 🏆</strong></h3>
              {{-- <p style="margin-bottom: 0px;">
                You will receive a surprise coupon code for your first order..!
              </p> --}}
              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul style="padding-bottom: 0px; margin-bottom: 0px;">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                  </ul>
                </div>
              @endif
              <form method="post" action="{{route('download-app-submit')}}">
                  @csrf
                  {{-- <input style="max-width: 436px; display: block; margin: 10px auto 5px;" type="text" required minlength="8" maxlength="12"name="mobile" class="form-control" placeholder="Enter your mobile number with your country code"> --}}
                  {{-- <small>Eg: 97412345678</small> --}}
                  <div class="phone-number-field">
                    <div style="display: inline-block; margin: 10px 3px 5px 0px; height: 58px; line-height: 58px; background: #eeeeee; color: #666666; border-radius: 8px; padding: 0px 15px; max-width: 100%;">
                      <img style="height: 22px; width: auto;" src="assets_v3/img/affiliates/flag-qatar.webp" alt="Qatar Flag"/> +974
                    </div>
                    <input style="display: inline-block; margin: 10px auto 5px; height: 58px; line-height: 58px; width: 275px; max-width: 100%; background: #eeeeee;color: #666666; border: 0px;" type="text" required minlength="8" maxlength="12"name="mobile" class="form-control" placeholder="Enter your phone number">
                  </div>
                  <br/>
                  <input type="hidden" name="affiliate_code" value="{{ app('request')->input('ref_code') }}">
                  <input type="submit" name="download_play_store" value="" class="btn btn-primary waves-effect waves-light" style="background: url(assets_v3/img/affiliates/google-button.svg) top center no-repeat; height: 58px; width: 200px; max-width: 100%; border: 0px; margin: 2px; "/>
                  <input type="submit" name="download_app_store" value="" class="btn btn-primary waves-effect waves-light" style="background: url(assets_v3/img/affiliates/apple-button.svg) top center no-repeat; height: 58px; width: 200px; max-width: 100%; border: 0px; margin: 2px; "/>
              </form>
              <br/>
              <small>You'll have to use the same mobile number in your Jeeb App to avail this offer.</small>
            </div>
          </div><!-- End Card Item -->
          
          <div class="col-lg-2 col-md-12"></div>

        </div>

      </div>
    </section><!-- End Services Section -->

</main><!-- End #main -->

@endsection