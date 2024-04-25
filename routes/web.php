<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Auth::routes();

Route::get('/', 'HomeController@home')->name('home');
Route::get('/media', 'HomeController@media')->name('media');
Route::get('/blog/{id}', 'HomeController@media_single')->name('media-single');
Route::redirect('/media/{id}', '/blog/{id}', 301);
Route::get('/contact-us', 'HomeController@contact')->name('contact');
Route::get('/about-us', 'HomeController@about')->name('about');
Route::post('/contact-form', 'HomeController@contact_form')->name('contact-form');
Route::get('/privacy-policy', 'HomeController@privacy_policy')->name('privacy-policy');
Route::get('/terms-and-conditions', 'HomeController@terms')->name('terms');
Route::get('/faq', 'HomeController@faq')->name('faq');

Route::get('/ai_service', 'HomeController@ai_service')->name('ai-service');

Route::get('/orders/{id}', 'HomeController@shared_link')->name('storekeeper-sharing');
Route::get('/customer-invoice/{id}', 'HomeController@customer_invoice')->name('customer-invoice');
Route::get('/customer-invoice-pdf/{id}', 'HomeController@customer_invoice_pdf')->name('customer-invoice-pdf');
Route::get('/order_tracker/{id}', 'HomeController@order_tracker');

Route::get('/order_payment', 'HomeController@order_payment');
Route::get('/forgot_something_order_payment', 'HomeController@forgot_something_order_payment');

Route::get('/bifm_order/{bifm_id}', 'HomeController@bifm_order');
Route::get('/bifm_order/payment/{order_id}', 'HomeController@bifm_order_payment');
Route::post('/bifm_order/payment/', 'HomeController@bifm_order_payment_process');

// Route::get('/', 'HomeController@index');
Route::get('/news', 'HomeController@news');
Route::get('/app', 'HomeController@app');
Route::get('/privacy_policy', 'HomeController@privacy_policy_for_app');
Route::get('/terms', 'HomeController@terms_for_app');
Route::get('/download-app', 'HomeController@download_app');
Route::post('/download-app-submit', 'HomeController@download_app_submit')->name('download-app-submit');

Route::get('/user_otps', 'Admin\DashboardController@otps');

Route::get('/all-products', 'Admin\ProductController@all_products');
Route::get('/my-all-products', 'Admin\ProductController@my_all_products');

Route::group(['prefix' => 'admin', 'middleware' => ['admin', 'admin_login_access']], function () {
    Route::get('/', 'Admin\AuthController@login_get');
    Route::get('login', 'Admin\AuthController@login_get');

    Route::get('forgot_password', 'Admin\AuthController@forgot_password');
    Route::post('send_password_reset_link', 'Admin\AuthController@send_password_reset_link');
});

Route::get('admin/reset_password/{token}', 'Admin\AuthController@reset_password');
Route::post('admin/set_new_password', 'Admin\AuthController@set_new_password');

Route::group(['prefix' => 'admin', 'middleware' => ['admin_login_access']], function () {
    Route::post('login_post', 'Admin\AuthController@login_post');
    Route::get('logout', 'Admin\AuthController@logout');
});

// Route::post('vendor/login_post', 'Vendor\AuthController@login_post');

Route::get('admin/forbidden', 'Admin\AdminController@forbidden')->name('forbidden');

Route::group(['prefix' => 'admin', 'middleware' => ['admin_auth', 'admin_login_access']], function () {

    Route::get('notify', 'Admin\TestController@notify');
    // Route::get('dashboard', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\DashboardController@index']);
    
    Route::get('dashboard', 'Admin\DashboardController@index');
    
    // Report 
    Route::get('report', ['middleware' => 'permission:report-management', 'uses' => 'Admin\ReportController@index']);
    Route::get('report/stores', ['middleware' => 'permission:report-management', 'uses' => 'Admin\ReportController@stores']);
    Route::get('report/stores/{id}/categories', ['middleware' => 'permission:report-management', 'uses' => 'Admin\ReportController@store_categories']);

    // Fleet management
    Route::get('fleet', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@index'])->name('fleet');
    Route::get('fleet/store/{id}', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@store'])->name('fleet-store');
    Route::get('fleet/store/{id}/orders', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@orders'])->name('fleet-orders');
    Route::get('fleet/store/{id}/active-orders', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@active_orders'])->name('fleet-active-orders');
    Route::get('fleet/store/orders/{order_id}', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@order_detail'])->name('fleet-order-detail');
    Route::get('fleet/store/{id}/drivers', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@drivers'])->name('fleet-drivers');
    Route::get('fleet/store/drivers/{driver_id}', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@driver_detail'])->name('fleet-driver-detail');
    Route::get('fleet/store/{id}/storekeepers', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@storekeepers'])->name('fleet-storekeepers');
    Route::get('fleet/store/{id}/storekeeper-detail', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@storekeeper_detail'])->name('fleet-storekeeper-detail');
    Route::post('fleet/assign_storekeeper', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@assign_storekeeper']);
    Route::post('fleet/mark_out_of_stock', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@mark_out_of_stock']);
    Route::post('fleet/revert_marked_out_of_stock', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@revert_marked_out_of_stock']);
    Route::post('fleet/mark_collected', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@mark_collected']);
    Route::post('fleet/revert_mark_collected', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@revert_mark_collected']);
    Route::post('fleet/invoice_order', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@invoice_order']);
    Route::post('fleet/assign_driver', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@assign_driver']);
    Route::get('fleet/store/{id}/products/update_stock_multiple', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@update_stock_multiple'])->name('fleet-products-panel');
    Route::post('fleet/store/{id}/products/update_stock_multiple_save', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@update_stock_multiple_save']);
    Route::get('fleet/store/{id}/orders/map', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@store_orders_map'])->name('fleet-store-order-map');
    Route::get('fleet/store/list/{id}', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@store_list'])->name('fleet-store-list');
    Route::post('fleet/store/assign_driver', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@assign_store_driver'])->name('fleet-assign-store-driver');
    Route::get('fleet/driver/list', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@driver_list'])->name('fleet-driver-list');
    Route::get('fleet/driver/get_group_stores', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@get_group_stores'])->name('fleet-driver-get-group-stores');
    Route::post('fleet/driver/update_driver_store', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@update_driver_store'])->name('fleet-update-driver-store');
    Route::get('fleet/driver/delete_driver_store/{id}', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@delete_driver_store'])->name('fleet-delete-driver-store');

    Route::get('fleet/store/{id}/base_products/update_stock_multiple/{category}', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@update_stock_multiple'])->name('fleet-base-products-panel');
    Route::post('fleet/store/{id}/base_products/update_stock_multiple_save', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@update_stock_multiple_save']);
    
    Route::get('fleet/store-orders', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@store_orders'])->name('fleet-store-orders');
    Route::get('fleet/store-order-detail/{id}/{store}', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@store_order_detail'])->name('fleet-store-order-detail');
    Route::get('fleet/stores', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@stores'])->name('fleet-stores');
    Route::get('fleet/stores/location/{id}', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@store_location'])->name('fleet-store-location');
    Route::get('fleet/stores/catalog', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@stores_catalog'])->name('fleet-stores-catalog');
    Route::get('fleet/stores/sales', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@stores_sales'])->name('fleet-stores-sales');
    Route::get('fleet/stores/sales/high_sale_products', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@stores_high_sale_products'])->name('fleet-stores-high-sale-product');
    Route::get('fleet/stores/edit/{id}', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@store_edit']); // Not using now
    Route::post('fleet/stores/update/{id}', [
        'middleware' => 'permission:fleet-store-management',
        'uses' => 'Admin\FleetController@store_update',
        'as' => 'admin.fleet.stores.update'
    ]);
    Route::get('fleet/stores/catalog-with-filter', ['middleware' => 'permission:fleet-store-management-catalog', 'uses' => 'Admin\FleetController@stores_catalog_with_filter'])->name('fleet-stores-catalog-with-filter');
    
    // Store schedules
    Route::get('fleet/stores/schedules/{id}', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@store_schedules'])->name('fleet-stores-schedules');
    Route::post('fleet/stores/schedules/{id}/update', [
        'middleware' => 'permission:fleet-store-management',
        'uses' => 'Admin\FleetController@store_schedules_update',
        'as' => 'admin.fleet.store_schedules.update'
    ]);
    Route::get('fleet/stores/special-days-schedules/{id}', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@store_special_schedules'])->name('fleet-stores-special-days-schedules');
    Route::post('fleet/stores/special-days-schedules/{id}/update', [
        'middleware' => 'permission:fleet-store-management',
        'uses' => 'Admin\FleetController@store_special_schedules_update',
        'as' => 'admin.fleet.store_special_schedules.update'
    ]);

    //Instant model
    Route::get('fleet/instant_model', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@instant_model'])->name('fleet-instant-model');
    Route::get('fleet/instant_model/store_group/{id}/active-orders', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@instant_model_active_orders'])->name('fleet-instant-model-active-orders');
    Route::get('fleet/instant_model/store_groups', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@instant_model_store_groups'])->name('fleet-instant-model-store-groups');
    Route::post('fleet/instant_model/store_group/create', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@create_instant_model_store_group'])->name('fleet-create-instant-model-store-group');
    Route::get('fleet/instant_model/store_group/delete/{id}', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@delete_instant_model_store_group'])->name('fleet-delete-instant-model-store-group');
    Route::get('fleet/instant_model/{id}/orders', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@instant_model_orders'])->name('fleet-instant-model-orders');
    Route::get('fleet/instant_model/orders/{order_id}', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@instant_model_order_detail'])->name('fleet-instant-model-order-detail');

    //storekeeper fleet panel
    Route::get('fleet/storekeeper/orders', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@storekeeper_orders'])->name('fleet-storekeeper-orders');
    Route::get('fleet/storekeeper/orders/{order_id}', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@storekeeper_order_detail'])->name('fleet-storekeeper-order-detail');
    Route::post('fleet/storekeeper/assign_driver', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@assign_driver'])->name('fleet-storekeeper-order-driver-assign');

    Route::get('fleet/search_new_product', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@search_new_product']);
    Route::post('fleet/add_order_product', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\FleetController@add_order_product']);

    Route::get('fleet/storekeeper/store/list/{id}', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@storekeeper_store_list'])->name('storekeeper-store-list');
    Route::post('fleet/storekeeper/store/assign_driver', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@storekeeper_assign_store_driver'])->name('storekeeper-assign-store-driver');
    Route::get('fleet/storekeeper/driver/list', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@storekeeper_driver_list'])->name('storekeeper-driver-list');
    Route::post('fleet/storekeeper/driver/update_driver_store', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@storekeeper_update_driver_store'])->name('storekeeper-update-driver-store');
    Route::get('fleet/storekeeper/driver/delete_driver_store/{id}', ['middleware' => 'permission:storekeeper-fleet-management', 'uses' => 'Admin\FleetController@storekeeper_delete_driver_store'])->name('storekeeper-delete-driver-store');

    Route::get('/stock_update/store/{id}/base_products/update_stock_multiple/{category}', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@store_update_stock_multiple'])->name('fleet-stock-update-base-products-panel');
    Route::post('stock_update/store/{id}/base_products/update_stock_multiple_save', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@update_stock_multiple_save']);
    Route::post('/stock_update/store/{id}/base_products/get_product_from_barcode', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@update_stock_multiple_save']);
    Route::get('/stock_update/store/{id}/base_products/update_stock/', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@store_update'])->name('fleet-stock-update-store-update');
    Route::post('/stock_update/store/{id}/base_products/update_stock_save', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@update_stock_save']);
    Route::get('/stock_update/store/{id}/base_products/update_sale/', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@sale_update'])->name('fleet-stock-update-sale-update');
    Route::post('/stock_update/store/{id}/base_products/update_sale_save', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@update_sale_save']);

    //Stores grouping
    Route::get('fleet/store_groups', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@store_groups']);
    Route::post('fleet/store_groups/create', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@create_store_groups']);
    Route::get('fleet/store_groups/edit', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@edit_store_groups']);
    Route::post('fleet/store_groups/update', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@update_store_groups']);
    Route::get('fleet/store_groups/delete_store/{id}', ['middleware' => 'permission:fleet-management', 'uses' => 'Admin\FleetController@delete_group_store'])->name('delete_store_group');

    //vendor Panel 
    Route::get('/store/{id}', 'Admin\FleetBpController@dashboard')->name('vendor-dashboard');
    Route::get('/store/orders/{id}', 'Admin\FleetBpController@store_orders')->name('vendor-orders');
    Route::get('/store/orders/active/{id}', 'Admin\FleetBpController@store_active_orders')->name('vendor-active-orders');
    Route::get('/store/orders/{store}/detail/{id}', 'Admin\FleetBpController@store_order_detail')->name('vendor-order-detail');
    Route::get('/store/product/add/{id}', 'Admin\FleetBpController@store_product_add')->name('vendor-product-add');
    Route::post('/store/product/store/', 'Admin\FleetBpController@vendor_product_store')->name('vendor-product-store');
    Route::get('/store/{id}/product/instock/{category}', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@store_instock_products'])->name('vendor_instock_product');
    Route::get('/store/{id}/product/out_of_stock/{category}', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@store_out_of_stock_products'])->name('vendor_out_of_stock_product');
    Route::post('/store/order/product/collect', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@mark_order_product_collected'])->name('vendor_mark_order_product_collected');
    Route::post('/store/order/product/out_of_stock', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@mark_order_product_out_of_stock'])->name('vendor_mark_order_product_out_of_stock');
    Route::post('/store/order/product/update_products_status', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@update_products_status'])->name('update_products_status');

    Route::get('/store/instant/orders/active/{id}', 'Admin\FleetBpController@store_instant_active_orders')->name('vendor-instant-active-orders');
    Route::get('/store/instant/orders/{id}', 'Admin\FleetBpController@store_instant_orders')->name('vendor-instant-orders');
    Route::get('/store/instant/orders/{store}/detail/{id}', 'Admin\FleetBpController@store_instant_order_detail')->name('vendor-instant-order-detail');
    Route::get('/store/orders/{store}/vendor-invoice-pdf/{id}', 'Admin\FleetBpController@vendor_invoice_pdf')->name('vendor-invoice-pdf');
    Route::get('/store/orders/{store}/vendor-invoice-print/{id}', 'Admin\FleetBpController@vendor_invoice_print')->name('vendor-invoice-print');

    Route::get('/store/order/preparing_orders', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@preparing_orders'])->name('preparing_orders');
    Route::get('/store/order/ready_for_pickup_orders', ['middleware' => 'permission:store-product-stock-management', 'uses' =>'Admin\FleetBpController@ready_for_pickup_orders'])->name('ready_for_pickup_orders');

    // Stores panel
    Route::get('stores', ['middleware' => 'permission:store-management', 'uses' => 'Admin\StoreController@index']);
    Route::get('stores/{id}/categories', ['middleware' => 'permission:store-management', 'uses' => 'Admin\StoreController@categories']);
    Route::get('stores/{id}/categories_bp', ['middleware' => 'permission:store-management', 'uses' => 'Admin\StoreController@categories_bp']);
    Route::post('stores/activate_all_store_products', [ // Activate deactivate products
        'middleware' => 'permission:product-management', 
        'uses' => 'Admin\BaseProductController@activate_all_store_products',
        'as' => 'admin.stores.activate_all_store_products'
    ]);
    Route::get('stores/get_base_products_store_status', ['middleware' => 'permission:store-management', 'uses' => 'Admin\BaseProductController@get_base_products_store_status']);
    
    Route::get('stores/show/{id}', ['middleware' => 'permission:store-management', 'uses' => 'Admin\StoreController@show']); // Not using now
    Route::get('stores/create', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\StoreController@create']); // Not using now
    Route::get('stores/destroy/{id}', ['middleware' => 'permission:store-management', 'uses' => 'Admin\StoreController@destroy']); // Not using now
    Route::post('stores/store', [
        'middleware' => 'permission:fleet-store-management',
        'uses' => 'Admin\StoreController@store',
        'as' => 'admin.stores.store'
    ]);
    Route::get('stores/edit/{id}', ['middleware' => 'permission:store-management', 'uses' => 'Admin\StoreController@edit']); // Not using now
    Route::post('stores/update/{id}', [
        'middleware' => 'permission:store-management',
        'uses' => 'Admin\StoreController@update',
        'as' => 'admin.stores.update'
    ]);
    Route::post('stores/create_company_store_name', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\StoreController@create_company_store_name']);

    //Store master panel
    Route::get('/store/master/{id}', ['middleware' => 'permission:stores-master-panel-management', 'uses' => 'Admin\FleetBpController@master_dashboard'])->name('vendor-master-dashboard');

    // Companies panel
    Route::get('companies', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\CompanyController@index']);
    Route::get('companies/create', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\CompanyController@create']); // Not using now
    Route::get('companies/destroy/{id}', ['middleware' => 'permission:store-management', 'uses' => 'Admin\CompanyController@destroy']); // Not using now
    Route::post('companies/store', [
        'middleware' => 'permission:fleet-store-management',
        'uses' => 'Admin\CompanyController@store',
        'as' => 'admin.companies.store'
    ]);
    Route::get('companies/edit/{id}', ['middleware' => 'permission:fleet-store-management', 'uses' => 'Admin\CompanyController@edit']); // Not using now
    Route::post('companies/update/{id}', [
        'middleware' => 'permission:fleet-store-management',
        'uses' => 'Admin\CompanyController@update',
        'as' => 'admin.companies.update'
    ]);

    // Products management
    Route::get('base_products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@index']);
    Route::get('base_products/show/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@show']);
    Route::get('base_products/create/', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@create']);
    Route::post('base_products/store/', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@store',
        'as' => 'admin.base_products.store'
    ]);
    Route::get('base_products/edit/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@edit']);
    Route::post('base_products/update/{id}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@update',
        'as' => 'admin.base_products.update'
    ]);
    Route::post('base_products/delete_base_product', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@delete_base_product']);

    Route::post('base_products_store/create/', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@create_base_product_ajax',
        'as' => 'admin.base_products_store.create'
    ]);
    
    Route::post('base_products_update/update/', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@update_base_product_ajax',
        'as' => 'admin.base_products_store.update'
    ]);

    Route::post('base_products_store/delete_base_product_store', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@delete_base_product_store']);

    Route::get('base_products/create_multiple', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@create_multiple']);
    Route::post('base_products/bulk_upload_product', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@bulk_upload_product',
        'as' => 'admin.base_products.bulk_upload_products'
    ]);

    Route::post('base_products/bulk_upload_product_store', [
        'uses' => 'Admin\BaseProductController@bulk_upload_product_store',
        'as' => 'admin.base_products.bulk_upload_product_store'
    ]);

    Route::get('base_products/stock-update/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@stock_update_new']);
    Route::get('base_products/stock_update_stores', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@stock_update_stores']);
    Route::get('base_products/stock_update/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@stock_update_new']);
    Route::get('base_products/stock_update/{id}/{batch_id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@stock_update_new']);
    Route::post('base_products/bulk_stock_update', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@bulk_stock_update',
        'as' => 'admin.base_products.bulk_stock_update'
    ]);
    Route::get('base_products/stock_update/{id}/{batch_id}/update', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@stock_update_one_by_one'])->name('base_product_stock_update_one_by_one');
    Route::post('base_products/post_stock_update_one_by_one', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@post_stock_update_one_by_one',
        'as' => 'admin.base_products.post_stock_update_one_by_one'
    ]);
    Route::post('base_products/post_stock_update_one_by_one_bulk_v2', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@post_stock_update_one_by_one_bulk_v2',
        'as' => 'admin.base_products.post_stock_update_one_by_one_bulk_v2'
    ]);
    Route::post('base_products/post_stock_update_one_by_one_bulk_v3', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@post_stock_update_one_by_one_bulk_v3',
        'as' => 'admin.base_products.post_stock_update_one_by_one_bulk_v3'
    ]);

    Route::get('base_products/stock_update_stores/new_products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@stock_update_stores_new_products']);
    Route::post('base_products/stock_update_stores/add_new_products_store', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@add_new_products_store']);
    
    Route::get('base_products/requested_products_store', ['middleware' => 'permission:requested-product-management', 'uses' => 'Admin\BaseProductController@requested_products_store']);
    Route::post('base_products/add_requested_products_store', ['middleware' => 'permission:requested-product-management', 'uses' => 'Admin\BaseProductController@add_requested_products_store']);
    Route::post('base_products/remove_store_requested_product', ['middleware' => 'permission:requested-product-management', 'uses' => 'Admin\BaseProductController@remove_store_requested_product']);
    Route::get('base_products/get_base_products_ajax', ['middleware' => 'permission:requested-product-management', 'uses' => 'Admin\BaseProductController@get_base_products_ajax']);
    Route::post('base_products/create_requested_base_product/', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@create_requested_base_product',
        'as' => 'admin.base_products.create_requested_base_product'
    ]);

    Route::get('base_products/edit_multiple/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@edit_multiple']);
    Route::post('base_products/product_edit_multiple_save', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@product_edit_multiple_save']);

    Route::get('base_products/offers', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@offers']);
    Route::post('base_products/product_edit_offer_save', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@product_edit_offer_save']);
    Route::post('base_products/product_edit_offer_remove', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@product_edit_offer_remove']);
    
    Route::get('base_products/products_discount', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@products_discount']);
    Route::post('base_products/update_products_discount', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@update_products_discount',
        'as' => 'admin.base_products.update_products_discount'
    ]);

    Route::get('products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@index']);
    Route::get('products/show/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@show']);
    Route::get('products/create', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@create']);
    Route::get('products/create_multiple', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@create_multiple']);
    Route::get('products/edit_multiple/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@edit_multiple']);
    Route::post('products/product_edit_multiple_save', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@product_edit_multiple_save']);
    Route::get('products/update_stock_multiple/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@update_stock_multiple']);
    Route::post('products/update_stock_multiple_save', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@update_stock_multiple_save']);
    // Route::get('stock-update', 'Admin\ProductController@stock_update');

    Route::post('products/change_product_status', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@change_product_status']);
    Route::post('products/get_category_brand', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@get_category_brand']);
    Route::post('products/get_sub_category', 'Admin\ProductController@get_sub_category');
    Route::get('products/create_subproduct/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@create_subproduct']);
    Route::post('products/store', [
        'uses' => 'Admin\ProductController@store',
        'as' => 'admin.products.store'
    ]);
    Route::post('products/bulk_upload', [
        'uses' => 'Admin\ProductController@bulk_upload',
        'as' => 'admin.products.bulk_upload'
    ]);
    Route::get('products/stock-update/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@stock_update_new'])->name('stock_update_new');
    Route::get('products/stock-update/{id}/{batch_id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@stock_update_new'])->name('stock_update_new');
    Route::post('products/bulk_stock_update', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@bulk_stock_update',
        'as' => 'admin.products.bulk_stock_update'
    ]);
    Route::get('products/stock-update/{id}/{batch_id}/update', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@stock_update_one_by_one'])->name('stock_update_one_by_one');
    Route::post('products/post_stock_update_one_by_one', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@post_stock_update_one_by_one',
        'as' => 'admin.products.post_stock_update_one_by_one'
    ]);
    Route::post('products/post_stock_update_one_by_one_bulk', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@post_stock_update_one_by_one_bulk',
        'as' => 'admin.products.post_stock_update_one_by_one_bulk'
    ]);

    // Route::post('products/bulk_stock_update_step1', [
    //     'uses' => 'Admin\ProductController@bulk_stock_update_step1',
    //     'as' => 'admin.products.bulk_stock_update_step1'
    // ]);
    // Route::post('products/bulk_stock_update_step2', [
    //     'uses' => 'Admin\ProductController@bulk_stock_update_step2',
    //     'as' => 'admin.products.bulk_stock_update_step2'
    // ]);

    Route::post('products/store_subproduct', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@store_subproduct',
        'as' => 'admin.products.store_subproduct'
    ]);
    Route::get('products/edit/{id}',['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@edit']);
    Route::post('products/update/{id}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@update',
        'as' => 'admin.products.update'
    ]);
    Route::get('base_products/bulk_upload_single_column', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@bulk_upload_single_column']);
    Route::post('base_products/bulk_upload_single_column_post', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@bulk_upload_single_column_post',
        'as' => 'admin.base_products.bulk_upload_single_column_post'
    ]);
    Route::post('base_products/bulk_upload_single_column_store_post', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@bulk_upload_single_column_store_post',
        'as' => 'admin.base_products.bulk_upload_single_column_store_post'
    ]);
    Route::post('recipes/bulk_upload_single_column_post', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\RecipeController@bulk_upload_single_column_post',
        'as' => 'admin.recipes.bulk_upload_single_column_post'
    ]);
    Route::get('base_products/export_base_products', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@export_base_products',
        'as' => 'admin.base_products.export_base_products'
    ]);
    Route::get('base_products/export_base_products_store', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@export_base_products_store',
        'as' => 'admin.base_products.export_base_products_store'
    ]);
    Route::get('base_products/export_base_products_heading', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@export_base_products_heading',
        'as' => 'admin.base_products.export_base_products_heading'
    ]);
    Route::get('base_products/export_base_products_store_heading', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@export_base_products_store_heading',
        'as' => 'admin.base_products.export_base_products_store_heading'
    ]);

    // Product Tags
    Route::get('product_tags', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@index']);
    Route::get('product_tags/create', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@create']);
    Route::post('product_tags/store', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductTagController@store',
        'as' => 'admin.product_tags.store'
    ]);
    Route::get('product_tags/edit/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@edit']);
    Route::post('product_tags/update/{id}', [
        'uses' => 'Admin\ProductTagController@update',
        'as' => 'admin.product_tags.update'
    ]);
    Route::get('product_tags/destroy/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@destroy']);

    //multiple product tags upload
    Route::get('base_products/product_tags/create_multiple', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@create_multiple']);
    Route::post('base_products/product_tags/bulk_upload', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductTagController@bulk_upload',
        'as' => 'admin.product_tags.bulk_upload'
    ]);

    Route::post('base_products/bulk_upload/product_tag_ids', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductTagController@bulk_upload_product_tag_ids',
        'as' => 'admin.base_products.bulk_upload_product_tag_ids'
    ]);

    // Tags search query fixes for Algolia 
    Route::get('base_products/search_products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@search_products']);
    Route::post('base_products/search_products_update', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@search_products_update']);
    Route::post('base_products/search_tags_load_more', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@search_tags_load_more']);
    Route::post('base_products/tags_arrange_save', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@tags_arrange_save']);
    Route::get('base_products/get_tags', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductTagController@get_tags']);
    
    // Tag Bundles
    Route::get('tag_bundles', ['middleware' => 'permission:product-management', 'uses' => 'Admin\TagBundleController@index']);
    Route::get('tag_bundles/create', ['middleware' => 'permission:product-management', 'uses' => 'Admin\TagBundleController@create']);
    Route::post('tag_bundles/store', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\TagBundleController@store',
        'as' => 'admin.tag_bundles.store'
    ]);
    Route::get('tag_bundles/edit/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\TagBundleController@edit']);
    Route::post('tag_bundles/update/{id}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\TagBundleController@update',
        'as' => 'admin.tag_bundles.update'
    ]);
    Route::get('tag_bundles/destroy/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\TagBundleController@destroy']);

    Route::get('recent-orders/{slot}', 'Admin\DashboardController@recent_orders');
    Route::post('dashboard/update_assigned_driver', [
        'uses' => 'Admin\DashboardController@update_assigned_driver',
        'as' => 'admin.dashboard.update_assigned_driver'
    ]);
    Route::get('changepassword', 'Admin\DashboardController@change_password');
    Route::post('update', [
        'uses' => 'Admin\DashboardController@update',
        'as' => 'admin.dashboard.update'
    ]);
    Route::get('settings', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\SettingController@index']);
    Route::post('/update_setting', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\SettingController@update_setting',
        'as' => 'admin.update_setting'
    ]);
    Route::post('/update_maintenance_setting', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\SettingController@update_maintenance_setting',
        'as' => 'admin.update_maintenance_setting'
    ]);

    Route::get('categories', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@index']);
    Route::get('categories/create', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@create']);
    Route::post('categories/store', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\CategoryController@store',
        'as' => 'admin.categories.store'
    ]);
    Route::get('categories/edit/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@edit']);
    Route::post('categories/update/{id}', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\CategoryController@update',
        'as' => 'admin.categories.update'
    ]);
    Route::get('home_categories', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@home_categories']);
    Route::get('categories/destroy/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@destroy']);
    Route::get('categories/remove_from_home/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@remove_from_home']);
    Route::post('categories/add_remove_home', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@add_remove_home']);
    Route::get('categories/show/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@show']);
    Route::get('categories/create_sub_category/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@create_sub_category']);
    Route::get('categories/edit_sub_category/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@edit_sub_category']);
    Route::get('classifications', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@classifications']);
    Route::get('classifications/create', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@create_classification']);
    Route::post('categories/store_classification', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\CategoryController@store_classification',
        'as' => 'admin.categories.store_classification'
    ]);
    Route::get('classifications/edit/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@edit_classification']);
    Route::post('categories/update_classification/{id}', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\CategoryController@update_classification',
        'as' => 'admin.categories.update_classification'
    ]);
    Route::get('categories/destroy_classification/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@destroy_classification']);
    Route::get('classification_detail/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@classification_detail']);
    Route::get('classifications/create_sub_classification/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@create_sub_classification']);
    Route::get('classifications/edit_sub_classification/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@edit_sub_classification']);
    Route::post('classifications/deleteClassified', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\CategoryController@deleteClassified']);

    Route::get('brands', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@index']);
    Route::get('home_brands', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@home_brands']);
    Route::get('brands/upload_images', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@upload_images']);
    Route::post('brands/store_multiple_images', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\BrandController@store_multiple_images',
        'as' => 'admin.brands.store_multiple_images'
    ]);
    Route::get('brands/create', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@create']);
    Route::post('brands/store', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\BrandController@store',
        'as' => 'admin.brands.store'
    ]);
    Route::get('brands/images', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@images']);
    Route::get('brands/create_multiple', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@create_multiple']);
    Route::post('brands/bulk_upload', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\BrandController@bulk_upload',
        'as' => 'admin.brands.bulk_upload'
    ]);
    Route::get('brands/edit/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@edit']);
    Route::post('brands/update/{id}', [
        'middleware' => 'permission:category-brand-management',
        'uses' => 'Admin\BrandController@update',
        'as' => 'admin.brands.update'
    ]);
    Route::get('brands/destroy/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@destroy']);
    Route::get('brands/remove_from_home/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@remove_from_home']);
    Route::post('brands/add_remove_home', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@add_remove_home']);
    Route::get('brands/show/{id}', ['middleware' => 'permission:category-brand-management', 'uses' => 'Admin\BrandController@show']);

    Route::get('banners', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\BannerController@index']);
    Route::get('banners/create', 'Admin\BannerController@create');
    Route::post('banners/store', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\BannerController@store',
        'as' => 'admin.banners.store'
    ]);
    Route::get('banners/edit/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\BannerController@edit']);
    Route::post('banners/update/{id}', [
        'uses' => 'Admin\BannerController@update',
        'as' => 'admin.banners.update'
    ]);
    Route::get('banners/detail/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\BannerController@detail']);
    Route::get('banners/destroy/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\BannerController@destroy']);

    Route::get('ads', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\AdsController@index']);
    Route::get('ads/create', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\AdsController@create']);
    Route::post('ads/store', [
        'middleware' => 'premission:news-ads-management',
        'uses' => 'Admin\AdsController@store',
        'as' => 'admin.ads.store'
    ]);
    Route::get('ads/edit/{id}', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\AdsController@edit']);
    Route::post('ads/update/{id}', [
        'middleware' => 'premission:news-ads-management',
        'uses' => 'Admin\AdsController@update',
        'as' => 'admin.ads.update'
    ]);
    Route::get('ads/detail/{id}', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\AdsController@detail']);
    Route::get('ads/destroy/{id}', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\AdsController@destroy']);

    Route::get('vendors', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\VendorController@index']);
    Route::get('vendors/show/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\VendorController@show']);
    Route::get('vendors/create', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\VendorController@create']);

    Route::post('change_status', 'Admin\AjaxController@change_status');
    Route::post('approve_reject_entity', 'Admin\AjaxController@approve_reject_entity');

    Route::post('vendors/store', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\VendorController@store',
        'as' => 'admin.vendors.store'
    ]);
    Route::get('vendors/edit/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\VendorController@edit']);
    Route::post('vendors/update/{id}', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\VendorController@update',
        'as' => 'admin.vendors.update'
    ]);

    Route::get('storekeepers', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@index']);
    Route::get('storekeepers/create', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@create']);
    Route::post('storekeepers/store', [
        'middleware' => 'permission:storekeeper-management',
        'uses' => 'Admin\StorekeeperController@store',
        'as' => 'admin.storekeepers.store'
    ]);
    Route::get('storekeepers/edit/{id}', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@edit']);
    Route::post('storekeepers/update/{id}', [
        'middleware' => 'permission:storekeeper-management',
        'uses' => 'Admin\StorekeeperController@update',
        'as' => 'admin.storekeepers.update'
    ]);
    Route::get('storekeepers/show/{id}', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@show']);
    Route::get('storekeepers/destroy/{id}', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@destroy']);
    Route::get('store/{id}/storekeepers', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@storekeepers']);
    Route::get('store/{id}/storekeepers/detail/{storekeeper_id}', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@storekeeper_detail']);
    Route::post('storekeepers/update_subcategories_storekeeper', ['middleware' => 'permission:storekeeper-management', 'uses' => 'Admin\StorekeeperController@update_subcategories_storekeeper']);
    
    // App homepage section - home_static upload
    Route::get('app_homepage/plus', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\AppHomeController@plus']);
    Route::post('app_homepage/plus', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@stores_home_static_plus',
        'as' => 'admin.app_homepage.stores_home_static_plus'
    ]);
    Route::get('app_homepage/mall', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\AppHomeController@mall']);
    Route::post('app_homepage/mall', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@stores_home_static_mall',
        'as' => 'admin.app_homepage.stores_home_static_mall'
    ]);
    Route::get('app_homepage/instant', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\AppHomeController@instant']);
    Route::post('app_homepage/instant', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@stores_home_static_instant',
        'as' => 'admin.app_homepage.stores_home_static_instant'
    ]);
    
    // App homepage section - home_static_instant upload
    Route::get('app_homepage/old_instant', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\AppHomeController@home_static_instant']);
    Route::post('app_homepage/home_static_instant_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_instant_store',
        'as' => 'admin.app_homepage.home_static_instant_store'
    ]);

    Route::get('app_homepage/stores', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\AppHomeController@stores']);
    Route::post('app_homepage/stores_home_static_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@stores_home_static_store',
        'as' => 'admin.app_homepage.stores_home_static_store'
    ]);
    Route::get('app_homepage', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\AppHomeController@index']);
    Route::post('app_homepage/home_static_1_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_1_store',
        'as' => 'admin.app_homepage.home_static_1_store'
    ]);
    Route::post('app_homepage/home_static_2_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_2_store',
        'as' => 'admin.app_homepage.home_static_2_store'
    ]);
    Route::post('app_homepage/home_static_3_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_3_store',
        'as' => 'admin.app_homepage.home_static_3_store'
    ]);
    Route::post('app_homepage/home_static_4_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_4_store',
        'as' => 'admin.app_homepage.home_static_4_store'
    ]);
    Route::post('app_homepage/home_static_5_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_5_store',
        'as' => 'admin.app_homepage.home_static_5_store'
    ]);
    Route::post('app_homepage/home_static_6_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_6_store',
        'as' => 'admin.app_homepage.home_static_6_store'
    ]);
    Route::post('app_homepage/home_static_7_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_7_store',
        'as' => 'admin.app_homepage.home_static_7_store'
    ]);
    Route::post('app_homepage/home_static_8_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_8_store',
        'as' => 'admin.app_homepage.home_static_8_store'
    ]);
    Route::post('app_homepage/home_static_9_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_9_store',
        'as' => 'admin.app_homepage.home_static_9_store'
    ]);
    Route::post('app_homepage/home_static_10_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_10_store',
        'as' => 'admin.app_homepage.home_static_10_store'
    ]);
    Route::post('app_homepage/home_static_11_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_11_store',
        'as' => 'admin.app_homepage.home_static_11_store'
    ]);
    Route::post('app_homepage/home_static_12_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_12_store',
        'as' => 'admin.app_homepage.home_static_12_store'
    ]);
    Route::post('app_homepage/home_static_13_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_13_store',
        'as' => 'admin.app_homepage.home_static_13_store'
    ]);
    Route::post('app_homepage/home_static_14_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_14_store',
        'as' => 'admin.app_homepage.home_static_14_store'
    ]);
    Route::post('app_homepage/home_static_15_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_static_15_store',
        'as' => 'admin.app_homepage.home_static_15_store'
    ]);
    Route::post('app_homepage/home_personalized_1_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_personalized_1_store',
        'as' => 'admin.app_homepage.home_personalized_1_store'
    ]);
    Route::post('app_homepage/home_personalized_2_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_personalized_2_store',
        'as' => 'admin.app_homepage.home_personalized_2_store'
    ]);
    Route::post('app_homepage/home_personalized_3_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\AppHomeController@home_personalized_3_store',
        'as' => 'admin.app_homepage.home_personalized_3_store'
    ]);
    // App homepage section - not using now
    Route::post('app_homepage_remove', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_remove']);
    Route::get('app_homepage_create', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_create']);
    Route::post('app_homepage_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\ProductController@app_homepage_store',
        'as' => 'admin.app_homepage_store'
    ]);
    Route::get('app_homepage_edit/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_edit']);
    Route::post('app_homepage_update/{id}', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\ProductController@app_homepage_update',
        'as' => 'admin.app_homepage_update'
    ]);

    Route::get('app_homepage_detail/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_detail']);
    Route::get('app_homepage_add_data/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_add_data']);
    Route::get('app_homepage_edit_data/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_edit_data']);
    Route::post('app_homepage_update_data/{id}', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\ProductController@app_homepage_update_data',
        'as' => 'admin.app_homepage_update_data'
    ]);
    Route::post('app_homepage_add_remove_item', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_add_remove_item']);
    Route::post('app_homepage_remove_data', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_remove_data']);
    Route::post('app_homepage_store_data', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\ProductController@app_homepage_store_data',
        'as' => 'admin.app_homepage_store_data'
    ]);
    Route::get('app_homepage_add_banner_products/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_add_banner_products']);
    Route::post('app_homepage_banner_add_remove_item', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\ProductController@app_homepage_banner_add_remove_item']);

    Route::post('products/home_static_store', [
        'middleware' => 'permission:marketing-management',
        'uses' => 'Admin\ProductController@home_static_store',
        'as' => 'admin.products.home_static_store'
    ]);

    Route::post('/proccess_home_static_json', 'Admin\AppHomeController@proccessHomeStaticJsonFile')->name('proccess-home-static-json');

    // Products section
    Route::post('products/delete_product', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@delete_product']);
    Route::post('products/set_home_product', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@set_home_product']);
    Route::get('products/remove_from_home_popular/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@remove_from_home_popular']);
    Route::get('products/remove_from_home_essential/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@remove_from_home_essential']);
    Route::get('products/upload_images', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@upload_images']);
    Route::post('products/store_multiple_images', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@store_multiple_images',
        'as' => 'admin.products.store_multiple_images'
    ]);
    Route::get('products/images', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@images']);
    Route::get('free-products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@free_products']);
    Route::post('products/update_free_products', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@update_free_products',
        'as' => 'admin.products.update_free_products'
    ]);
    Route::post('products/update_free_product', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@update_free_product',
        'as' => 'admin.products.update_free_product'
    ]);
    Route::post('products/get_sub_classification', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@get_sub_classification']);
    Route::post('products/add_classified_product', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@add_classified_product',
        'as' => 'admin.products.add_classified_product'
    ]);
    Route::get('products/price_formula', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@price_formula'])->name('price_formula');
    Route::get('products/filter_price_formula', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@filter_price_formula'])->name('filter-price-formula');
    Route::get('products/edit-formula/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@edit_formula']);
    Route::post('products/update_price_formula/{id}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@update_price_formula',
        'as' => 'admin.products.update_price_formula'
    ]);
    Route::get('products/create-formula/{id}/{subcategory}/{brand}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@create_formula']);
    Route::post('products/store_price_formula/{id}/{subcategory}/{brand}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@store_price_formula',
        'as' => 'admin.products.store_price_formula'
    ]);

    //copy all store formula
    Route::get('products/copy-default-formula/{id}/{subcategory}/{brand}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@copy_default_formula',
        'as' => 'admin.products.copy_default_formula'
    ]);

    // delete store price formula combination
    Route::get('products/delete-store-price-formula/{id}/{subcategory}/{brand}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@delete_store_price_formula',
        'as' => 'admin.products.delete_store_price_formula'
    ]);

    Route::get('products/delete-price-formula/{id}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@delete_price_formula',
        'as' => 'admin.products.delete_price_formula'
    ]);

    //offer pricing formula
    Route::get('products/apply_formula', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@apply_formula'])->name('apply_formula');
    Route::post('products/apply_formula_to_all_products', [
        'middleware' => 'permission:product-management', 
        'uses' => 'Admin\ProductController@apply_formula_to_all_products',
        'as' => 'admin.products.apply_formula_to_all_products'
    ]);
    
    Route::get('products/offer_formula', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@offer_formula'])->name('offer_formula');
    Route::get('products/filter_offer_formula', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@filter_offer_formula'])->name('filter-offer-formula');
    Route::post('products/store_offer_formula/{id}/{subcategory}/{brand}', [
            'middleware' => 'permission:product-management', 
            'uses' => 'Admin\ProductController@store_offer_formula',
            'as' => 'admin.products.store_offer_formula'
        ]);

    Route::get('products/edit-offer-formula/{id}', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@edit_offer_formula'])->name('edit-offer-formula');
    Route::get('products/delete-offer-formula/{id}', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\ProductController@delete_offer_formula',
        'as' => 'admin.products.delete_offer_formula'
    ]);

    Route::get('products/top-searched-products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@top_searched_products']);
    Route::get('products/top-selling-products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@top_selling_products']);
    Route::get('products/recent-searched-keywords', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@recent_searched_keywords']);
    Route::get('remaining-products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@null_itemcode_products']);
    Route::get('new-products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@new_products']);
    Route::post('products/update_product_stock', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@update_product_stock']);
    Route::get('products/get_product_upload_progress', ['middleware' => 'permission:product-management', 'uses' => 'Admin\ProductController@get_product_upload_progress']);

    Route::get('products/suggestions', ['middleware' => 'permission:suggested-product-management', 'uses' => 'Admin\ProductController@product_suggestions']);

    // Coupons
    Route::get('coupons', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@index']);
    Route::get('coupons/show/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@show']);
    Route::get('coupons/create', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@create']);
    Route::post('coupons/change_coupon_status', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@change_coupon_status']);
    Route::post('coupons/store', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\CouponController@store',
        'as' => 'admin.coupons.store'
    ]);
    Route::get('coupons/recreate/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@recreate']);
    Route::post('coupons/update/{id}', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\CouponController@update',
        'as' => 'admin.coupons.update'
    ]);
    Route::get('coupons/edit/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@edit']);
    Route::post('coupons/update_coupon/{id}', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\CouponController@update_coupon',
        'as' => 'admin.coupons.update_coupon'
    ]);

    Route::get('coupons_hidden', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@index_hidden']);
    Route::get('coupons_hidden/show/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@show_hidden']);
    Route::get('coupons_hidden/create', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@create_hidden']);
    Route::post('coupons_hidden/change_coupon_status', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@change_coupon_status']);
    Route::post('coupons_hidden/store_hidden', [
        'uses' => 'Admin\CouponController@store_hidden',
        'as' => 'admin.coupons_hidden.store'
    ]);
    Route::get('coupons_hidden/recreate/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@recreate_hidden']);
    Route::post('coupons_hidden/update/{id}', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\CouponController@update_hidden',
        'as' => 'admin.coupons_hidden.update'
    ]);
    Route::get('coupons_hidden/edit/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@edit_hidden']);
    Route::post('coupons_hidden/update_coupon/{id}', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\CouponController@update_coupon',
        'as' => 'admin.coupons_coupon.update'
    ]);
    Route::get('coupons_hidden/create_multiple', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\CouponController@create_multiple_hidden']);
    Route::post('coupons_hidden/bulk_upload_hidden', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\CouponController@bulk_upload_hidden',
        'as' => 'admin.coupons_hidden.bulk_upload'
    ]);

    // Users
    Route::get('users', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@index']);
    Route::get('users/abondoned_cart', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@index_abondoned_cart']);
    Route::get('users/show/{id}', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@show']);
    Route::get('users/mobile/{mobile}', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@show_by_mobile']);
    Route::post('users/enable_user_features', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@enable_user_features']);
    Route::post('users/change_user_status', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@change_user_status']);
    Route::get('users/delete/{id}', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@delete']);
    Route::post('users/send_push_notification', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@send_push_notification']);
    Route::post('users/add_wallet', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@add_wallet']);
    Route::post('users/add_scratch_card', ['middleware' => 'permission:customer-management', 'uses' => 'Admin\UserController@add_scratch_card']);

    Route::get('drivers', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@index']);
    Route::get('drivers/create', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@create']);
    Route::post('drivers/store', [
        'middleware' => 'permission:driver-management',
        'uses' => 'Admin\DriverController@store',
        'as' => 'admin.drivers.store'
    ]);
    Route::get('drivers/edit/{id}', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@edit']);
    Route::post('drivers/update/{id}', [
        'middleware' => 'permission:driver-management',
        'uses' => 'Admin\DriverController@update',
        'as' => 'admin.drivers.update'
    ]);

    Route::get('drivers/show/{id}', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@show']);
    Route::get('drivers/destroy/{id}', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@destroy']);
    Route::post('drivers/change_driver_status', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@change_driver_status']);
    Route::get('drivers/get_store_groups', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@get_store_groups']);
    Route::get('drivers/get_group_stores', ['middleware' => 'permission:driver-management', 'uses' => 'Admin\DriverController@get_group_stores']);
    Route::get('vehicles', ['middleware' => 'permission:vehicle-management', 'uses' => 'Admin\DriverController@vehicles']);
    Route::get('add-vehicle', ['middleware' => 'permission:vehicle-management', 'uses' => 'Admin\DriverController@create_vehicle']);
    Route::post('store-vehicle', [
        'middleware' => 'permission:vehicle-management',
        'uses' => 'Admin\DriverController@store_vehicle',
        'as' => 'admin.drivers.store_vehicle'
    ]);
    Route::get('edit-vehicle/{id}', ['middleware' => 'permission:vehicle-management', 'uses' => 'Admin\DriverController@edit_vehicle']);
    Route::post('update-vehicle/{id}', [
        'middleware' => 'permission:vehicle-management',
        'uses' => 'Admin\DriverController@update_vehicle',
        'as' => 'admin.drivers.update_vehicle'
    ]);
    Route::post('update_driver_vehicle', ['middleware' => 'permission:vehicle-management', 'uses' => 'Admin\DriverController@update_driver_vehicle']);

    Route::get('all-orders', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@all_orders']);
    Route::post('orders/order_generate_json', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\OrderController@order_generate_json',
        'as' => 'admin.orders.order_generate_json'
    ]);
    Route::post('orders/apply_all_order_generate_jsons', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\OrderController@apply_all_order_generate_jsons',
        'as' => 'admin.orders.apply_all_order_generate_jsons'
    ]);
    Route::get('orders/export_orders', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\OrderController@export_orders', 
        'as' => 'admin.orders.export_orders'
    ]);
    Route::get('forgot-something-orders', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@forgot_something_orders']);
    // Route::get('orders/{slot}', 'Admin\OrderController@all_orders');
    Route::get('active-orders', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@active_orders']);
    Route::get('later-orders', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@later_orders']);
    Route::get('payments', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@payments']);
    Route::get('payments/detail/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@payment_detail']);
    Route::get('orders/detail/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@detail']);
    Route::post('orders/update_status/{id}', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\OrderController@update_status',
        'as' => 'admin.orders.update_status'
    ]);
    Route::post('orders/allocate_order', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@allocate_order']);
    Route::post('orders/get_slots', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@get_slots']);
    Route::post('orders/update_driver/{id}', [
        'middleware' => 'permission:orders-management',
        'uses' => 'Admin\OrderController@update_driver',
        'as' => 'admin.orders.update_driver'
    ]);
    Route::post('orders/cancel_order', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@cancel_order']);
    Route::post('orders/refund_order_amount', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@refund_order_amount']);
    Route::post('orders/cancel_order_detail', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@cancel_order_detail']);

    Route::get('orders/replacement_options/{id}', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@replacement_options']);
    Route::post('orders/add_remove_replaced_products', ['middleware' => 'permission:orders-management', 'uses' => 'Admin\OrderController@add_remove_replaced_products']);

    //Technical Support Routes
    Route::post('orders/create_customer_support_ticket', 'Admin\SupportController@create_customer_support_ticket');

    Route::get('delivery-area', ['middleware' => 'permission:delivery-management', 'uses' => 'Admin\CommonController@delivery_area']);
    Route::post('update_delivery_area', ['middleware' => 'permission:delivery-management', 'uses' => 'Admin\CommonController@update_delivery_area']);
    Route::get('delivery-area/destroy/{id}', ['middleware' => 'permission:delivery-management', 'uses' => 'Admin\CommonController@destroy_area']);

    Route::get('slot-settings', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\CommonController@slot_settings']);
    Route::get('delivery-slots', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\CommonController@delivery_slots']);
    Route::get('add-delivery-slots', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\CommonController@create_delivery_slots']);
    Route::post('create_update_slot', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\CommonController@create_update_slot']);
    Route::post('delete_slot_setting', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\CommonController@delete_slot_setting']);
    Route::get('edit-delivery-slots/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\CommonController@edit_delivery_slots']);

    Route::post('store_delivery_slots', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\CommonController@store_delivery_slots',
        'as' => 'admin.store_delivery_slots'
    ]);
    Route::post('update_delivery_slots', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\CommonController@update_delivery_slots',
        'as' => 'admin.update_delivery_slots'
    ]);
    
    // offer options
    Route::get('base_products/offer_options', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@offer_options']);
    Route::get('base_products/create_offer_option', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@create_offer_option']);
    Route::post('base_products/store_offer_option', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@store_offer_option'])->name('admin.base_products.store_offer_option');
    Route::get('base_products/edit_offer_option/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@edit_offer_option']);
    Route::post('base_products/update_offer_option/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@update_offer_option'])->name('admin.base_products.update_offer_option');
    Route::get('base_products/delete_offer_option/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@delete_offer_option']);

    // offer products
    Route::get('base_products/product_offers', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@product_offers']);
    Route::post('base_products/update_product_offer/{id}', ['middleware' => 'permission:marketing-management', 'uses' => 'Admin\BaseProductController@update_product_offer'])->name('admin.base_products.update_product_offer');

    Route::get('base_products/products_discount', ['middleware' => 'permission:product-management', 'uses' => 'Admin\BaseProductController@products_discount']);
    Route::post('base_products/update_products_discount', [
        'middleware' => 'permission:product-management',
        'uses' => 'Admin\BaseProductController@update_products_discount',
        'as' => 'admin.base_products.update_products_discount'
    ]);

    // feedback
    Route::get('user-feedback', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\FeedbackController@feedback']);
    Route::get('order-reviews', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\FeedbackController@reviews']);

    // technical support
    Route::get('technical-support', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\SupportController@technical_support']);
    Route::get('technical-support/detail/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\SupportController@technical_support_detail']);
    Route::post('support/send-message', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\SupportController@send_message',
        'as' => 'admin.support.send_message'
    ]);
    Route::post('support/send-image', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\SupportController@send_image',
        'as' => 'admin.support.send_image'
    ]);

    Route::get('customer-support', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\SupportController@customer_support']);
    Route::get('customer-support/detail/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\SupportController@customer_support_detail']);
    Route::post('support/open_close_ticket', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\SupportController@open_close_ticket']);

    Route::get('news', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\NewsController@index']);
    Route::get('news/create', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\NewsController@create']);
    Route::post('news/store', [
        'middleware' => 'permission:news-ads-management',
        'uses' => 'Admin\NewsController@store',
        'as' => 'admin.news.store'
    ]);
    Route::get('news/edit/{id}', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\NewsController@edit']);
    Route::post('news/update/{id}', [
        'middleware' => 'permission:news-ads-management',
        'uses' => 'Admin\NewsController@update',
        'as' => 'admin.news.update'
    ]);
    Route::get('news/destroy/{id}', ['middleware' => 'permission:news-ads-management', 'uses' => 'Admin\NewsController@destroy']);

    // Custome notifications
    Route::get('custom_notifications', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@index']);
    Route::get('custom_notification/create', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@create']);
    Route::post('custom_notification/store', [
        'middleware' => 'permission:notification-management',
        'uses' => 'Admin\CustomNotificationController@store',
        'as' => 'admin.custom_notification.store'
    ]);
    Route::get('custom_notification/edit/{id}', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@edit']);
    Route::post('custom_notification/update/{id}', [
        'middleware' => 'permission:notification-management',
        'uses' => 'Admin\CustomNotificationController@update',
        'as' => 'admin.custom_notification.update'
    ]);
    Route::get('custom_notification/detail/{id}', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@show']);
    Route::get('custom_notification/destroy/{id}', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@destroy']);

    // Sheduling notifications
    Route::get('sheduled_notifications', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@sheduled_notifications']);
    Route::get('sheduled_notification/create', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@sheduled_notification_create']);
    Route::post('sheduled_notification/store', [
        'middleware' => 'permission:notification-management',
        'uses' => 'Admin\CustomNotificationController@sheduled_notification_store',
        'as' => 'admin.sheduled_notification.store'
    ]);
    Route::get('sheduled_notification/edit/{id}', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@sheduled_notification_edit']);
    Route::post('sheduled_notification/update/{id}', [
        'middleware' => 'permission:notification-management',
        'uses' => 'Admin\CustomNotificationController@sheduled_notification_update',
        'as' => 'admin.sheduled_notification.update'
    ]);
    Route::get('sheduled_notification/resend/{id}', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@sheduled_notification_resend']);
    Route::post('sheduled_notification/resend/{id}', [
        'middleware' => 'permission:notification-management',
        'uses' => 'Admin\CustomNotificationController@sheduled_notification_resend_update',
        'as' => 'admin.sheduled_notification.resend_update'
    ]);
    Route::get('sheduled_notification/detail/{id}', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@sheduled_notification_show']);
    Route::get('sheduled_notification/destroy/{id}', ['middleware' => 'permission:notification-management', 'uses' => 'Admin\CustomNotificationController@sheduled_notification_destroy']);

    // Recipes routes
    Route::get('recipes',  ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@index']);
    Route::get('recipes/home', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@app_homepage']);
    Route::post('recipes/home_static_store', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeController@home_static_store',
        'as' => 'admin.recipes.home_static_store'
    ]);
    Route::get('recipes/create', ['middleware' => 'permission:recipe-management', 'uses' =>  'Admin\RecipeController@create']);
    Route::post('recipe/store', [
        'uses' => 'Admin\RecipeController@store',
        'as' => 'admin.recipe.store'
    ]);
    Route::get('recipes/edit/{id}',  ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@edit']);
    Route::post('recipe/update/{id}', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeController@update',
        'as' => 'admin.recipe.update'
    ]);
    Route::get('recipes/destroy/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@destroy']);
    Route::post('recipes/edit_ingredient_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@edit_ingredient_save']);
    Route::post('recipes/add_ingredient_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@add_ingredient_save']);
    Route::post('recipes/delete_ingredient_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@delete_ingredient_save']);
    
    Route::post('recipes/edit_step_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@edit_step_save']);
    Route::post('recipes/add_step_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@add_step_save']);
    Route::post('recipes/delete_step_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@delete_step_save']);

    Route::post('recipes/edit_variant_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@edit_variant_save']);
    Route::post('recipes/add_variant_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@add_variant_save']);
    Route::post('recipes/delete_variant_save', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@delete_variant_save']);

    // Recipe variant routes
    Route::get('recipes/edit/{id}/view_recipe_variant', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@view_recipe_variant']);
    Route::get('recipes/edit/{id}/create_recipe_variant', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@create_recipe_variant']);
    Route::post('recipe/store_recipe_variant', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeController@store_recipe_variant',
        'as' => 'admin.recipe.store_recipe_variant'
    ]);
    Route::get('recipes/edit_recipe_variant/{id}/', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@edit_recipe_variant']);
    Route::post('recipe/update_recipe_variant/{id}', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeController@update_recipe_variant',
        'as' => 'admin.recipe.update_recipe_variant'
    ]);
    Route::get('recipes/destroy_recipe_variant/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@destroy_recipe_variant']);

    Route::post('recipes/set_featured_recipe', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@set_featured_recipe']);
    Route::post('recipes/get_products', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeController@get_products']);

    // Recipe Tags
    Route::get('recipe_tags', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeTagController@index']);
    Route::get('recipe_tags/create', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeTagController@create']);
    Route::post('recipe_tags/store', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeTagController@store',
        'as' => 'admin.recipe_tags.store'
    ]);
    Route::get('recipe_tags/edit/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeTagController@edit']);
    Route::post('recipe_tags/update/{id}', [
        'uses' => 'Admin\RecipeTagController@update',
        'as' => 'admin.recipe_tags.update'
    ]);
    Route::get('recipe_tags/destroy/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeTagController@destroy']);

    // Recipe Diets
    Route::get('recipe_diets', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeDietController@index']);
    Route::get('recipe_diets/create', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeDietController@create']);
    Route::post('recipe_diets/store', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeDietController@store',
        'as' => 'admin.recipe_diets.store'
    ]);
    Route::get('recipe_diets/edit/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeDietController@edit']);
    Route::post('recipe_diets/update/{id}', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeDietController@update',
        'as' => 'admin.recipe_diets.update'
    ]);
    Route::get('recipe_diets/destroy/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeDietController@destroy']);

    // Recipe Categories
    Route::get('recipe_categories', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeCategoryController@index']);
    Route::get('recipe_categories/create', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeCategoryController@create']);
    Route::post('recipe_categories/store', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeCategoryController@store',
        'as' => 'admin.recipe_categories.store'
    ]);
    Route::get('recipe_categories/edit/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeCategoryController@edit']);
    Route::post('recipe_categories/update/{id}', [
        'middleware' => 'permission:recipe-management',
        'uses' => 'Admin\RecipeCategoryController@update',
        'as' => 'admin.recipe_categories.update'
    ]);
    Route::get('recipe_categories/destroy/{id}', ['middleware' => 'permission:recipe-management', 'uses' => 'Admin\RecipeCategoryController@destroy']);

    Route::get('twosteptags', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\TwoStepController@index']);
    Route::get('twosteptags/create', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\TwoStepController@create']);
    Route::post('twosteptags/store', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\TwoStepController@store',
        'as' => 'admin.twosteptags.store'
    ]);
    Route::get('twosteptags/edit/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\TwoStepController@edit']);
    Route::post('twosteptags/update/{id}', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\TwoStepController@update',
        'as' => 'admin.twosteptags.update'
    ]);
    Route::get('twosteptags/destroy/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\TwoStepController@destroy']);

    // Affiliates
    Route::get('affiliates', ['middleware' => 'permission:affiliate-management', 'uses' => 'Admin\AffiliateController@index']);
    Route::get('affiliates/create', ['middleware' => 'permission:affiliate-management', 'uses' => 'Admin\AffiliateController@create']);
    Route::post('affiliates/store', [
        'middleware' => 'permission:affiliate-management',
        'uses' => 'Admin\AffiliateController@store',
        'as' => 'admin.affiliates.store'
    ]);
    Route::get('affiliates/edit/{id}', ['middleware' => 'permission:affiliate-management', 'uses' => 'Admin\AffiliateController@edit']);
    Route::post('affiliates/update/{id}', [
        'middleware' => 'permission:affiliate-management',
        'uses' => 'Admin\AffiliateController@update',
        'as' => 'admin.affiliates.update'
    ]);
    Route::get('affiliates/detail/{id}', ['middleware' => 'permission:affiliate-management', 'uses' => 'Admin\AffiliateController@show']);
    Route::get('affiliates/destroy/{id}', ['middleware' => 'permission:affiliate-management', 'uses' => 'Admin\AffiliateController@destroy']);

    // Admin Users Role Permissions
    Route::get('administrators', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\AdminController@index']);
    Route::get('administrators/create', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\AdminController@create']);
    Route::post('administrators/store', [
        'uses' => 'Admin\AdminController@store',
        'as' => 'admin.administrators.store'
    ]);
    Route::get('administrators/edit/{id}', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\AdminController@edit']);
    Route::post('administrators/update/{id}', [
        'uses' => 'Admin\AdminController@update',
        'as' => 'admin.administrators.update'
    ]);
    Route::get('administrators/delete/{id}', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\AdminController@delete']);

    Route::get('roles', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\RoleController@index']);
    Route::get('roles/create', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\RoleController@create']);
    Route::post('roles/store', [
        'middleware' => 'permission:administrator-management',
        'uses' => 'Admin\RoleController@store',
        'as' => 'admin.roles.store'
    ]);
    Route::get('roles/edit/{id}', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\RoleController@edit']);
    Route::post('roles/update/{id}', [
        'middleware' => 'permission:administrator-management',
        'uses' => 'Admin\RoleController@update',
        'as' => 'admin.roles.update'
    ]);
    Route::get('roles/delete/{id}', ['middleware' => 'permission:administrator-management', 'uses' => 'Admin\RoleController@delete']);

    // Admin image uploads
    Route::get('upload_image', ['middleware' => 'permission:image-management', 'uses' => 'Admin\ImageController@index']);
    Route::post('upload_image_remove', ['middleware' => 'permission:image-management', 'uses' => 'Admin\ImageController@upload_image_remove']);
    Route::post('upload_image_store', [
        'middleware' => 'permission:image-management',
        'uses' => 'Admin\ImageController@upload_image_store',
        'as' => 'admin.upload_image_store'
    ]);

    // PayThem
    Route::get('paythem/base_products', ['middleware' => 'permission:product-management', 'uses' => 'Admin\PayThemController@index']);
    
    // Scratch card
    Route::get('scratch_cards', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@index']);
    Route::get('scratch_cards_users', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@index_users']);
    Route::get('scratch_cards/show/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@show']);
    Route::get('scratch_cards/create', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@create']);
    Route::post('scratch_cards/change_scratch_card_status', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@change_scratch_card_status']);
    Route::post('scratch_cards/store', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\ScratchCardController@store',
        'as' => 'admin.scratch_cards.store'
    ]);
    Route::get('scratch_cards/edit/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@edit']);
    Route::post('scratch_cards/update/{id}', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\ScratchCardController@update',
        'as' => 'admin.scratch_cards.update'
    ]);
    Route::post('scratch_cards/change_scratch_cards_status', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@change_scratch_cards_status']);
    Route::get('scratch_cards/bulk_upload_to_users', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ScratchCardController@bulk_upload_to_users']);
    Route::post('scratch_cards/bulk_upload_to_users_post', [
        'middleware' => 'permission:super-admin',
        'uses' => 'Admin\ScratchCardController@bulk_upload_to_users_post',
        'as' => 'admin.scratch_cards.bulk_upload_to_users_post'
    ]);

    // WhatsApp
    Route::get('whatsapp', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\WhatsAppController@index']);
    Route::get('whatsapp', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\WhatsAppController@index']);
    Route::get('whatsapp/user/create', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\WhatsAppController@create']);
    Route::get('whatsapp/user/delete/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\WhatsAppController@delete']);
    Route::post('whatsapp/order_payment', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\WhatsAppController@order_payment']);

    //Bot Rotator
    Route::get('bot_rotator', ['middleware' => 'permission:bot-management', 'uses' => 'Admin\BotRotatorController@index']);
    Route::get('bot_rotator/create', ['middleware' => 'permission:bot-management', 'uses' => 'Admin\BotRotatorController@create']);
    Route::post('bot_rotator/store', ['middleware' => 'permission:bot-management', 'uses' => 'Admin\BotRotatorController@store']);
    Route::post('bot_rotator/upload_csv', ['middleware' => 'permission:bot-management', 'uses' => 'Admin\BotRotatorController@upload_csv']);
    Route::post('bot_rotator/rotate', ['middleware' => 'permission:bot-management', 'uses' => 'Admin\BotRotatorController@rotate']);
});
Route::get('admin/batch/{id}', ['middleware' => 'permission:super-admin', 'uses' => 'Admin\ProductController@batch']);

Route::group(['prefix' => 'account', 'middleware' => ['account', 'admin_login_access']], function () {
    Route::get('/', 'Account\AuthController@login_get');
    Route::get('login', 'Account\AuthController@login_get');
});

Route::group(['prefix' => 'account', 'middleware' => ['admin_login_access']], function () {
    Route::post('login_post', 'Account\AuthController@login_post');
    Route::get('logout', 'Account\AuthController@logout');
});
 
Route::group(['prefix' => 'account', 'middleware' => ['admin_login_access']], function () {
    Route::get('invoice', 'Account\InvoiceController@index');
    Route::get('invoice/{id}/edit', 'Account\InvoiceController@edit');
    Route::patch('invoice/update/{id}', [
        'uses' => 'Account\InvoiceController@update',
        'as' => 'account.invoice.update'
    ]);
    Route::get('invoice/{id}', 'Account\InvoiceController@show');

    Route::get('orders', 'Account\OrderController@index');
    Route::get('orders/show/{id}', 'Account\OrderController@show');
    Route::get('wallets', 'Account\WalletController@index');
    Route::get('wallets/{id}', 'Account\WalletController@show');
    // Route::post('/downloadImage', [InvoiceController::class, 'downloadImage'])->name('downloadImage');    
});
Route::post('account/invoice/downloadImage', [
    'uses' => 'Account\InvoiceController@downloadImage',
    'as' => 'account.invoice.downloadImage'
]);

//Khushbooo
// Route::get('vendor/dashboard/{id}',  'Admin\DashboardController@index');

Route::group(['prefix' => 'vendor', 'middleware' => ['admin_login_access']], function () {

    Route::get('/', 'Vendor\AuthController@login_get');
    Route::get('login', 'Vendor\AuthController@login_get')->name('vendor_login');
    Route::post('login_post', 'Vendor\AuthController@login_post');
    Route::get('forgot_password', 'Vendor\AuthController@forgot_password');
    Route::post('send_password_reset_link', 'Vendor\AuthController@send_password_reset_link');
    Route::post('set_new_password', 'Vendor\AuthController@set_new_password');
    Route::get('reset_password', 'Vendor\AuthController@reset_password');
    // Route::get('register', 'Vendor\AuthController@register');
    // Route::post('register_post', 'Vendor\AuthController@register_post');
    Route::get('logout', 'Vendor\AuthController@logout');
    
    Route::get('dashboard',  'Vendor\DashboardController@index')->name('vendor_dashboard');

    //brands
    Route::get('brands',  'Vendor\BrandController@index')->name('vendor_products_brands');
    Route::get('brands/create',  'Vendor\BrandController@create')->name('vendor_products_create');
    Route::post('brands/store',  'Vendor\BrandController@store')->name('vendor.brands.store');
    Route::get('brands/show/{id}',  'Vendor\BrandController@show')->name('vendor.brands.show');
    Route::get('brands/edit/{id}',  'Vendor\BrandController@edit')->name('vendor.brands.edit');
    Route::post('brands/update/{id}',  'Vendor\BrandController@update')->name('vendor.brands.update');
    Route::get('brands/destroy/{id}',  'Vendor\BrandController@destroy')->name('vendor.brands.destroy');
    Route::get('brands/remove_from_home/{id}', 'Vendor\BrandController@remove_from_home');
    Route::post('brands/add_remove_home', 'Vendor\BrandController@add_remove_home');

    //products
    Route::get('all_products',  'Vendor\ProductController@index')->name('vendor_all_products');
    Route::get('product_stock_update',  'Vendor\ProductController@stock_update')->name('vendor_product_stock_update');
    Route::get('new_product',  'Vendor\ProductController@new_product')->name('vendor_new_product');
    Route::post('add_new_product',  'Vendor\ProductController@add_new_product')->name('vendor.products.store');
    Route::post('products/get_sub_category',  'Vendor\ProductController@get_sub_category');
    Route::get('products/view/{id}', 'Vendor\ProductController@view_product');
    Route::get('products/edit/{id}', 'Vendor\ProductController@edit_product');
    Route::post('products/update/{id}', 'Vendor\ProductController@update')->name('vendor.products.update');
    Route::get('products/delete/{id}', 'Vendor\ProductController@delete_product')->name('vendor.products.delete');
    Route::get('products/stock/{id}', 'Vendor\ProductController@product_stock')->name('vendor.products.product_stock');
    Route::get('products/stock/{id}/{batch_id}', 'Vendor\ProductController@product_stock')->name('vendor.products.product_stock');
    // Route::post('products/stock_update', 'Vendor\ProductController@product_stock_update')->name('vendor.products.stock_update');
    Route::get('batch/{id}', 'Vendor\ProductController@batch');
    Route::post('bulk_stock_update', 'Vendor\ProductController@bulk_stock_update')->name('vendor.all_products.bulk_stock_update');
    
    //fleet
    Route::get('fleet',  'Vendor\FleetController@index')->name('fleet_data');

    //orders
    Route::get('active_orders',  'Vendor\OrderController@active_orders')->name('vendor_active_orders');
    Route::get('completed_orders',  'Vendor\OrderController@completed_orders')->name('vendor-completed-orders');
    Route::get('cancelled_orders',  'Vendor\OrderController@canceled_orders')->name('vendor-cancelled-orders');
    Route::get('orders/detail/{id}',  'Vendor\OrderController@detail')->name('vendor-orders-detail');
    Route::get('orders/pdfview/{id}',  'Vendor\OrderController@detail')->name('vendor.orders.pdfview');
    Route::post('orders/update_status/{id}',  'Vendor\OrderController@update_status')->name('vendor.orders.update_status');
    Route::post('orders/update_driver/{id}',  'Vendor\OrderController@update_driver')->name('vendor.orders.update_driver');
    Route::get('orders/product-detail/{id}',  'Vendor\OrderController@ordered_product_detail');

    //coupons
    Route::get('coupons_list',  'Vendor\CouponsController@index')->name('vendor-coupons-list');
    Route::get('coupons/create',  'Vendor\CouponsController@create_coupon')->name('create_coupons');
    Route::post('coupons/store',  'Vendor\CouponsController@store_coupon')->name('vendor.coupons.store');
    Route::get('coupons/recreate/{id}', 'Vendor\CouponsController@recreate_coupon');
    Route::post('coupons/update/{id}', 'Vendor\CouponsController@update')->name('vendor.coupons.update');
    Route::get('coupons/edit/{id}', 'Vendor\CouponsController@edit');
    Route::get('coupons/show/{id}', 'Vendor\CouponsController@show');
    Route::post('coupons/update_coupon/{id}', 'Vendor\CouponsController@update_coupon')->name('vendor.coupons.update_coupon');
    Route::post('coupons/change_coupon_status', 'Vendor\CouponsController@change_coupon_status');


    //settings
    
    Route::get('profile',  'Vendor\SettingsController@profile_page')->name('vendor_profile');
    Route::post('update_profile',  'Vendor\SettingsController@update_profile')->name('update_profile');
    Route::get('store_setting',  'Vendor\SettingsController@stores_data')->name('vendor-stores');
    Route::post('update_store_detail',  'Vendor\SettingsController@update_store_detail')->name('update_store_detail');
    Route::get('payment_card',  'Vendor\SettingsController@payment_card')->name('vendor-payment-card');
    Route::post('payment_store',  'Vendor\SettingsController@card_store')->name('vendor.settings.payment_store');
    Route::get('pass_key',  'Vendor\SettingsController@pass_token')->name('pass_token');
    Route::post('pass_key_generate',  'Vendor\SettingsController@pass_token_generate')->name('vendor.settings.pass_key_generate');
    
    //Analytics
    Route::get('analytics',  'Vendor\DashboardController@analytics')->name('vendor-analytics');

    //Accounts
    Route::get('subscription',  'Vendor\AccountsController@subscription')->name('vendor-subscription');
    Route::get('billing_overview',  'Vendor\AccountsController@billing_overview')->name('billing_overview');
    Route::get('usage_details',  'Vendor\AccountsController@usage_details')->name('usage_details');
    Route::get('payment_details',  'Vendor\AccountsController@payment_details')->name('payment_details');
    Route::get('invoice',  'Vendor\AccountsController@invoice')->name('invoice');

});



