<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\View;
use App\Model\Admin;
use App\Model\Store;
use Auth;
class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            \URL::forceScheme('https');
        }

        Builder::defaultStringLength(191);

        Paginator::useBootstrap();

        View::composer(['admin.layouts.dashboard_layout_for_vendor_panel','admin.vendor_panel.dashboard'], function($view) {
            $admin = Auth::guard('admin')->user();
            $view->with(compact('admin'));
        });

        //checking admin role permissions for sidebar
        View::composer(['admin.layouts.sidebar_items','admin.base_products.requested_products_store'], function($view) {
            $admin = Admin::find(Auth::guard('admin')->user()->id);
            $permissions = array();
            foreach ($admin->getRole()->get() as $key => $value) {
                $permissions = array_merge($permissions,$value->permissions()->pluck('permissions.slug')->toArray());
                $permissions = array_unique($permissions);
            }

            $view->with(compact('permissions'));

        });


    }

}
