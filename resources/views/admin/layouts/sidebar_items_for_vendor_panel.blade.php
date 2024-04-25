@if(get_action() == 'store_instant_active_orders' || get_action() == 'store_instant_orders' || get_action() == 'store_instant_order_detail')
  <style>
  .order-history table thead tr th{
      background-color: rgb(206 62 62 / 5%);
      color:#c10707
  }

  * a{
    color:#c10707
  }
  
  .btn-primary {
      background-color: #c10707 !important;
      border-color: #c10707 !important;
  }
  
  .pagination-primary .page-item.active .page-link{
    color: #fff !important;
    background-color: #c10707 !important;
    border-color: #c10707;

  }
  </style>
@endif

  <body>
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
      
      <!-- Page Header Ends                              -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <div class="sidebar-wrapper">
          <div>
            <div class="logo-wrapper"><a href="{{ route('vendor-dashboard',['id' => base64url_encode($store->id)]) }}"><img class="img-fluid for-light" src="{{ asset('assets_v4/images/logo/logo-white.webp')}}" alt="" width="70%"></a>
              {{-- <div class="back-btn"><i class="fa fa-angle-left"></i></div>
              <div class="toggle-sidebar"><i class="fa fa-cog status_toggle middle sidebar-toggle"> </i></div> --}}
            </div>
            <div class="logo-icon-wrapper"><a href="{{ route('vendor-dashboard',['id' => base64url_encode($store->id)]) }}"><img class="img-fluid" src="#" alt=""></a></div>
            <nav class="sidebar-main">
              <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
              <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                  <li class="back-btn"><a href="{{ route('vendor-dashboard',['id' => base64url_encode($store->id)]) }}"><img class="img-fluid" src="{{ asset('assets_v4/images/logo/logo-icon.png')}}" alt=""></a>
                    <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                  </li>
                  <li class="menu-box"> 
                    <ul>             
                      <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="{{ route('vendor-dashboard',['id' => base64url_encode($store->id)]) }}"><i data-feather="home"></i>
                            <span class="lan-3">Dashboard</span></a>
                      </li>
                      <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)"><i data-feather="airplay"></i><span class="lan-6">Products</span>
                        </a>
                        <ul class="sidebar-submenu">
                          <li><a href="{{ route('fleet-stock-update-base-products-panel',['id' => base64url_encode($store->id), 'category' => 0]); }}">All Products</a></li>
                          <li><a href="{{ route('vendor_instock_product',['id' => base64url_encode($store->id), 'category' => 0]) }}">In Stock Products</a></li>
                          <li><a href="{{ route('vendor_out_of_stock_product',['id' => base64url_encode($store->id), 'category' => 0]) }}">Out Of Stock Products</a></li>
                        </ul>
                      </li>
                      {{-- <li class="sidebar-list"><a class="sidebar-link sidebar-title" href="{{ route('vendor-orders',['id' => base64url_encode($store->id)]) }}"><i data-feather="layout"></i><span class="lan-7">Orders</span></a></li> --}}
                      <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)"><i data-feather="layout"></i><span>Orders</span></a>
                        <ul class="sidebar-submenu">
                          <li><a href="{{ route('vendor-active-orders',['id' => base64url_encode($store->id)]) }}">Active Orders</a></li>
                          <li><a href="{{ route('vendor-orders',['id' => base64url_encode($store->id)]) }}">All Orders</a></li>
                        </ul>
                      </li>
                      <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)"><i data-feather="layout"></i><span>Instant Orders</span></a>
                        <ul class="sidebar-submenu">
                          <li><a href="{{ route('vendor-instant-active-orders',['id' => base64url_encode($store->id)]) }}">Active Instant Orders</a></li>
                          <li><a href="{{ route('vendor-instant-orders',['id' => base64url_encode($store->id)]) }}">All Instant Orders</a></li>
                        </ul>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div>
              <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
            </nav>
          </div>
        </div>
      