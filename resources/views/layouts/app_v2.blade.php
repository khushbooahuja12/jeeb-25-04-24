<!DOCTYPE html>
<html class="no-js" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="index, follow" />
    <title>Jeeb: Groceries, Groceries &amp; Groceries</title>
    <meta name='robots' content='max-image-preview:large' />
    <link rel='dns-prefetch' href='http://fonts.googleapis.com/' />
    <link rel='dns-prefetch' href='http://s.w.org/' />
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('home_assets/uploads/2019/10/jeeb_square_logo.png') }}" />
    <script defer src="{{ asset('home_assets/plugins/fontawesome/js/fontawesome.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' id='jeeb-main-css' href="{{ asset('assets_v2/css/style.css?ver=1.0') }}"
        type='text/css' media='all' />
    <script type='text/javascript' src="{{ asset('home_assets/js/jquery/jquery.min9d52.js?ver=3.5.1') }}"
        id='jquery-core-js'></script>
    {{-- <script type='text/javascript' src="{{ asset('home_assets/js/jquery/jquery-migrate.mind617.js?ver=3.3.2') }}"
        id='jquery-migrate-js'></script> --}}
    <noscript>
        <style>
            /* .wpb_animate_when_almost_visible {
                opacity: 1;
            } */
        </style>
    </noscript>
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

<body data-rsssl=1
    class="page-template page-template-one-page page-template-one-page-php page page-id-13 wp-embed-responsive theme-nastik woocommerce-no-js wpb-js-composer js-comp-ver-6.6.0 vc_responsive">
    <!--loader-->
    {{-- <div class="loader-wrap color-bg">
        <div class="loader-bg"></div>
        <div class="loader-inner">
            <div class="loader"></div>
        </div>
    </div> --}}
    <!--loader end-->
    <!-- Main  -->
    <div id="main">
        <!-- header-->
        <header class="main-header">
            <!-- nav-button-wrap-->
            <div class="header-nav">
                <ul>
                    <li><a target="_blank" href="{{ route('home') }}">Home</a></li>
                    <li><a target="_blank" href="{{ route('home') }}">Blog</a></li>
                    <li><a target="_blank" href="{{ route('home') }}">Contact Us</a></li>
                </ul>
            </div>
            <!-- wrapper -->
            @yield('header-banner')
            <!--wrapper end -->
        </header>
        <!-- header end -->
        <!-- wrapper -->
        @yield('content')
        <!--wrapper end -->
        <!--footer-->
        <footer class="footer">
            <div class="block-1">
            </div>
            <div class="block-2">
                <div class="links-1">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About us</a></li>
                        <li><a href="#">How it works</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Contact us</a></li>
                    </ul>
                </div>
                <div class="links-2">
                    <h3 class="footer-title">Social Links</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa fa-facebook-f"></i> jeeb.qa</a></li>
                        <li><a href="#"><i class="fa fa-instagram"></i> jeeb.qa</a></li>
                    </ul>
                </div>
            </div>
        </footer>
        <!--footer  end -->
        <!-- cursor-->
        <div class="element">
            <div class="element-item" data-mouseback="#F68338" data-mouseborder="#F68338"></div>
        </div>
        <!-- cursor end-->
    </div>
    <!-- Main end -->
    {{-- <script type='text/javascript' src="{{ asset('home_assets/plugins/assets/js/frontend.minaeb9.js?ver=3.1.4') }}"
        id='ppress-frontend-script-js'></script> --}}
</body>

</html>
