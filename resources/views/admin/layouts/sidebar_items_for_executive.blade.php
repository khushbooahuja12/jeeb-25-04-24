<?php
$controller = get_controller();
$action = get_action();

$bannerActionArr = ['index', 'create', 'edit', 'detail'];
$adActionArr = ['index', 'create', 'edit', 'detail'];

$couponOfferArr = ['index', 'create', 'edit', 'show', 'detail', 'recreate'];
$couponActionArr = ['index', 'create', 'edit', 'show', 'recreate'];
$offerActionArr = ['index', 'create', 'edit', 'detail'];

$brandActionArr = ['index', 'create', 'edit', 'show'];
$driverActionArr = ['index', 'create', 'edit', 'show'];
$storekeeperActionArr = ['index', 'create', 'edit', 'show'];
$vehicleActionArr = ['create_vehicle', 'edit_vehicle'];
$categoryActionArr = ['index', 'create', 'edit', 'show', 'create_sub_category', 'edit_sub_category'];
$categoryClassificationArr = ['index', 'create', 'edit', 'show', 'create_sub_category', 'edit_sub_category', 'classification_detail', 'create_classification', 'edit_classification'];
$classiArr = ['classifications', 'create_classification', 'edit_classification', 'create_sub_classification', 'edit_sub_classification', 'classification_detail'];
$allPrActionArr = ['index', 'create', 'edit', 'show', 'create_formula', 'edit_formula'];
$prActionArr = ['index', 'create', 'edit', 'show'];
$appHomepageArr = ['app_homepage', 'app_homepage_create', 'app_homepage_edit', 'app_homepage_detail', 'app_homepage_add_data', 'app_homepage_edit_data'];
$priceFormulaArr = ['price_formula', 'create_formula', 'edit_formula'];
$allOrdersArr = ['all_orders', 'detail', 'replacement_options'];

$userActionArr = ['index', 'show'];
$reviewFeedbackArr = ['reviews', 'feedback'];
$technicalSupportArr = ['technical_support', 'technical_support_detail'];
$customerSupportArr = ['customer_support', 'customer_support_detail'];
$newsArr = ['index', 'create', 'edit'];
$customNotificationArr = ['index', 'create', 'store', 'edit', 'show'];
$sheduledNotificationArr = ['sheduled_notifications', 'sheduled_notification_create', 'sheduled_notification_store', 'sheduled_notification_edit', 'sheduled_notification_resend', 'sheduled_notification_show'];

$recipeActionArr = ['create', 'edit'];

$fleetActionArr = ['index', 'show'];

?>
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                <li class="menu-title">Menu</li>
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect mm-active"><i
                            class="fa fa-users"></i><span> Delivery Mgmt</span></a>
                    <ul class="submenu">
                        <li class="<?= $action == 'delivery_area' ? 'mm-active' : '' ?>"><a
                                href="<?= url('admin/delivery-area') ?>">Delivery Area</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0)"
                        class="waves-effect mm-active"><i
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
                <li>
                    <a href="<?= url('admin/base_products/offers') ?>"
                        class="waves-effect <?= $controller == 'BaseproductController' && ($action == 'offers') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Daily / Weekly Offers</span>
                    </a>
                </li>
                <li
                    class="<?= $controller == 'ProductController' && in_array($action, $appHomepageArr) ? 'mm-active' : '' ?>">
                    <a href="<?= url('admin/app_homepage') ?>">
                        <i class="icon-accelerator"></i> <span> <span>Home Screen</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('admin/upload_image') ?>"
                        class="waves-effect <?= $controller == 'ImageController' && ($action == 'index' || $action == 'create' || $action == 'edit') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Images</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
