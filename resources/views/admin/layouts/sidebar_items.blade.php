<?php
$controller = get_controller();
$action = get_action();

$bannerActionArr = ['index', 'create', 'edit', 'detail'];
$adActionArr = ['index', 'create', 'edit', 'detail'];

$couponOfferArr = ['index', 'create', 'edit', 'show', 'detail', 'recreate'];
$couponActionArr = ['index', 'create', 'edit', 'show', 'recreate'];
$couponHiddenActionArr = ['index_hidden', 'create_hidden', 'edit_hidden', 'show_hidden', 'recreate_hidden'];
$offerActionArr = ['index', 'create', 'edit', 'detail'];

$scratchCardActionArr = ['index', 'create', 'edit', 'show'];
$scratchCardUserActionArr = ['index_users'];
$scratchCardUserBulkUploadActionArr = ['bulk_upload_to_users'];

$brandActionArr = ['index', 'create', 'edit', 'show'];
$driverActionArr = ['index', 'create', 'edit', 'show'];
$storekeeperActionArr = ['index', 'create', 'edit', 'show','storekeepers','storekeeper_detail'];
$vehicleActionArr = ['create_vehicle', 'edit_vehicle'];
$categoryActionArr = ['index', 'create', 'edit', 'show', 'create_sub_category', 'edit_sub_category'];
$categoryClassificationArr = ['index', 'create', 'edit', 'show', 'create_sub_category', 'edit_sub_category', 'classification_detail', 'create_classification', 'edit_classification'];
$classiArr = ['classifications', 'create_classification', 'edit_classification', 'create_sub_classification', 'edit_sub_classification', 'classification_detail'];
$allPrActionArr = ['index', 'create', 'edit', 'show', 'create_formula', 'edit_formula'];
$prActionArr = ['index', 'create', 'edit', 'show'];
$appHomepageArr = ['app_homepage', 'app_homepage_create', 'app_homepage_edit', 'app_homepage_detail', 'app_homepage_add_data', 'app_homepage_edit_data'];
$priceFormulaArr = ['price_formula', 'create_formula', 'edit_formula', 'filter_price_formula'];
$allOrdersArr = ['all_orders', 'detail', 'replacement_options'];
$offerFormulaArr = ['offer_formula','filter_offer_formula'];

$userActionArr = ['index', 'show'];
$reviewFeedbackArr = ['reviews', 'feedback'];
$technicalSupportArr = ['technical_support', 'technical_support_detail'];
$customerSupportArr = ['customer_support', 'customer_support_detail'];
$newsArr = ['index', 'create', 'edit'];
$customNotificationArr = ['index', 'create', 'store', 'edit', 'show'];
$sheduledNotificationArr = ['sheduled_notifications', 'sheduled_notification_create', 'sheduled_notification_store', 'sheduled_notification_edit', 'sheduled_notification_resend', 'sheduled_notification_show'];

$recipeActionArr = ['create', 'edit'];

$fleetActionArr = ['index', 'show'];

$dailyWeeklyOfferArr = ['offers', 'offer_option', 'create_offer_option', 'edit_offer_option', 'product_offers'];
$offerOptionArr = ['offer_option', 'create_offer_option', 'edit_offer_option'];
$productOfferArr = ['product_offers'];

?>
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                <li class="menu-title">Menu</li>
                @if(in_array('store-management',$permissions))
                <li>
                    <a href="<?= url('admin/dashboard') ?>"
                        class="waves-effect <?= $controller == 'DashboardController' && ($action == 'index' || $action == 'change_password' || $action == 'recent_orders') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Dashboard</span>
                    </a>
                </li>
                @endif

                @if(in_array('fleet-store-management',$permissions))
                <li>
                    <a href="javascript:void(0);) ?>"
                        class="waves-effect <?= $controller == 'StoreController' || $controller == 'FleetController' && ($action == 'index' || $action == 'create' || $action == 'edit' || $action == 'categories' || $action == 'categories_bp') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Stores</span>
                    </a>
                    <ul class="submenu">
                        @if(in_array('store-management',$permissions))
                        <li
                            class="<?= $controller == 'StoreController' && ($action == 'stores' || $action == 'store_location') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/stores') ?>">List</a>
                        </li>
                        @endif
                        <li>
                            <a href="<?= url('admin/fleet/store-orders') ?>"
                                class="waves-effect <?= $controller == 'FleetController' && ($action == 'store_orders' || $action == 'store_order_detail') ? 'mm-active' : '' ?>">
                                <span>Orders</span>
                            </a>
                        </li>
                        <li
                            class="<?= $controller == 'FleetController' && ($action == 'stores' || $action == 'store_location') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet/stores') ?>">Locations</a>
                        </li>
                        <li
                            class="<?= $controller == 'FleetController' && $action == 'stores_catalog' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet/stores/catalog') ?>">Catalog</a>
                        </li>
                        <li
                            class="<?= $controller == 'FleetController' && $action == 'stores_sales' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet/stores/sales') ?>">Sales</a>
                        </li>
                        <li
                            class="<?= $controller == 'FleetController' && $action == 'high_sale_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet/stores/sales/high_sale_products') ?>">High sale products </a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('fleet-store-management',$permissions))
                <li>
                    <a href="<?= url('admin/companies') ?>"
                        class="waves-effect <?= $controller == 'CompanyController' && ($action == 'index' || $action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Companies</span>
                    </a>
                </li>
                @endif

                @if(in_array('marketing-management',$permissions))
                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect <?= $controller == 'BaseProductController' && in_array($action, $dailyWeeklyOfferArr) ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i><span> Offers & Pricing Formula</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'BaseProductController' && ($action == 'offers') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/offers') ?>">Daily / Weekly Offers</a>
                        </li>
                        <li
                            class="<?= $controller == 'BaseProductController' && in_array($action, $offerOptionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/offer_options') ?>">Offer Options</a>
                        </li>
                        <li
                            class="<?= $controller == 'BaseProductController' && in_array($action, $productOfferArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/product_offers') ?>">Add Product to Offers</a>
                        </li>

                        
                        <li>

                        <a href="javascript:void(0);"
                            class="waves-effect <?= $controller == 'BaseProductController' && in_array($action, $dailyWeeklyOfferArr) ? 'mm-active' : '' ?>">
                        <span> Pricing formulas</span>
                        </a>

                        <ul class="submenu">
                            <li class="<?= $controller == 'ProductController' && in_array($action, $priceFormulaArr) ? 'mm-active' : '' ?>">
                                <a href="<?= url('admin/products/price_formula') ?>">Price formula</a>
                            </li>
                            <li class="<?= $controller == 'ProductController' && in_array($action, $offerFormulaArr) ? 'mm-active' : '' ?>">
                                <a href="<?= url('admin/products/offer_formula') ?>">Offer formula</a>
                            </li>
                            <li class="<?= $controller == 'ProductController' && in_array($action, $offerFormulaArr) ? 'mm-active' : '' ?>">
                                <a href="<?= url('admin/products/apply_formula') ?>">Apply formula to all products</a>
                            </li>
                        </ul>
                        
                    </ul>
                </li>
                @endif

                @if(in_array('marketing-management',$permissions))
                <li
                    class="<?= $controller == 'AppHomeController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                    <a href="javascript:void(0);">
                        <i class="icon-accelerator"></i> <span> <span>Home Screen</span>
                    </a>
                    <ul class="submenu">
                        {{-- 
                        <li
                            class="<?= $controller == 'AppHomeController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/app_homepage/stores') ?>">Stores Home</a>
                        </li> --}}
                        <li
                            class="<?= $controller == 'AppHomeController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/app_homepage/plus') ?>">JEEB Plus / Groceries</a>
                        </li>
                        <li
                            class="<?= $controller == 'AppHomeController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/app_homepage/mall') ?>">JEEB Mall</a>
                        </li>
                        <li
                            class="<?= $controller == 'AppHomeController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/app_homepage/instant') ?>">JEEB Instant</a>
                        </li>
                        <li
                            class="<?= $controller == 'AppHomeController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/app_homepage') ?>">Other Home Statics</a>
                        </li>
                        <li
                            class="<?= $controller == 'AppHomeController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/app_homepage/old_instant') ?>">Old Instant Model</a>
                        </li>
                    </li>
                    </ul>
                </li>
                @endif

                @if(in_array('image-management',$permissions))
                <li>
                    <a href="<?= url('admin/upload_image') ?>"
                        class="waves-effect <?= $controller == 'ImageController' && ($action == 'index' || $action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Images</span>
                    </a>
                </li>
                @endif

                @if(in_array('news-ads-management',$permissions))
                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect <?= $controller == 'BannerController' && in_array($action, $bannerActionArr) ? 'mm-active' : '' ?>">
                        <i class="mdi mdi-alpha-b-circle"></i><span> News & Ads</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'AdsController' && in_array($action, $adActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/ads') ?>">Ads List</a>
                        </li>
                        <li
                            class="<?= $controller == 'NewsController' && in_array($action, $newsArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/news') ?>">News List</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('category-brand-management',$permissions))
                <li>
                    <a href="#"
                        class="waves-effect
                    <?= ($controller == 'CategoryController' || $controller == 'BrandController') && in_array($action, $categoryClassificationArr) ? 'mm-active' : '' ?>"><i
                            class="mdi mdi-alpha-c-circle"></i><span> Categories & Brands</span></a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'CategoryController' && in_array($action, $categoryActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/categories') ?>">Category List</a>
                        </li>
                        <li
                            class="<?= $controller == 'CategoryController' && in_array($action, $classiArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/classifications') ?>">Classifications</a>
                        </li>
                        <li
                            class="<?= $controller == 'BrandController' && $action == 'home_brands' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/home_brands') ?>">Home Brands</a>
                        </li>
                        <li
                            class="<?= $controller == 'BrandController' && in_array($action, $brandActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/brands') ?>">Brand List</a>
                        </li>
                        {{-- <li class="<?= $controller == 'BrandController' && $action == 'images' ? 'mm-active' : '' ?>"><a
                                href="<?= url('admin/brands/images') ?>">Upload Brand Images</a>
                        </li> --}}
                        {{-- <li
                            class="<?= $controller == 'BrandController' && $action == 'create_multiple' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/brands/create_multiple') ?>">Bulk Upload Brands</a>
                        </li> --}}
                    </ul>
                </li>
                @endif

                {{-- <li>
                    <a href="javascript:void(0);"
                        class="waves-effect
                    <?= $controller == 'ProductController' && in_array($action, $allPrActionArr) ? 'mm-active' : '' ?>"><i
                            class="mdi mdi-pentagon"></i><span> Product Mgmt</span></a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'ProductController' && in_array($action, $prActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/products') ?>">All products</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'null_itemcode_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/remaining-products') ?>">Products with no itemcode/barcode</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'new_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/new-products') ?>">New products</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'create_multiple' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/products/create_multiple') ?>">Multiple Products Upload</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'edit_multiple' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/products/edit_multiple/0') ?>">Multiple Products Edit</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'edit_multiple' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/products/update_stock_multiple/4') ?>">Products Stock Update (Vegetables And Fruits) - Regency Store 1</a>
                        </li>
                    </ul>
                </li> --}}

                @if(in_array('product-management',$permissions) || in_array('requested-product-management',$permissions) || in_array('suggested-product-management',$permissions))
                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect
                    <?= $controller == 'BaseProductController' && in_array($action, $allPrActionArr) ? 'mm-active' : '' ?>"><i
                            class="mdi mdi-pentagon"></i><span> Base Product Mgmt</span></a>
                    <ul class="submenu">
                        @if(in_array('product-management',$permissions))
                        <li
                            class="<?= $controller == 'BaseProductController' && in_array($action, $prActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products') ?>">All products</a>
                        </li>
                        {{-- <li class="<?= $controller == 'BaseProductController' && $action == '/new_products_store' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/new_products_store') ?>">New Products</a>
                        </li> --}}
                        @endif
                        @if (in_array('requested-product-management',$permissions))
                            <li class="<?= $controller == 'BaseProductController' && $action == '/requested_products_store' ? 'mm-active' : '' ?>">
                                <a href="<?= url('admin/base_products/requested_products_store') ?>">Requested Products</a>
                            </li>
                        @endif
                        @if(in_array('product-management',$permissions))
                        <li
                            class="<?= $controller == 'BaseProductController' && $action == 'create_multiple' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/create_multiple') ?>">Multiple Products Upload</a>
                        </li>
                        <li
                            class="<?= $controller == 'BaseProductController' && $action == 'edit_multiple' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/edit_multiple/0') ?>">Multiple Products Edit</a>
                        </li>
                        <li class="<?= $controller == 'BaseProductController' && $action == 'stock_update_new' ? 'mm-active' : '' ?>">
                                <a href="<?= url('admin/base_products/stock_update_stores') ?>">Products Stock Update</a>
                        </li>
                        {{-- <li class="<?= $controller == 'BaseProductController' && $action == 'products_discount' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/products_discount') ?>">Products Discount Update</a>
                        </li> --}}
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'top_selling_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/products/top-selling-products') ?>">Top selling products</a>
                        </li>
                        <li class="<?= $controller == 'TwoStepController' && $action == 'index' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/twosteptags') ?>">Two Step Tags</a>
                        </li>
                        @endif
                        @if (in_array('suggested-product-management',$permissions))
                            <li
                                class="<?= $controller == 'ProductController' && $action == 'product_suggestions' ? 'mm-active' : '' ?>">
                                <a href="<?= url('admin/products/suggestions') ?>">Suggested Products</a>
                            </li>
                        @endif
                        @if(in_array('product-management',$permissions))
                        <li
                            class="<?= $controller == 'ProductTagController' && $action == 'product_tags' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/product_tags') ?>"> Product Tags</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductTagController' && $action == 'product_tags' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/product_tags/create_multiple') ?>"> Multiple Product Tags</a>
                        </li>
                        <li
                            class="<?= $controller == 'TagBundleController' && $action == 'tag_bundles' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/tag_bundles') ?>"> Tag Bundles</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductTagController' && $action == 'search_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/base_products/search_products') ?>"> Search Products</a>
                        </li>
                        <li
                            class="<?= $controller == 'BaseProductController' && $action == 'index' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/paythem/base_products') ?>"> PayThem Products</a>
                        </li>
                        @endif
                        {{-- <li
                            class="<?= $controller == 'ProductController' && $action == 'products-price-update' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/products/products-price-update') ?>">Price formula update for vegetables and fruites category</a>
                        </li> --}}
                    </ul>
                </li>
                @endif

                @if(in_array('orders-management',$permissions))
                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect <?= $controller == 'OrderController' && in_array($action, $allOrdersArr) ? 'mm-active' : '' ?>"><i
                            class="fa fa-percent"></i><span> Order, Coupons & Offers</span></a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'OrderController' && $action == 'active_orders' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/active-orders') ?>">Active Orders</a>
                        </li>
                        <li
                            class="<?= $controller == 'OrderController' && in_array($action, $allOrdersArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/all-orders') ?>">All Orders</a>
                        </li>
                        {{-- <li
                            class="<?= $controller == 'OrderController' && $action == 'later_orders' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/later-orders') ?>">Plus Orders</a>
                        </li> --}}
                        <li
                            class="<?= $controller == 'OrderController' && $action == 'forgot_something_orders' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/forgot-something-orders') ?>">Forgot Something Orders</a>
                        </li>
                        <li
                            class="<?= ($controller == 'OrderController' && $action == 'payments') || $action == 'payment_detail' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/payments') ?>">Payment History</a>
                        </li>
                        <li
                            class="<?= $controller == 'CouponController' && in_array($action, $couponActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/coupons') ?>">Universal Coupons</a>
                        </li>
                        <li
                            class="<?= $controller == 'CouponController' && in_array($action, $couponHiddenActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/coupons_hidden') ?>">Hidden Coupons</a>
                        </li>
                        <li
                            class="<?= $controller == 'ScratchCardController' && in_array($action, $scratchCardActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/scratch_cards') ?>">Scratch Cards</a>
                        </li>
                        <li
                            class="<?= $controller == 'ScratchCardController' && in_array($action, $scratchCardUserActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/scratch_cards_users') ?>">User Specific Coupons / Onspot Rewards</a>
                        </li>
                        <li
                            class="<?= $controller == 'ScratchCardController' && in_array($action, $scratchCardUserBulkUploadActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/scratch_cards/bulk_upload_to_users') ?>">Users Scratch Card Bulk Upload</a>
                        </li>
                        {{-- <li
                            class="<?= $controller == 'OfferController' && in_array($action, $offerActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/offers') ?>">Offer List</a>
                        </li> --}}
                    </ul>
                </li>
                @endif

                @if(in_array('storekeeper-management',$permissions))
                <li>
                    <a href="javascript:void(0);" class="waves-effect"><i class="fas fa-shipping-fast"></i><span>Staff
                            Members
                            & Vehicle</span></a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'StorekeeperController' && in_array($action, $storekeeperActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/storekeepers') ?>">Storekeepers</a>
                        </li>
                        <li
                            class="<?= $controller == 'DriverController' && in_array($action, $driverActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/drivers') ?>">Drivers</a>
                        </li>
                        <li
                            class="<?= $controller == 'DriverController' && in_array($action, $vehicleActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/vehicles') ?>">Vehicles</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('customer-management',$permissions))
                <li>
                    <a href="javascript:void(0);" class="waves-effect"><i
                            class="fa fa-users"></i><span>Customers</span></a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'UserController' && in_array($action, $userActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/users') ?>">All Users</a>
                        </li>
                        <li
                            class="<?= $controller == 'UserController' && in_array($action, $userActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/users/abondoned_cart') ?>">Abondoned Cart Users</a>
                        </li>
                        <li
                            class="<?= $controller == 'FeedbackController' && in_array($action, $reviewFeedbackArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/order-reviews') ?>">Review & Feedback</a>
                        </li>
                        <li
                            class="<?= $controller == 'SupportController' && in_array($action, $technicalSupportArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/technical-support') ?>">Technical Support</a>
                        </li>
                        <li
                            class="<?= $controller == 'SupportController' && in_array($action, $customerSupportArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/customer-support') ?>">Customer Support</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('delivery-management',$permissions))
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect <?= $controller == 'CommonController' && ($action == 'delivery_area' || $action == 'delivery_slots' || $action == 'slot_settings' || $action == 'create_delivery_slots' || $action == 'edit_delivery_slots') ? 'mm-active' : '' ?>"><i
                            class="fa fa-users"></i><span> Delivery Mgmt</span></a>
                    <ul class="submenu">
                        <li class="<?= $action == 'delivery_area' ? 'mm-active' : '' ?>"><a
                                href="<?= url('admin/delivery-area') ?>">Delivery Area</a></li>
                        {{-- <li
                            class="<?= $action == 'delivery_slots' || $action == 'create_delivery_slots' ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/delivery-slots') ?>">Delivery Slots</a>
                        </li> --}}
                        {{-- <li class="<?= $action == 'slot_settings' ? 'mm-active' : '' ?>"><a
                                href="<?= url('admin/slot-settings') ?>">Slot settings</a>
                        </li> --}}
                    </ul>
                </li>
                @endif

                @if(in_array('notification-management',$permissions))
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect <?= $controller == 'CustomNotificationController' ? 'mm-active' : '' ?>"><i
                            class="fa fa-users"></i><span> Notifications</span></a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= url('admin/custom_notifications') ?>"
                                class="waves-effect <?= $controller == 'CustomNotificationController' && in_array($action, $customNotificationArr) ? 'mm-active' : '' ?>">
                                <i class="fa fa-newspaper"></i> <span>Custom Notification</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('admin/sheduled_notifications') ?>"
                                class="waves-effect <?= $controller == 'CustomNotificationController' && in_array($action, $sheduledNotificationArr) ? 'mm-active' : '' ?>">
                                <i class="fa fa-newspaper"></i> <span>Sheduled Notification</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('recipe-management',$permissions))
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect">
                        <i class="mdi mdi-alpha-b-circle"></i><span> Recipe Mgmt</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller = 'RecipeController' && ($action == 'app_homepage') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipes/home') ?>">Home</a>
                        </li>
                        <li
                            class="<?= $controller = 'RecipeTagController' && ($action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipe_tags') ?>">Tags</a>
                        </li>
                        <li
                            class="<?= $controller = 'RecipeDietController' && ($action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipe_diets') ?>">Diets</a>
                        </li>
                        <li
                            class="<?= $controller = 'RecipeCategoryController' && ($action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipe_categories') ?>">Categories</a>
                        </li>
                        <li class="<?= $controller = 'RecipeController' && ($action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipes') ?>">Recipes</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('affiliate-management',$permissions))
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect">
                        <i class="mdi mdi-alpha-b-circle"></i><span> Affiliates Mgmt</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller = 'AffiliateController' && ($action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/affiliates') ?>">Affiliates</a>
                        </li>
                    </ul>
                </li>
                @endif
                
                @if(in_array('fleet-management',$permissions))
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect">
                        <i class="mdi mdi-alpha-b-circle"></i><span> Fleet Mgmt</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller = 'FleetController' && ($action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet') ?>">Fleet Panel</a>
                        </li>
                    </ul>
                    <ul class="submenu">
                        <li
                            class="<?= $controller = 'FleetController' && ($action == 'store_groups' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet/store_groups') ?>">Store Groups</a>
                        </li>
                    </ul>
                    <ul class="submenu">
                        <li
                            class="<?= $controller = 'FleetController' && ($action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet/instant_model') ?>">Instant Model</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('report-management',$permissions))
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect">
                        <i class="mdi mdi-alpha-b-circle"></i><span> Report</span>
                    </a>
                    <ul class="submenu">
                        <li class="<?= $controller = 'ReportController' && ($action == 'index') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/report') ?>" target="_blank">Show report</a>
                        </li>
                        <li class="<?= $controller = 'ReportController' && ($action == 'stores' || $action == 'store_categories') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/report/stores') ?>" target="_blank">Stores</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(in_array('administrator-management',$permissions))
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect">
                        <i class="mdi mdi-alpha-b-circle"></i><span> Administrator</span>
                    </a>
                    <ul class="submenu">
                        <li class="">
                            <a href="<?= url('admin/administrators')?>">All Administrators</a>
                        </li>
                        <li class="#">
                            <a href="<?= url('admin/roles')?>">Roles</a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(in_array('super-admin',$permissions))
                <li>
                    <a href="{{ url('admin/whatsapp') }}" class="waves-effect">
                        <i class="mdi mdi-whatsapp"></i><span>WhatsApp API</span>
                    </a>
                </li>
                @endif
                @if(in_array('bot-management',$permissions) && env('BOT_ROTATOR') == true)
                <li>
                    <a href="{{ url('admin/bot_rotator') }}" class="waves-effect">
                        <i class="fa fa-robot"></i></i><span>Bot Rotator</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
