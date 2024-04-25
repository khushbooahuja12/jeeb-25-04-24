<input type="checkbox" id="nav-toggle">
<div class="sidebar">
        <div class="sidebar-brand">
                <h3><span class="lab la-accusoft"></span><span>JEEB</span></h3>
        </div>
        <div class="sidebar-brand">
                <h3><span class="lab la-accusoft"></span><span>Accounts Panel</span></h3>
        </div>
        <div class="sidebar-menu">
                <ul>
                        <li>
                                <a href="<?= url('account/invoice'); ?>" ><span class="fa fa-home mr-3"></span>
                                <span>Invoices</span>
                                </a>
                        </li>
                        <li>
                                <a href="<?= url('account/orders'); ?>" ><span class="fa fa-home mr-3"></span>
                                <span>Orders</span>
                                </a>
                        </li>
                        <li>
                                <a href="<?= url('account/wallets'); ?>" ><span class="fa fa-home mr-3"></span>
                                <span>Wallet Money</span>
                                </a>
                        </li>
                        <li>
                                <a href="<?= url('account/logout'); ?>" onclick="return confirm('Are you sure you want to logout ?')"><span class="fa fa-home mr-3"></span>
                                <span>Logout</span>
                                </a>
                        
                        </li>
                </ul>
        </div>
</div>