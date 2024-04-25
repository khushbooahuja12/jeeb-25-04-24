<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Enzo admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Enzo admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" type="image/x-icon">
    <title>Jeeb - Admin Panel</title>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/font-awesome.css') }}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/icofont.css')}}">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/themify.css')}}">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/flag-icon.css')}}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/feather-icon.css')}}">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/scrollbar.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/animate.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/chartist.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/date-picker.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/prism.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/vector-map.css')}}">
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/vendors/bootstrap.css')}}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/style.css')}}">
    <link id="color" rel="stylesheet" href="{{ asset('assets_v4/css/color-1.css')}}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets_v4/css/responsive.css')}}">
    <link rel="stylesheet" type="text/css" href="https://jeremyfagis.github.io/dropify/dist/css/dropify.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
  </head>
  <body onload="notifyMe()">
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- Loader starts-->
    <div class="loader-wrapper">
      <div class="loader"></div>
    </div>
    <!-- Loader ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <!-- Page Header Start-->
      <div class="page-header">
        <div class="header-wrapper row m-0">
          <form class="form-inline search-full col" action="#" method="get">
            <div class="form-group w-100">
              <div class="Typeahead Typeahead--twitterUsers">
                <div class="u-posRelative">
                  <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search In Enzo .." name="q" title="" autofocus>
                  <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...</span></div><i class="close-search" data-feather="x"></i>
                </div>
                <div class="Typeahead-menu"></div>
              </div>
            </div>
          </form>
          <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"><a href="index.html"><img class="img-fluid" src="../assets/images/logo/login.png" alt=""></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
          </div>
          <div class="left-header col horizontal-wrapper ps-0">
            <h5 class="lan-1" style="margin-bottom: 0rem;">{{ $company->name }}</h5>
          </div>
          <div class="nav-right col-8 pull-right right-header p-0">
            <ul class="nav-menus">             
              <li>
                <div class="mode"><i class="fa fa-moon-o"></i></div>
              </li>
              <li class="maximize"><a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()"><i data-feather="maximize"></i></a></li>
              <li class="maximize"><a class="text-dark" href="<?= url('admin/logout') ?>" ><i data-feather="log-out"></i></a></li>
              <li class="profile-nav p-0 me-0"></li>
            </ul>
          </div>
          <script class="result-template" type="text/x-handlebars-template">
            <div class="ProfileCard u-cf">                        
            <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
            <div class="ProfileCard-details">
            <div class="ProfileCard-realName"></div>
            </div>
            </div>
          </script>
          <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
        </div>
      </div>
      <!-- Page Header Ends                              -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        @include('admin.layouts.sidebar_items_for_vendor_master_panel')
        <!-- Page Sidebar Ends-->
        @yield('content')
        <!-- Modal--> 
        <div class="modal" tabindex="-1" id="order_notification_popup">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <h5>Order Received</h5>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal" tabindex="-1" id="order_cancelled_notification_popup">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <h5>Order</h5> <h5 id="cancelled_order_number"></h5> <h5>Cancelled</h5>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        <!-- footer start-->
        <footer class="footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6 p-0 footer-left">
                <p class="mb-0">Copyright © 2023 Jeeb. All rights reserved.</p>
              </div>
              
            </div>
          </div>
        </footer>
      </div>
    </div>
    <!-- latest jquery-->
    <script src="{{ asset('assets_v4/js/jquery-3.6.0.min.js')}}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('assets_v4/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('assets_v4/js/icons/feather-icon/feather.min.js')}}"></script>
    <script src="{{ asset('assets_v4/js/icons/feather-icon/feather-icon.js')}}"></script>
    <!-- scrollbar js-->
    <script src="{{ asset('assets_v4/js/scrollbar/simplebar.js')}}"></script>
    <script src="{{ asset('assets_v4/js/scrollbar/custom.js')}}"></script>
    <!-- Sidebar jquery-->
    <script src="{{ asset('assets_v4/js/config.js')}}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('assets_v4/js/sidebar-menu.js')}}"></script>
    <script src="{{ asset('assets_v4/js/chart/chartist/chartist.js')}}"></script>
    <script src="{{ asset('assets_v4/js/chart/chartist/chartist-plugin-tooltip.js')}}"></script>
    <script src="{{ asset('assets_v4/js/chart/knob/knob.min.js')}}"></script>
    <script src="{{ asset('assets_v4/js/chart/knob/knob-chart.js')}}"></script>
    <script src="{{ asset('assets_v4/js/prism/prism.min.js')}}"></script>
    <script src="{{ asset('assets_v4/js/clipboard/clipboard.min.js')}}"></script>
    <script src="{{ asset('assets_v4/js/custom-card/custom-card.js')}}"></script>
    <script src="{{ asset('assets_v4/js/notify/bootstrap-notify.min.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/jquery-jvectormap-2.0.2.min.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map/jquery-jvectormap-world-mill-en.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map/jquery-jvectormap-us-aea-en.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map/jquery-jvectormap-uk-mill-en.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map/jquery-jvectormap-au-mill.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map/jquery-jvectormap-chicago-mill-en.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map/jquery-jvectormap-in-mill.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map/jquery-jvectormap-asia-mill.js')}}"></script>
    <script src="{{ asset('assets_v4/js/datepicker/date-picker/datepicker.js')}}"></script>
    <script src="{{ asset('assets_v4/js/datepicker/date-picker/datepicker.en.js')}}"></script>
    <script src="{{ asset('assets_v4/js/datepicker/date-picker/datepicker.custom.js')}}"></script>
    <script src="{{ asset('assets_v4/js/typeahead/handlebars.js')}}"></script>
    <script src="{{ asset('assets_v4/js/typeahead/typeahead.bundle.js')}}"></script>
    <script src="{{ asset('assets_v4/js/typeahead/typeahead.custom.js')}}"></script>
    <script src="{{ asset('assets_v4/js/typeahead-search/handlebars.js')}}"></script>
    <script src="{{ asset('assets_v4/js/typeahead-search/typeahead-custom.js')}}"></script>
    <script src="{{ asset('assets_v4/js/vector-map/map-vector.js')}}"></script>
    <script src="{{ asset('assets_v4/js/dashboard/dashboard_2.js')}}"></script>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ asset('assets_v4/js/script.js')}}"></script>
    <script src="{{ asset('assets_v4/js/theme-customizer/customizer.js')}}"></script>
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> --}}
    <script type="text/javascript" src="https://jeremyfagis.github.io/dropify/dist/js/dropify.min.js"></script>
    <script src="{{ asset('assets/js/jquery.validate.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- login js-->
    <!-- pusher -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="//cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
      var base_url = "{{URL('/')}}";
    </script>

  </body>
</html>