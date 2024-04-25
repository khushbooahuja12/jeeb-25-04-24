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

$recipeActionArr = ['create', 'edit'];

?>
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                <li class="menu-title">Menu</li>
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
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
