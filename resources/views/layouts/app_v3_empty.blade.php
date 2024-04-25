<!DOCTYPE html>
<html class="no-js" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
<meta charset="UTF-8">
<meta name="viewport"
    content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="robots" content="noindex, nofollow" />
@if(request()->route()->getName() == 'home')
  <title>Online Grocery Shopping and Doorstep Delivery in Qatar | Jeeb</title>
  <meta name="description" content="Order groceries online from Jeeb and get delivered from nearest store to your doorstep. An easiest, fast, and hassle free shopping experience for daily grocery needs!" />
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
</head>

<body style="background: rgba(15, 102, 223)">

    <!-- wrapper -->
    @yield('content')
    <!--wrapper end -->
    
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

</body>

</html>