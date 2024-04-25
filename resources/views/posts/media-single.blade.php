@extends('layouts.app_v3')
@section('content')

<main id="main">

    <!-- ======= Breadcrumbs ======= -->
    <div class="breadcrumbs">
      <div class="page-header d-flex align-items-center" style="background-image: url('{{ asset('assets_v3/img/media-bg.webp') }}');">
        <div class="container position-relative">
          <div class="row d-flex justify-content-center">
            <div class="col-lg-6 text-center">
              <h2>Media</h2>
              <p>&nbsp;</p>
            </div>
          </div>
        </div>
      </div>
      <nav>
        <div class="container">
          <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('media') }}">Media</a></li>
            <li>Not found!</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <section class="sample-page">
      <div class="container" data-aos="fade-up">
        
        <div class="section-header">
            {{-- <span>Blog</span> --}}
            <h2>Not found!</h2>
        </div>
        <p>
          <a href="{{ route('media') }}" class="readmore stretched-link"><i class="bi bi-arrow-left"></i> <span>Back to Media</span></a>
        </p>
      </div>
    </section>

  </main><!-- End #main -->

@endsection