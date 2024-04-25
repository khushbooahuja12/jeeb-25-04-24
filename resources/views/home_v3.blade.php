@extends('layouts.app_v3')
@section('content')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
  <div class="container">
    <div class="row gy-4 d-flex justify-content-between">
      <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
        <h2 data-aos="fade-up">
          Think Groceries,<br/>
          Think Jeeb
        </h2>
        <p data-aos="fade-up" data-aos-delay="100">Jeeb is the solution to the traditional way of shopping for groceries by making it simple, fun, and quick.</p>
        <p data-aos="fade-up" data-aos-delay="100">
          <a target="_blank" href="https://play.google.com/store/apps/details?id=com.jeeb.user&hl=en_US&gl=US"><img class="homeheader-storeicon" src="{{asset('home_assets/uploads/2019/10/google-play-logo2.png')}}" alt="Play Store"/></a>
          <a target="_blank" href="https://apps.apple.com/qa/app/jeeb-grocery-service/id1614152924"><img class="homeheader-storeicon" src="{{asset('home_assets/uploads/2019/10/app-store2.png')}}" alt="App Store"/></a>
        </p>

        {{-- <div class="row gy-4" data-aos="fade-up" data-aos-delay="400">
          <div class="col-lg-3 col-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="1" class="purecounter"></span>
              <p>Clients</p>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="1" class="purecounter"></span>
              <p>Projects</p>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="1453" data-purecounter-duration="1" class="purecounter"></span>
              <p>Support</p>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="32" data-purecounter-duration="1" class="purecounter"></span>
              <p>Workers</p>
            </div>
          </div>
        </div> --}}
      </div>

      <div class="col-lg-5 order-1 order-lg-2 hero-img" data-aos="zoom-out">
        <img src="assets_v3/img/phone.webp" class="img-fluid mb-3 mb-lg-0" alt="">
      </div>

    </div>
  </div>
</section><!-- End Hero Section -->
<main id="main">
    
  <section>
    <div class="container-fluid">
       <div class="row">
          <div class="section-header">
            <span>Jeeb in the news</span>
            <h2>Jeeb in the news</h2>
          </div>
           <div class="swiper-container swiper-container1 py-2 ">
               <div class="swiper-wrapper">
                   <div class="swiper-slide styled--swiper" >
                       <a href="https://www.qatarday.com/in-a-matter-of-minutes-how-jeeb%E2%80%99s-2-step-tackles-large-orders" target="_blank">
                           <img src="{{ asset('assets_v3/img/news/1.png') }}" alt="medium" class="p-3" width="100%">
                       </a>
                   </div>
                   <div class="swiper-slide styled--swiper">
                       <a href="http://wgoqatar.com/jeebqatar" target="_blank">
                       <img src="{{ asset('assets_v3/img/news/2.png') }}" alt="news" class="p-3" width="100%">
                       </a>
                   </div>
                   <div class="swiper-slide styled--swiper">
                       <a href="https://m.thepeninsulaqatar.com/article/20/07/2023/jeebs-ceo-on-elevating-market-standards" target="_blank">
                       <img src="{{ asset('assets_v3/img/news/3.png ') }}" alt="parcel" class="p-3" width="100%">
                       </a>
                   </div>
                   <div class="swiper-slide styled--swiper">
                       <a href="https://s.alarab.qa/n/1578014" target="_blank">
                       <img src="{{ asset('assets_v3/img/news/4.png') }}" alt="evening_standard" class="p-3" width="100%">
                       </a>
                   </div>
                   <div class="swiper-slide styled--swiper" >
                    <a href="http://bitly.ws/HGy6" target="_blank">
                        <img src="{{ asset('assets_v3/img/news/5.png') }}" alt="medium" class="p-3" width="100%">
                    </a>
                </div>
                <div class="swiper-slide styled--swiper">
                    <a href="https://shrq.me/nbqild" target="_blank">
                    <img src="{{ asset('assets_v3/img/news/6.png') }}" alt="news" class="p-3" width="100%">
                    </a>
                </div>
               </div>
               <div class="swiper-pagination swiper-pagination3 d-none"></div>
           </div>
       </div>
    </div>
</section>

    <!-- ======= Qualities Section ======= -->
    <section id="about" class="featured-services">
        <div class="container">
  
          <div class="row gy-4">
  
            <div class="col-lg-4 col-md-6 service-item d-flex" data-aos="fade-up">
              <div class="icon flex-shrink-0">
                {{-- <i class="fa-solid fa-truck"></i> --}}
                <img class="img-icon" src="assets_v3/img/quick-and-safe-deliveries.webp" alt="Quick & Safe Deliveries"/>
              </div>
              <div>
                <h4 class="title">Quick & Safe Deliveries</h4>
                <p class="description">Jeeb will make sure that all your items will be taken care of during delivery and will come to you in one piece and on time</p>
                {{-- <a href="#" class="readmore stretched-link"><span>Learn More</span><i class="bi bi-arrow-right"></i></a> --}}
              </div>
            </div>
            <!-- End Service Item -->
  
            <div class="col-lg-4 col-md-6 service-item d-flex" data-aos="fade-up" data-aos-delay="100">
                <div class="icon flex-shrink-0">
                  {{-- <i class="fa-solid fa-cart-flatbed"></i> --}}
                  <img class="img-icon" src="assets_v3/img/handling-of-frozen-goods.webp" alt="Quick & Safe Deliveries"/>
                </div>
                <div>
                <h4 class="title">Handling of Frozen Goods</h4>
                <p class="description">Jeeb has a unique way of making sure your frozen items will stay frozen from the store to your home</p>
                {{-- <a href="#" class="readmore stretched-link"><span>Learn More</span><i class="bi bi-arrow-right"></i></a> --}}
              </div>
            </div><!-- End Service Item -->
  
            <div class="col-lg-4 col-md-6 service-item d-flex" data-aos="fade-up" data-aos-delay="200">
              <div class="icon flex-shrink-0">
                {{-- <i class="fa-solid fa-truck-ramp-box"></i> --}}
                <img class="img-icon" src="assets_v3/img/eco-friendly.webp" alt="Quick & Safe Deliveries"/>
              </div>
              <div>
                <h4 class="title">Eco-Friendly</h4>
                <p class="description">Jeeb cares about the environment and it reflects in our packaging where only biodegradable and recyclable bags are used</p>
                {{-- <a href="service-details.html" class="readmore stretched-link"><span>Learn More</span><i class="bi bi-arrow-right"></i></a> --}}
              </div>
            </div><!-- End Service Item -->
  
          </div>
  
        </div>
    </section><!-- End Qualities Section -->
  
    <!-- ======= About Us Section ======= -->
    <section id="about-us" class="about pt-0">
        <div class="container" data-aos="fade-up">
  
          <div class="row gy-4">
            <div class="col-lg-7 position-relative align-self-start order-lg-last order-first">
                <video autoplay muted loop id="promoVideo">
                    <source src="{{ asset('assets_v3/videos/how-we-operate.mp4') }}" type="video/mp4">
                </video>
                {{-- <img src="assets_v3/img/about.jpg" class="img-fluid" alt="">
              <a href="https://www.youtube.com/watch?v=LXb3EKWsInQ" class="glightbox play-btn"></a> --}}
            </div>
            <div class="col-lg-5 content order-last  order-lg-first">
              {{-- <h3>About Us</h3> --}}
              <br/>
              <p>
                Hi! We're Jeeb! The easiest grocery delivery app to hit Qatar.
              </p>
              <p>
                Created with safety in mind during the height of the pandemic, Jeeb's priorities evolved and now includes fast, easy, and hassle-free shopping.
                With a team of experts behind the app, Jeeb has made the grocery shopping experience much more accessible and interactive to anyone. 
              </p>
              <p>
                Jeeb will make sure that you can stock up on everything you need for the day, the week, or the whole month.
              </p>
              <p>
                And you won't even have to leave your house for it.
              </p>
              <ul>
                <li data-aos="fade-up" data-aos-delay="100">
                  <i class="bi bi-diagram-3"></i>
                  <div class="about-item">
                    <h5>Strategic logistical management</h5>
                    {{-- <p>Magni facilis facilis repellendus cum excepturi quaerat praesentium libre trade</p> --}}
                  </div>
                </li>
                <li data-aos="fade-up" data-aos-delay="200">
                  <i class="bi bi-broadcast"></i>
                  <div class="about-item">
                    <h5>Elementary app technology</h5>
                    {{-- <p>Quo totam dolorum at pariatur aut distinctio dolorum laudantium illo direna pasata redi</p> --}}
                  </div>
                </li>
                {{-- <li data-aos="fade-up" data-aos-delay="300">
                  <i class="bi bi-fullscreen-exit"></i>
                  <div>
                    <h5>Voluptatem et qui exercitationem</h5>
                    <p>Et velit et eos maiores est tempora et quos dolorem autem tempora incidunt maxime veniam</p>
                  </div>
                </li> --}}
              </ul>
            </div>
          </div>
  
        </div>
    </section><!-- End About Us Section -->
  
    <!-- ======= Media Section ======= -->
    <section id="media" class="services pt-0">
        <div class="container" data-aos="fade-up">
  
          <div class="section-header">
            <span>Media</span>
            <h2>Media</h2>
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
            
          </div>
  
        </div>
    </section><!-- End Media Section -->
  
    <!-- ======= Call To Action Section ======= -->
    {{-- <section id="call-to-action" class="call-to-action">
        <div class="container" data-aos="zoom-out">
  
          <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
              <h3>Call To Action</h3>
              <p> Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
              <a class="cta-btn" href="#">Call To Action</a>
            </div>
          </div>
  
        </div>
    </section> --}}
    <!-- End Call To Action Section --> 
    
    <!-- Area Of Operations -->
    <section class="operation-area">
        <img
            src="{{ asset('assets_v3/img/area-of-operations-wider.webp') }}"
            alt="Area Of Operations" class="operation-area-img"/>
    </section>
    <!-- End Area Of Operations -->

</main>
@endsection