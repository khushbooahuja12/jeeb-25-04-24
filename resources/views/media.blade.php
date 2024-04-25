@extends('layouts.app_v3')
@section('content')

<main id="main">

    <!-- ======= Breadcrumbs ======= -->
    <div class="breadcrumbs">
      <div class="page-header d-flex align-items-center" style="background-image: url('assets_v3/img/media-bg.webp');">
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
            <li>Media</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <!-- ======= Blog Section ======= -->
    <section id="service" class="services pt-0">
      <div class="container" data-aos="fade-up">

        <div class="section-header">
          <span>Blog</span>
          <h2>Blog</h2>

        </div>

        <div class="row gy-4">
  
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/jeeb-bio-degradable-thumb.jpg') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="{{ route('media-single','100-biodegradable-with-jeeb') }}" class="stretched-link">100% Biodegradable with Jeeb</a></h3>
              <p>
                With grocery shopping getting easier online, it has been a massive increase in the use of plastic. People tap and order and get all their kitchen essentials at their doorsteps, packed in plastic boxes and bags. Meanwhile, Jeeb aims to deliver groceries in a way best for both customers and the environment. In the era of ever-increasing carbon emissions, Jeeb is one of the very first delivery apps to go carbon-neutral using 100% biodegradable packaging.
                <br/><br/>
                <a href="{{ route('media-single','100-biodegradable-with-jeeb') }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->
          
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/ready-to-cook-thumb.jpg') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="{{ route('media-single','top-5-ready-to-cook-items-on-Jeeb') }}" class="stretched-link">Top 5 Ready to Cook Items on Jeeb</a></h3>
              <p>
                Have a busy day but still want to eat a good and tasty meal? Don’t worry! Jeeb has a nice collection of ready-to-cook items that you can cook in minutes, serve in seconds, and enjoy their delicious taste.
                Out of the many ready-to-cook items available on Jeeb, we have prepared a list of five for you: pasta, noodles, cakes, custard, and soup. 
                <br/><br/>
                <a href="{{ route('media-single','top-5-ready-to-cook-items-on-Jeeb') }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->
          
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/what-really-differentiates-jeeb-thumb.jpg') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="{{ route('media-single','what-really-differentiates-jeeb') }}" class="stretched-link">What really differentiates Jeeb</a></h3>
              <p>
                Ordering groceries online has now become a trend. The introduction of the grocery delivery model keeps customers in a safe zone. Online grocery delivery is not a new approach; it has been growing in every corner of the world. It is easier, safer, and faster, so most families enjoy using instant grocery delivery apps. Jeeb, the best grocery delivery app, is known for its easy and quick delivery.
                <br/><br/>
                <a href="{{ route('media-single','what-really-differentiates-jeeb') }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->
          
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/jeeb-all-in-one-thumb.jpg') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="{{ route('media-single','what-makes-jeeb-on-top') }}" class="stretched-link">What makes Jeeb on Top</a></h3>
              <p>
                Our lives have become easier with the introduction of apps designed to assist users with daily tasks. We no longer need to leave our homes to fulfill our daily needs, including buying groceries, charging our electronics, making travel arrangements, and much more. Instead, we only need to use the apps to deliver our goods to our homes or other desired locations, which helps us save time.
                <br/><br/>
                <a href="{{ route('media-single','what-makes-jeeb-on-top') }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->
          
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/jeeb-get-connected-from-home-thumb.png') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="{{ route('media-single','get-connected-from-home-to-grocery-shop-with-jeeb') }}" class="stretched-link">Get Connected: From Home to Grocery Shop with Jeeb</a></h3>
              <p>
                In the old days, grocery shopping wasn’t easy, which took away our time and effort. Technology makes people’s day-to-day lives even better through its various features. Mobile applications have transformed the grocery buying process pretty well. Today, everything has become easy and convenient. It was no problem walking to the grocery store and waiting in line for payment.
                <br/><br/>
                <a href="{{ route('media-single','get-connected-from-home-to-grocery-shop-with-jeeb') }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->
          
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/jeeb-qatar-thumb.jpg') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="{{ route('media-single','why-moms-love-jeeb') }}" class="stretched-link">Why moms love Jeeb</a></h3>
              <p>
                Grocery shopping is the primary job of moms in every home. In the past, most families headed out to grocery stores to buy things for the entire month or week. As a mother, one of your responsibilities is to ensure that the groceries are fully stacked. In this technologically evolved era, no mom wants to leave home to buy any product from a grocery store because the online shopping culture has changed them to expect to receive any item at their doorsteps.
                <br/><br/>
                <a href="{{ route('media-single','why-moms-love-jeeb') }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->
          
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/jeeb-event-thumb.jpg') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="{{ route('media-single','start-up-event-in-qatar') }}" class="stretched-link">Jeeb and Startup Grind hosted a start-up event in Doha, Qatar</a></h3>
              <p>
                The event was held on 22 September 2022 at Workinton, Doha, Qatar. We were delighted to partner with Start-up Grind to help upcoming entrepreneurs take their ideas to the next level. The event was successful due to the rich participation of various industry persons and start-up founders. 
                <br/><br/>
                <a href="{{ route('media-single','start-up-event-in-qatar') }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->
          
          {{-- 
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/5-foods-to-improve-digestion.png') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="service-details.html" class="stretched-link">5 foods to improve digestion</a></h3>
              <p>
                The digestive tract is essential to your health, as it’s responsible for absorbing nutrients and eliminating waste. Unfortunately, many people suffer from digestive issues like bloating, cramping, gas, abdominal pain, diarrhea, and constipation for a variety of causes. However, even a healthy person can experience.. 
                <br/><br/>
                <a href="{{ route('media-single',2) }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="card">
              <div class="card-img">
                <img src="{{ asset('assets_v3/uploads/benefits-of-green-tea.png') }}" alt="" class="img-fluid">
              </div>
              <h3><a href="service-details.html" class="stretched-link">Benefits of Green Tea</a></h3>
              <p>
                Green tea is made from Camellia sinensis leaves, and buds that have not undergone the same withering and oxidation process used to make oolong and black teas. Green tea originated in China. Since then, its production and manufacture have spread to other countries in East Asia.
                Several green tea varieties differ substantially based on the type of C. Sinensis.. 
                <br/><br/>
                <a href="{{ route('media-single',3) }}" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
              </p>
            </div>
          </div><!-- End Card Item --> 
          --}}

        </div>

      </div>
    </section><!-- End Services Section -->

    <!-- ======= Press Section ======= -->
    <section id="service" class="services pt-0">
        <div class="container" data-aos="fade-up">
  
          <div class="section-header">
            <span>Press</span>
            <h2>Press</h2>
  
          </div>
  
          <div class="row gy-4">
  
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
              <div class="card">
                <div class="card-img">
                  <img src="{{ asset('assets_v3/uploads/watch-video.png') }}" alt="" class="img-fluid">
                </div>
                <h3><a href="service-details.html" class="stretched-link">Interview with CEO, Bashar Jaber</a></h3>
                <p>
                  Here is a snippet from a throwback interview with Start Up Of The Month featuring Bashar Jaber.
                  <br/><br/>
                  Check it out in the link below!
                  <br/><br/>
                  https://lnkd.in/dVwdDhQ7
                  <br/><br/>
                  <a href="https://lnkd.in/dVwdDhQ7" target="_blank" class="readmore stretched-link"><span>Watch Video</span> <i class="bi bi-arrow-right"></i></a>
                </p>
              </div>
            </div><!-- End Card Item -->
  
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
              <div class="card">
                <div class="card-img">
                  <img src="{{ asset('assets_v3/uploads/Jeeb-app-image-advertorial-910x600JEEB.jpg') }}" alt="" class="img-fluid">
                </div>
                <h3><a href="service-details.html" class="stretched-link">Use artificial intelligence to shop for your groceries in Qatar!</a></h3>
                <p>
                  Running on a tight schedule but in dire need of some fresh meat for this weekend’s barbecue night? Well, we have just what you need. Get a mega-market in the palm of your hand with this new application called Jeeb!
                  <br/><br/>
                  <a href="https://www.iloveqatar.net/news/technology/jeeb-online-grocery-shopping-app-qatar" target="_blank" class="readmore stretched-link"><span>Read More</span> <i class="bi bi-arrow-right"></i></a>
                </p>
              </div>
            </div><!-- End Card Item -->
  
          </div>
  
        </div>
    </section><!-- End Services Section -->

</main><!-- End #main -->

@endsection