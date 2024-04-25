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
            <li>Jeeb and Startup Grind hosted a start-up event in Doha, Qatar</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <section class="sample-page">
      <div class="container" data-aos="fade-up">
        
        <div class="section-header">
            {{-- <span>Blog</span> --}}
            <h2>Jeeb and Startup Grind hosted a start-up event in Doha, Qatar</h2>
        </div>
        <p><img src="{{ asset('assets_v3/uploads/JEEB-event.jpg') }}" alt="" class="img-fluid"></p>
        <p>
          The event was held on 22 September 2022 at Workinton, Doha, Qatar. We were delighted to partner with Start-up Grind to help upcoming entrepreneurs take their ideas to the next level. The event was successful due to the rich participation of various industry persons and start-up founders. 
          <br/><br/>
          The major attraction of this event was that we arranged a contest for start-up ventures. It was a remarkable experience for us and the participants of the event. Many different types of start-ups presented their business models and details. The judging panel was Bashar Jaber (CEO & Founder of Jeeb), Heba Al Masri (Innovative Consultant at the ministry of communication & information technology), Keram Mergen (Country manager at Workinton), Steve Mackie (Founder & co-founder of Business start-up Qatar) and Marcel Dridje (Board Member of European business angel's network). The winner of the contest was Venly, a start-up for booking venues and events, and the winners were awarded 5000 QAR.
        </p>
        <p>
            <br clear="all"/>
            <a href="{{ route('media') }}" class="readmore"><i class="bi bi-arrow-left"></i> <span>Back to Media</span></a>
        </p>
      </div>
    </section>

  </main><!-- End #main -->

@endsection