<div class="left side-menu">
    <div class="slimscroll-menu" id="remove-scroll">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                <li class="menu-title">Menu</li>
                <li class="mm-active">
                    <a href="javascript:void(0)"
                        class="waves-effect mm-active">
                        <i class="mdi mdi-alpha-b-circle"></i><span> Recipe Mgmt</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="<?= request()->is('admin/recipe_tags*') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipe_tags') ?>">Tags</a>
                        </li>
                        <li
                            class="<?= request()->is('admin/recipe_diets*') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipe_diets') ?>">Diets</a>
                        </li>
                        <li
                            class="<?= request()->is('admin/recipe_categories*') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipe_categories') ?>">Categories</a>
                        </li>
                        <li class="<?= request()->is('admin/recipes*') ? 'mm-active' : '' ?>">
                            <a href="<?= url('admin/recipes') ?>">Recipes</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
