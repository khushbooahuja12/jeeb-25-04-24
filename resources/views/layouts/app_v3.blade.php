<!DOCTYPE html>
<html class="no-js" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
<meta charset="UTF-8">
<meta name="viewport"
    content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="robots" content="index, follow" />
@if(request()->route()->getName() == 'home')
  <title>Online Grocery Shopping and Doorstep Delivery in Qatar | Jeeb</title>
  <meta name="description" content="Order groceries online from Jeeb and get delivered from nearest store to your doorstep. An easiest, fast, and hassle free shopping experience for daily grocery needs in Qatar." />
  <link rel="canonical" href="https://jeeb.tech" />
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Jeeb",
      "url": "https://www.jeeb.tech",
      "logo": "https://jeeb.tech/assets_v3/img/logo.webp",
      "sameAs": [
        "https://www.facebook.com/jeebqa/",
        "https://twitter.com/Jeeb_Groceries/",
        "https://www.instagram.com/jeebqa/?hl=en",
        "https://www.linkedin.com/company/jeebgroceries/"
      ]
    }
  </script>
@elseif(Request::is('privacy-policy'))
  <title>Privacy Policy - Jeeb | Qatar</title>
  <meta name="description" content="At Jeeb.tech we do care about our customers privacy. Read our privacy policy here" />
@elseif(Request::is('terms-and-conditions'))
  <title>Terms and conditions - Jeeb | Qatar</title>
  <meta name="description" content="Read our terms and conditions for using jeeb.tech" />
@elseif(Request::is('media'))
  <title>Jeeb Blog - Delivery guidance, updates and news</title>
  <meta name="description" content="Guidance on delivery services in Qatar. Stay tuned for updates about Jeeb, helpful tips and information about groceries." />
@elseif(Request::is('contact-us'))
  <title>Contact us - Jeeb | Qatar</title>
  <meta name="description" content="Jeeb is a simple grocery delivery service which is operating in Qatar. Jeeb delivers groceries from the store to your doorstep in Qatar." />
@elseif(Request::is('blog/why-moms-love-jeeb'))
  <title>Why moms love Jeeb</title>
  <meta name="description" content="Overview of the benefits of having a grocery app on phone and how it can be helpful for all moms. Download the Jeeb app for easy online grocery shopping in Qatar." />
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://jeeb.tech/blog/why-moms-love-jeeb"
      },
      "headline": "Why moms love Jeeb",
      "image": "https://jeeb.tech/assets_v3/uploads/jeeb-qatar.jpeg",  
      "author": {
        "@type": "Organization",
        "name": ""
      },  
      "publisher": {
        "@type": "Organization",
        "name": "",
        "logo": {
          "@type": "ImageObject",
          "url": "https://jeeb.tech/assets_v3/img/logo-white-2.webp"
        }
      },
      "datePublished": "2022-11-21",
      "dateModified": "2022-11-21"
    }
  </script>
@else
  <title>Jeeb: Groceries, Groceries &amp; Groceries</title>
  <meta name="description" content="" />
@endif
<meta name='robots' content='max-image-preview:large' />
<link rel='dns-prefetch' href='http://fonts.googleapis.com/' />
<link rel='dns-prefetch' href='http://s.w.org/' />
    
<!-- Favicons -->
<link href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" rel="icon">
<link href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
<link rel="shortcut icon" type="image/png" href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" />

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

<!-- Vendor CSS Files -->
<link href="{{ asset('assets_v3/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets_v3/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets_v3/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets_v3/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets_v3/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets_v3/vendor/aos/aos.css') }}" rel="stylesheet">

<!-- Template Main CSS File -->
<link href="{{ asset('assets_v3/css/main.css') }}" rel="stylesheet">
<link href="{{ asset('assets_v3/css/custom.css?v1.1') }}" rel="stylesheet">

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-886F7KNBL5"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-886F7KNBL5');
</script>

<!-- Snap Pixel Code --> 
<script type='text/javascript'> (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function() {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)}; a.queue=[];var s='script';r=t.createElement(s);r.async=!0; r.src=n;var u=t.getElementsByTagName(s)[0]; u.parentNode.insertBefore(r,u);})(window,document, 'https://sc-static.net/scevent.min.js'); snaptr('init', '01441647-3896-43b9-afdb-cbdbb3e54214', { 'user_email': 'jeeb.socialmedia@gmail.com' }); snaptr('track', 'PAGE_VIEW'); </script> 
<!-- End Snap Pixel Code -->

<!-- google reaptcha -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header d-flex align-items-center fixed-top">
      <div class="container-fluid container-xl d-flex align-items-center justify-content-between">
  
        <a href="{{ route('home') }}" class="logo d-flex align-items-center">
          <img src="{{ asset('assets_v3/img/logo-white-2.webp') }}" alt="">
          {{-- <h1>Jeeb</h1> --}}
        </a>
  
        <i class="mobile-nav-toggle mobile-nav-show bi bi-list"></i>
        <i class="mobile-nav-toggle mobile-nav-hide d-none bi bi-x"></i>
        <nav id="navbar" class="navbar">
          <ul>
            <li><a href="{{ route('home') }}" class="active">Home</a></li>
            <li><a href="{{ route('about') }}">About us</a></li>
            <li><a href="{{ route('media') }}" class="">Media</a></li>
            <li><a href="{{ route('contact') }}" class="">Contact Us</a></li>
          </ul>
        </nav><!-- .navbar -->
  
      </div>
    </header><!-- End Header -->
    <!-- End Header -->
  
    <!-- wrapper -->
    @yield('content')
    <!--wrapper end -->
    
  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">

    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-6 col-md-12 footer-info">
          <a href="{{ route('home') }}" class="logo d-flex align-items-center">
            <img src="{{ asset('assets_v3/img/logo.webp') }}" alt="">
            {{-- <span>Jeeb</span> --}}
          </a>  
          {{-- <p>Founded in the midst of the COVID-19 pandemic, Jeeb aims to tackle the barriers and complications between day-to-day average grocery consumers, and their needs.</p> --}}
          {{-- <div class="social-links d-flex mt-4">
            <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
            <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
          </div> --}}
        </div>

        <div class="col-lg-2 col-4 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('about') }}">About us</a></li>
            {{-- <li><a href="#">How it works</a></li> --}}
            <li><a href="{{ route('media') }}">Media</a></li>
            <li><a href="{{ route('contact') }}">Contact us</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-4 footer-links">
          <h4>Privacy</h4>
          <ul>
            <li><a href="{{ route('terms') }}">Terms of service</a></li>
            <li><a href="{{ route('privacy-policy') }}">Privacy policy</a></li>
            <li><a href="{{ route('faq') }}">FAQ</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-4 footer-links social-links-menu">
          <h4>Social Links</h4>
          <ul>
            {{-- <li><a href="#" class="twitter"><i class="bi bi-twitter"></i></a></li> --}}
            <li><a href="{{ $social_links['facebook'] }}" target="_blank" class="facebook"><i class="bi bi-facebook"></i> Facebook</a></li>
            <li><a href="{{ $social_links['instagram'] }}" target="_blank" class="instagram"><i class="bi bi-instagram"></i> Instagram</a></li>
            <li><a href="{{ $social_links['linkedin'] }}" target="_blank" class="linkedin"><i class="bi bi-linkedin"></i> LinkedIn</a></li>
          </div>
        </div>

        {{-- <div class="col-lg-3 col-md-12 footer-contact text-center text-md-start">
          <h4>Contact Us</h4>
          <p>
            A108 Adam Street <br>
            New York, NY 535022<br>
            United States <br><br>
            <strong>Phone:</strong> +1 5589 55488 55<br>
            <strong>Email:</strong> info@example.com<br>
          </p>

        </div> --}}

      </div>
    </div>
    <hr/>

    <div class="container mt-4">
      <div class="copyright">
        &copy; Copyright <strong><span>Jeeb</span> {{ date('Y') }}</strong>. All Rights Reserved
      </div>
    </div>

  </footer><!-- End Footer -->
  <!-- End Footer -->

  <a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets_v3/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets_v3/vendor/purecounter/purecounter_vanilla.js') }}"></script>
  <script src="{{ asset('assets_v3/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('assets_v3/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('assets_v3/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('assets_v3/vendor/php-email-form/validate.js') }}"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('assets_v3/js/main.js') }}"></script>
  <script>
    var swiper=new Swiper('.swiper-container1',{
        slidesPerView: 4,
        spaceBetween: 30,
        slidesPerGroup: 1,
        loop: true,
        loopFillGroupWithBlank: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination3',
            clickable: true,
        },

        breakpoints: {
            320: {
                slidesPerView: 2,
                spaceBetween: 5,
            },

            640: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 10,
            },
            1024: {
                slidesPerView: 5,
                spaceBetween: 10,
            },
        }
    })
</script>
</body>

</html>