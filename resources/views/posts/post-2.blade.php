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
            <li>5 foods to improve digestion</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <section class="sample-page">
      <div class="container" data-aos="fade-up">
        
        <div class="section-header">
            {{-- <span>Blog</span> --}}
            <h2>5 foods to improve digestion</h2>
        </div>
        <p><img src="{{ asset('assets_v3/uploads/5-foods-to-improve-digestion.png') }}" alt="" class="img-fluid"></p>
        <p>
          The digestive tract is essential to your health, as it's responsible for absorbing nutrients and eliminating waste.
          Unfortunately, many people suffer from digestive issues like bloating, cramping, gas, abdominal pain, diarrhea, and constipation for a variety of causes.
          However, even a healthy person can experience digestive problems due to a lack of fiber or probiotic-rich foods in their diet.
        </p>
        <p>
          Here are the ten best foods to improve your digestion.
          <br/><br/>
          1. Yogurt
          <br/><br/>
          Yogurt is made from milk that has been fermented,
          typically by lactic acid bacteria.
          It contains friendly bacteria known as probiotics, which
          are good bacteria that live in your digestive tract and can
          help improve digestion, keeping your gut healthy.
          Probiotics can help with digestive issues like bloating,
          constipation, and diarrhea. They have also been shown to
          improve the digestion of lactose or milk sugar.
          <br/><br/>
          2. Apples
          <br/><br/>
          Apples are a rich source of pectin, soluble fiber, and
          antioxidants.
          The pectin in apples helps increase stool bulk and movement
          through your digestive tract. It may also decrease
          inflammation in your colon
          <br/><br/>
          3. Chia seed
          <br/><br/>
          Chia seeds are an excellent source of fiber, which causes
          them to form a gelatin-like substance in your stomach
          once consumed. They work like a prebiotic, supporting the
          growth of healthy bacteria in your gut and contributing to
          healthy digestion.
          <br/><br/>
          4. Ginger
          <br/><br/>
          Ginger is a traditional ingredient in Eastern medicine that
          helps improve digestion and prevent nausea. In addition,
          many pregnant women use it to treat morning sickness.
          From a digestion viewpoint, this yellowish root has been
          shown to accelerate gastric emptying.
          <br/><br/>
          5. Salmon
          <br/><br/>
          Salmon is an excellent source of omega-3 fatty acids,
          which can help reduce inflammation in your body.
          People with inflammatory bowel disease, food
          intolerances, and other digestive disorders often have
          inflammation in the gut. Omega-3 fatty acids may help
          reduce this inflammation and thereby improve digestion.
          <br/><br/>
        </p>
        <p>
          <a href="{{ route('media') }}" class="readmore"><i class="bi bi-arrow-left"></i> <span>Back to Media</span></a>
        </p>
      </div>
    </section>

  </main><!-- End #main -->

@endsection