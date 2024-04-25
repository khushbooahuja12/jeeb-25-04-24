<?php
$controller = get_controller();
$action = get_action();

$storeId = getStoreId();

$staffActionArr = ['drivers', 'storekeepers', 'driver_detail', 'storekeeper_detail'];

$storekeeperActionArr = ['storekeepers', 'storekeeper_detail'];
$driverActionArr = ['drivers', 'driver_detail'];

$allOrdersArr = ['all_orders', 'detail', 'later_orders'];

$productActionArr = ['outofstock_products','outofstock_products','edit','show'];

?>
<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                <li class="menu-title">Menu</li>
                <li>
                    <a href="<?= url('store/' . $storeId . '/dashboard') ?>"
                        class="waves-effect <?= $controller == 'DashboardController' && $action == 'index' ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect
                    <?= $controller == 'StaffController' && in_array($action, $staffActionArr) ? 'mm-active' : '' ?>">
                        <i class="mdi mdi-pentagon"></i><span> Staffs</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'StaffController' && in_array($action, $storekeeperActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/storekeepers') ?>">Storekeepers</a>
                        </li>
                        <li
                            class="<?= $controller == 'StaffController' && in_array($action, $driverActionArr) ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/drivers') ?>">Drivers</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect <?= $controller == 'OrderController' && in_array($action, $allOrdersArr) ? 'mm-active' : '' ?>"><i
                            class="fa fa-percent"></i><span> Orders</span></a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'OrderController' && $action == 'active_orders' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/active-orders') ?>">Active Orders</a>
                        </li>
                        <li
                            class="<?= $controller == 'OrderController' && $action == 'later_orders' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/later-orders') ?>">Later Orders</a>
                        </li>
                        <li
                            class="<?= $controller == 'OrderController' && $action == 'all_orders' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/all-orders') ?>">All Orders</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect
                    <?= $controller == 'ProductController' && in_array($action, $productActionArr) ? 'mm-active' : '' ?>"><i
                            class="mdi mdi-pentagon"></i><span> Product Mgmt</span></a>
                    <ul class="submenu">
                        <li class="<?= $controller == 'ProductController' && $action == 'instock_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/instock_products') ?>">In stock products</a>
                        </li>
                        <li class="<?= $controller == 'ProductController' && $action == 'outofstock_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/outofstock_products') ?>">Out of stock products</a>
                        </li>
                        {{-- <li
                            class="<?= $controller == 'ProductController' && $action == 'null_itemcode_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/remaining-products') ?>">Products with no
                                itemcode/barcode</a>
                        </li>
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'new_products' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/new-products') ?>">New products</a>
                        </li> --}}
                        <li
                            class="<?= $controller == 'ProductController' && $action == 'stock_update' ? 'mm-active' : '' ?>">
                            <a href="<?= url('store/' . $storeId . '/stock-update') ?>">Stock Update</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
