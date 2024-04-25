<?php
$controller = get_controller();
$action = get_action();
?>
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                <li class="menu-title">Menu</li> 
                <li>
                    <a href="<?= url('vendor/dashboard'); ?>" class="waves-effect <?=
                    $controller == 'DashboardController' && ($action == 'index' || $action == 'change_password') ? 'mm-active' : ''
                    ?>">
                        <i class="icon-accelerator"></i> <span>Dashboard</span>
                    </a>
                </li>
              
                <li>
                    
                    <a class="sidebar-link sidebar-title" href="javascript:void(0)"><i class="fa fa-shopping-cart"></i></i><span class="lan-6">Products</span>
                    </a>
                    <ul class="submenu">
                      <li><a href="{{ route('vendor_all_products') }}">Products List</a></li>
                      <li><a href="{{ route('vendor_products_brands') }}">Brands</a></li>
                      <li><a href="{{ route('vendor_product_stock_update') }}">Stock Update</a></li>
                    
                    </ul>
                </li>                
                <li>
                    <a  class="waves-effect" href="javascript:void(0)"><i class="fa fa-shopping-cart"></i></i><span class="lan-6">Orders</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('vendor_active_orders') }}">Active Orders</a></li>
                        <li><a href="{{ route('vendor-completed-orders') }}">Completed Orders</a></li>
                        <li><a href="{{ route('vendor-cancelled-orders') }}">Cancelled Orders</a></li>
                    </ul>
                </li>                
                <li>
                    <a href="{{ route('fleet_data') }}" class="waves-effect">
                        <i class="fa fa-shopping-cart"></i> <span>Fleet</span>
                    </a>

                </li>                
                <li>
                    <a href="javascript:void(0)" class="waves-effect">
                        <i class="fa fa-shopping-cart"></i> <span>Coupons</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('vendor-coupons-list') }}">Coupons List</a></li>
                       
                    </ul>
                </li>                
                <li>
                    <a href="{{ route('vendor-analytics') }}" class="waves-effect">
                        <i class="fa fa-shopping-cart"></i> <span>Analytics</span>
                    </a>
                </li>                
                <li>
                    <a href="javascript:void(0)" class="waves-effect">
                        <i class="fa fa-shopping-cart"></i> <span>Accounts</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('vendor-subscription') }}">Subscription</a></li>
                        <li><a  href="javascript:void(0)" >Billing</a>
                            <ul class="submenu">
                                <li><a href="{{ route('vendor_profile') }}">Overview</a></li>
                                <li><a href="{{ route('vendor-stores') }}">Usage Details</a></li>
                                <li><a href="{{ route('vendor-payment-card') }}">Payment Details</a></li>
                                <li><a href="{{ route('pass_token') }}">Invoices</a></li>
                               
                            </ul>
                        </li>
                    </ul>
                </li>                
                <li>
                    <a href="javascript:void(0)" class="waves-effect">
                        <i class="fa fa-shopping-cart"></i> <span>Settings</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('vendor_profile') }}">Profile</a></li>
                        <li><a href="{{ route('vendor-stores') }}">Store</a></li>
                        <li><a href="{{ route('vendor-payment-card') }}">Payment Card</a></li>
                        <li><a href="{{ route('pass_token') }}">Pass Key</a></li>
                       
                    </ul>
                </li>                
<!--                <li class="<?//=
                $controller == 'ProductController' && ($action == 'index' || $action == 'create_multiple') ? 'mm-active' : ''
                ?>">
                    <a href="javascript:void(0);" class="waves-effect <?//=
                    $controller == 'ProductController' && ($action == 'index' || $action == 'create_multiple') ? 'mm-active' : ''
                    ?>"><i class="mdi mdi-pentagon"></i><span> Products</span></a>
                    <ul class="submenu">
                        <li><a href="<?//= url('vendor/products'); ?>" class="<?//= $action == 'index' ? 'mm-active' : '' ?>">All products</a></li>
                        <li><a href="<?//= url('vendor/products/create_multiple'); ?>" class="<?//= $action == 'create_multiple' ? 'mm-active' : '' ?>">Bulk update</a></li>                      
                    </ul>
                </li>-->
                <!--                <li>
                                    <a href="<?//= url('vendor/reports'); ?>" class="waves-effect <?//=
                                    $controller == 'OrderController' && ($action == 'report') ? 'mm-active' : ''
                                    ?>">
                                        <i class="mdi mdi-note"></i> <span>Reports</span>
                                    </a>
                                </li>-->
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>