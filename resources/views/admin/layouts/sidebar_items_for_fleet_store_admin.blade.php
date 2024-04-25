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
                    <a href="<?= url('admin/fleet/store-orders') ?>"
                        class="waves-effect <?= $controller == 'FleetController' && ($action == 'store_orders' || $action == 'store_order_detail') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Orders</span>
                    </a>
                </li>

                <li>
                    <a href="javascript:void(0);"
                        class="waves-effect <?= $controller == 'FleetController' && ($action == 'stores' || $action == 'store_location') ? 'mm-active' : '' ?>">
                        <i class="icon-accelerator"></i> <span>Stores</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= $controller == 'FleetController' && ($action == 'stores' || $action == 'store_location') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/fleet/stores') ?>">List</a>
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
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
