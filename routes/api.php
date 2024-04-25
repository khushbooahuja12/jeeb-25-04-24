<?php

use Illuminate\Http\Request;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('users/register', 'Api\User\AuthController@register');
Route::post('users/verify_otp', 'Api\User\AuthController@verify_otp');
Route::post('users/resend_otp', 'Api\User\AuthController@resend_otp');

Route::post('users/guest_login', 'Api\User\AuthController@guest_login');

Route::group(['middleware' => ['api_auth']], function () {
    Route::get('users/logout', 'Api\User\AuthController@logout');
    Route::post('users/cancel_myaccount', 'Api\User\AuthController@cancel_myaccount');
    Route::get('users/delivery_slots', 'Api\User\OrderController@delivery_slots');

    Route::get('users/my_features', 'Api\User\HomeBpController@my_features');

    Route::post('users/get_profile', 'Api\User\ProfileController@get_profile');
    Route::post('users/update_profile', 'Api\User\ProfileController@update_profile')->middleware('log.route');

    Route::get('users/my_cart', 'Api\User\HomeController@my_cart');
    Route::get('users/my_cart_bp', 'Api\User\HomeBpController@my_cart');
    Route::post('users/update_cart', 'Api\User\HomeController@update_cart');
    Route::post('users/update_cart_bp', 'Api\User\HomeBpController@update_cart');
    Route::post('users/add_to_cart', 'Api\User\HomeController@add_to_cart');
    Route::post('users/add_to_cart_bp', 'Api\User\HomeBpController@add_to_cart');
    Route::post('users/remove_from_cart', 'Api\User\HomeController@remove_from_cart');
    Route::post('users/remove_from_cart_bp', 'Api\User\HomeBpController@remove_from_cart');
    Route::post('users/remove_outofstock_products_from_cart', 'Api\User\HomeController@remove_outofstock_products_from_cart');
    Route::post('users/save_cart', 'Api\User\HomeController@save_cart');
    Route::post('users/update_saved_cart', 'Api\User\HomeController@update_saved_cart');
    Route::post('users/remove_saved_cart', 'Api\User\HomeController@remove_saved_cart');
    
    Route::post('users/save_cart_bp', 'Api\User\HomeBpController@save_cart');
    Route::post('users/update_saved_cart_bp', 'Api\User\HomeBpController@update_saved_cart');
    Route::post('users/remove_saved_cart_bp', 'Api\User\HomeBpController@remove_saved_cart');
    Route::post('users/view_saved_cart_bp', 'Api\User\HomeBpController@view_saved_cart');
    Route::post('users/my_saved_cart_bp', 'Api\User\HomeBpController@my_saved_cart');
    
    Route::get('users/address_list', 'Api\User\AddressController@address_list');
    Route::post('users/add_new_address', 'Api\User\AddressController@add_new_address');
    Route::post('users/update_address', 'Api\User\AddressController@update_address');
    Route::post('users/delete_address', 'Api\User\AddressController@delete_address');
    Route::post('users/set_default_address', 'Api\User\AddressController@set_default_address');
    Route::get('users/get_default_address', 'Api\User\AddressController@get_default_address');

    Route::post('users/add_favorite', 'Api\User\ProductController@add_favorite');
    Route::post('users/add_favorite_bp', 'Api\User\ProductBpController@add_favorite');
    Route::get('users/view_all_favorite_products', 'Api\User\ProductController@view_all_favorite_products');
    Route::get('users/view_all_favorite_products_bp', 'Api\User\ProductBpController@view_all_favorite_products');

    Route::get('users/get_delivery_charge', 'Api\User\OrderController@get_delivery_charge');
    Route::post('users/apply_coupon', 'Api\User\OrderBpTapController@apply_coupon');
    Route::get('users/getOrderRefId', 'Api\User\OrderController@getOrderRefId');
    Route::post('users/make_order', 'Api\User\OrderController@make_order');
    Route::post('users/make_order_bp', 'Api\User\OrderBpController@make_order');
    Route::post('users/make_order_bp_tap', 'Api\User\OrderBpTapController@make_order');
    Route::post('users/make_order_bp_tap_pay', 'Api\User\OrderBpTapController@make_order_pay');
    Route::post('users/make_order_pay_by_token', 'Api\User\OrderBpTapController@make_order_pay_by_token');
    Route::post('users/make_order_pay_by_card_id', 'Api\User\OrderBpTapController@make_order_pay_by_card_id');
    Route::post('users/get_order_pay_status', 'Api\User\OrderBpTapController@get_order_pay_status');
    Route::post('users/get_subtotal_forgot_something_order', 'Api\User\OrderController@get_subtotal_forgot_something_order');
    Route::post('users/get_subtotal_forgot_something_order_bp', 'Api\User\OrderBpTapController@get_subtotal_forgot_something_order');
    Route::post('users/make_forgot_something_order', 'Api\User\OrderController@make_forgot_something_order');
    Route::post('users/make_forgot_something_order_bp', 'Api\User\OrderBpController@make_forgot_something_order');
    Route::post('users/make_forgot_something_order_bp_tap', 'Api\User\OrderBpTapController@make_forgot_something_order');
    Route::post('users/make_forgot_something_order_bp_tap_pay', 'Api\User\OrderBpTapController@make_forgot_something_order_pay');
    Route::post('users/make_forgot_something_order_bp_tap_pay_by_card_id', 'Api\User\OrderBpTapController@make_forgot_something_order_pay_by_card_id');
    Route::post('users/get_forgot_something_order_pay_status', 'Api\User\OrderBpTapController@get_forgot_something_order_pay_status');
    Route::post('users/get_subtotal_replacement_order', 'Api\User\OrderController@get_subtotal_replacement_order');
    Route::post('users/get_subtotal_replacement_order_bp', 'Api\User\OrderBpController@get_subtotal_replacement_order');
    Route::post('users/make_replacement_order', 'Api\User\OrderController@make_replacement_order');
    Route::post('users/make_replacement_order_bp', 'Api\User\OrderBpController@make_replacement_order');
    Route::post('users/cancel_order', 'Api\User\OrderBpTapController@cancel_order');

    Route::get('users/buy_again_products', 'Api\User\ProductController@buy_again_products');

    Route::get('users/my_orders', 'Api\User\OrderBpTapController@my_orders_opmtized');
    // Route::get('users/my_orders_opmtized', 'Api\User\OrderBpTapController@my_orders_opmtized');
    Route::get('users/order_detail', 'Api\User\OrderController@order_detail');
    Route::get('users/order_detail_bp', 'Api\User\OrderBpTapController@order_detail');
    Route::post('users/reorder', 'Api\User\OrderController@reorder');
    Route::post('users/reorder_bp', 'Api\User\OrderBpController@reorder');

    Route::post('users/rate_order', 'Api\User\OrderBpTapController@rate_order');
    Route::post('users/rate_product', 'Api\User\OrderController@rate_product');

    Route::get('users/view_all_recent_orders', 'Api\User\ProductController@view_all_recent_orders');
    Route::get('users/frequently_bought_together', 'Api\User\ProductController@frequently_bought_together');

    Route::post('users/notification_switch', 'Api\User\SettingController@notification_switch');
    Route::get('users/get_notifications', 'Api\User\SettingController@get_notifications');

    Route::post('users/change_mobile_number', 'Api\User\SettingController@change_mobile_number');
    Route::post('users/verify_mobile_number', 'Api\User\SettingController@verify_mobile_number');

    Route::post('users/add_wallet_balance', 'Api\User\UserController@add_wallet_balance');
    Route::post('users/add_wallet_balance_tap', 'Api\User\UserController@add_wallet_balance_tap');
    Route::get('users/get_wallet', 'Api\User\UserController@get_wallet');
    Route::get('users/get_referral_info', 'Api\User\UserController@get_referral_info');

    Route::post('users/send_feedback', 'Api\User\UserController@send_feedback');
    Route::post('users/customer_support', 'Api\User\SupportController@customer_support');

    Route::get('users/get_all_tickets', 'Api\User\SupportController@get_all_tickets');
    Route::get('users/get_technical_support_ticket_detail', 'Api\User\SupportController@get_technical_support_ticket_detail');
    Route::post('users/send_message', 'Api\User\SupportController@send_message');

    Route::get('users/get_customer_support_ticket_detail', 'Api\User\SupportController@get_customer_support_ticket_detail');
    Route::post('users/send_response', 'Api\User\SupportController@send_response');

    Route::post('users/mark_recipe_favorite', 'Api\User\RecipeController@mark_recipe_favorite');
    Route::get('users/view_all_favorite_recipes', 'Api\User\RecipeController@view_all_favorite_recipes');

    // New API calls added inside auth permission required
    Route::get('users/home_personalized', 'Api\User\HomeController@home_personalized');
    Route::get('users/home_personalized_bp', 'Api\User\HomeBpController@home_personalized');
    Route::get('users/ivp_switching_personalized', 'Api\User\HomeBpController@ivp_switching_personalized');
    Route::get('users/recipes_home_static_personalized', 'Api\User\RecipeController@recipes_home_static_personalized');
        
    Route::post('users/suggest_new_product', 'Api\User\ProductController@suggest_new_product');
    Route::get('users/view_all_suggestions', 'Api\User\ProductController@view_all_suggestions');

    // Set user language preferences
    Route::post('users/set_lang_preference', 'Api\User\UserController@set_lang_preference');

    // Saved carts
    Route::get('users/saved_cards', 'Api\User\SettingController@saved_cards');
    Route::post('users/delete_saved_card', 'Api\User\SettingController@delete_saved_card');

    // buy it for me
    Route::post('users/buy_it_for_me', 'Api\User\HomeBpController@buy_it_for_me');
    Route::get('users/buy_it_for_me_cart', 'Api\User\HomeBpController@buy_it_for_me_cart');
    Route::post('users/read_buy_it_for_me_request_status', 'Api\User\HomeBpController@read_buy_it_for_me_request_status');
    Route::post('users/buy_it_for_me_received_requests', 'Api\User\HomeBpController@buy_it_for_me_received_requests');
    Route::post('users/buy_it_for_me_sent_requests', 'Api\User\HomeBpController@buy_it_for_me_sent_requests');

    //filter user orders
    Route::post('users/filter_orders', 'Api\User\OrderBpTapController@filter_orders');
    Route::post('users/filter_order_products', 'Api\User\OrderBpTapController@filter_order_products');
    
    // scratch_cards
    Route::post('users/scratch_cards', 'Api\User\OrderBpTapController@get_scratch_cards');
    Route::post('users/scratch_cards/scratched', 'Api\User\OrderBpTapController@scratch_card_scratched');

});

// upload home_static
Route::post('users/upload_home_static', 'Api\User\HomeStaticController@upload_home_static');

// get coupon
Route::get('users/get_coupons', 'Api\User\OrderBpTapController@get_coupons');
Route::post('users/check_delivery_area', 'Api\User\HomeController@check_delivery_area');

// Route::get('users/home', 'Api\User\HomeController@home');
Route::get('users/home_static_plus', 'Api\User\HomeBpController@home_static_plus');
Route::get('users/home_static_mall', 'Api\User\HomeBpController@home_static_mall');
Route::get('users/home_static_instant', 'Api\User\HomeBpController@home_static_instant');

Route::get('users/home_static_store', 'Api\User\HomeBpController@home_static_store');
Route::get('users/home1', 'Api\User\HomeController@home1');
Route::get('users/home2', 'Api\User\HomeController@home2');
Route::get('users/home_static', 'Api\User\HomeBpController@home_static');
Route::get('users/home_static_1', 'Api\User\HomeBpController@home_static_1');
Route::get('users/home_static_2', 'Api\User\HomeBpController@home_static_2');
Route::get('users/home_static_3', 'Api\User\HomeBpController@home_static_3');
Route::get('users/home_personalized_1', 'Api\User\HomeBpController@home_personalized_1');
Route::get('users/home_personalized_2', 'Api\User\HomeBpController@home_personalized_2');
Route::get('users/home_personalized_3', 'Api\User\HomeBpController@home_personalized_3');
Route::get('users/home_reels', 'Api\User\HomeBpController@home_reels');
Route::get('users/view_all_products', 'Api\User\ProductController@view_all_products');
Route::get('users/view_all_banner_products', 'Api\User\ProductController@view_all_banner_products');

Route::get('users/get_all_categories', 'Api\User\CategoryBrandController@get_all_categories');
Route::get('users/get_all_brands', 'Api\User\CategoryBrandController@get_all_brands');
Route::get('users/get_brand_categories', 'Api\User\CategoryBrandController@get_brand_categories');

Route::post('users/upload_support_image', 'Api\User\SupportController@upload_support_image');

Route::get('users/get_classified_products', 'Api\User\ProductController@get_classified_products');
Route::get('users/view_all_classifications', 'Api\User\HomeController@view_all_classifications');
Route::get('users/view_all_fruitsNveg', 'Api\User\CategoryBrandController@view_all_fruitsNveg');
Route::get('users/get_two_step_tags', 'Api\User\SettingController@get_two_step_tags');

Route::get('users/recipes_home', 'Api\User\RecipeController@recipes_home');
Route::get('users/recipes_categories', 'Api\User\RecipeController@recipes_categories');
Route::get('users/view_all_recipes', 'Api\User\RecipeController@view_all_recipes');
Route::get('users/recipe_detail', 'Api\User\RecipeController@recipe_detail');
Route::get('users/view_all_featured_recipes', 'Api\User\RecipeController@view_all_featured_recipes');
Route::get('users/view_home_recipes', 'Api\User\RecipeController@view_home_recipes');
Route::get('users/recipes_home_static_1', 'Api\User\RecipeController@recipes_home_static_1');

/* ===========================Driver API ====================================== */
Route::post('drivers/register', 'Api\Driver\AuthController@register');
Route::post('drivers/verify_otp', 'Api\Driver\AuthController@verify_otp');
Route::post('drivers/resend_otp', 'Api\Driver\AuthController@resend_otp');

Route::post('drivers/upload_image', 'Api\Driver\ProfileController@upload_image');

Route::group(['middleware' => ['driver_api_auth']], function () {
    Route::post('drivers/update_location', 'Api\Driver\HomeController@update_location');

    Route::get('drivers/profile', 'Api\Driver\ProfileController@get_profile');
    Route::post('drivers/profile', 'Api\Driver\ProfileController@update_profile');

    Route::post('drivers/logout', 'Api\Driver\AuthController@logout');

    Route::post('drivers/home', 'Api\Driver\HomeController@home');
    Route::post('drivers/orders', 'Api\Driver\OrderController@orders');
    Route::get('drivers/order_detail', 'Api\Driver\OrderController@order_detail');

    Route::post('drivers/update_order_status', 'Api\Driver\OrderController@update_order_status');

    //Collector Driver
    Route::post('drivers/collector_home', 'Api\Driver\HomeController@collector_home');
    Route::post('drivers/collector_orders', 'Api\Driver\OrderController@collector_orders');
    Route::get('drivers/collector_time_slot_order_detail', 'Api\Driver\OrderController@collector_time_slot_order_detail');
    Route::post('drivers/collector_stores', 'Api\Driver\OrderController@collector_stores');
    Route::post('drivers/mark_store_order_out_for_delivery', 'Api\Driver\OrderController@mark_store_order_out_for_delivery');
    Route::post('drivers/mark_time_slot_order_out_for_delivery', 'Api\Driver\OrderController@mark_time_slot_order_out_for_delivery');
    Route::post('drivers/mark_store_order_delivered', 'Api\Driver\OrderController@mark_store_order_delivered');
});

/* ===========================Instant Driver API ====================================== */

Route::group(['middleware' => ['driver_api_auth']], function () {
    
    Route::post('drivers/instant_orders', 'Api\Driver\OrderController@instant_orders');
    Route::post('drivers/update_instant_order', 'Api\Driver\OrderController@update_instant_orders');
    Route::get('drivers/instant_order_detail', 'Api\Driver\OrderController@instant_order_detail');
});

/* =========================== Storekeeper API ====================================== */
Route::post('storekeepers/register', 'Api\Storekeeper\AuthController@register');
Route::post('storekeepers/verify_otp', 'Api\Storekeeper\AuthController@verify_otp');
Route::post('storekeepers/resend_otp', 'Api\Storekeeper\AuthController@resend_otp');

Route::group(['middleware' => ['storekeeper_api_auth']], function () {
        
    Route::post('storekeepers/update_location', 'Api\Storekeeper\HomeController@update_location');

    Route::get('storekeepers/profile', 'Api\Storekeeper\ProfileController@get_profile');
    Route::post('storekeepers/profile', 'Api\Storekeeper\ProfileController@update_profile');

    Route::get('storekeepers/logout', 'Api\Storekeeper\AuthController@logout');

    Route::get('storekeepers/orders', 'Api\Storekeeper\OrderController@orders');
    Route::get('storekeepers/order_detail', 'Api\Storekeeper\OrderController@order_detail');
    Route::post('storekeepers/update_order_item_status', 'Api\Storekeeper\OrderController@update_order_item_status');
    Route::post('storekeepers/suggest_replacement_items', 'Api\Storekeeper\OrderController@suggest_replacement_items');
    Route::post('storekeepers/suggest_order_replacement_items', 'Api\Storekeeper\OrderController@suggest_order_replacement_items');

    Route::get('storekeepers/get_header_inner', 'Api\Storekeeper\OrderController@get_header');

    Route::get('storekeepers/sub_categories', 'Api\Storekeeper\HomeController@sub_categories');
});

/*====================== Start :: User Whatsapp API ===========================================*/

Route::group(['middleware' => ['whatsapp_api_auth']], function () {

    Route::post('whatsapp/make_order', 'Api\WhatsApp\WhatsAppController@make_order');
    Route::post('whatsapp/address_list', 'Api\WhatsApp\WhatsAppController@address_list');
    Route::post('whatsapp/order_status', 'Api\WhatsApp\WhatsAppController@order_status');
    Route::get('whatsapp/delivery_slots', 'Api\WhatsApp\WhatsAppController@delivery_slots');
    Route::get('whatsapp/get_all_categories', 'Api\WhatsApp\WhatsAppController@get_all_categories');
    Route::get('whatsapp/get_all_brands', 'Api\WhatsApp\WhatsAppController@get_all_brands');
    Route::post('whatsapp/buy_it_for_me_cart', 'Api\WhatsApp\WhatsAppController@buy_it_for_me_cart');
});

/*====================== End :: User Whatsapp API =====================================*/

Route::post('paythem/get_ProductList', 'Api\PayThem\PayThemController@get_ProductList');

Route::get('users/store_barcode', 'Api\User\SettingController@storeBarcode');
