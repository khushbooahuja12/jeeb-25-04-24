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
            <li>Benefits of Green Tea</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <section class="sample-page">
      <div class="container" data-aos="fade-up">
        
        <div class="section-header">
            {{-- <span>Blog</span> --}}
            <h2>Benefits of Green Tea</h2>
        </div>
        <p><img src="{{ asset('assets_v3/uploads/benefits-of-green-tea.png') }}" alt="" class="img-fluid"></p>
        <p>
          Green tea is made from Camellia sinensis leaves, and buds that have not undergone the same withering and oxidation process used to make oolong and black teas. Green tea originated in China. Since then, its production and manufacture have spread to other countries in East Asia.
          Several green tea varieties differ substantially based on the type of C. Sinensis used in growing conditions, horticultural methods, production processing, and harvest time.
          The two main components unique to green tea are "catechins" and "theanine," and the health effects of these components are attracting a great deal of attention in Japan and abroad.
          <br/><br/>
          <h3>Helps in the functioning of the brain:</h3>
          Besides preventing diseases and protecting cells, green tea also benefits the mind. Its caffeine acts as a stimulant, enhancing various aspects of brain functioning, including improved mood, memory, etc. The drink also contains the amino acid L-theanine, which works with caffeine for a better effect.
          <br/><br/>
          <h3>Lowers the risk of cancer and various harmful diseases:</h3>
          Cancer is still a significant threat to human life. However, it is proven that increasing the level of antioxidants usually protects against this disease. Green tea is known to all to provide a high quotient of antioxidants. It thus aids in reducing the cause of harmful cancerous diseases like breast, colorectal, and prostate cancer.
          <br/><br/>
          <h3>Lowers various bacterial infections:</h3>
          The catechins in green tea also consist of organics that prevent many bacterial infections. These catechins are essential in destroying harmful bacteria, lessening the chances of various diseases or viruses like influenza.
          <br/><br/>
          <h3>Helps to overcome obesity:</h3>
          Studies have shown that green tea assists in curbing weight gain and overcoming the increasing problem of obesity. It has been proven to reduce excess abdominal fat and other unwanted fat. The drink also increases the metabolic rate, thus kick-starting weight loss.
          <br/><br/>
          <h3>Reduces the risk of diabetes:</h3>
          It is now known that diabetes is a fast-spreading epidemic affecting millions of people. One of the causes is the spike in sugar levels. Research has clearly shown that green tea can successfully improve insulin activity and regulate the sugar level in the body.
        </p>
        <p>
            <a href="{{ route('media') }}" class="readmore"><i class="bi bi-arrow-left"></i> <span>Back to Media</span></a>
        </p>
      </div>
    </section>

  </main><!-- End #main -->

@endsection