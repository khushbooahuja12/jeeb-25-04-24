<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuth {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!Auth::guard('admin')->check()) {
            return redirect('admin/login');
        }
        if (Auth::guard('admin')->check()) {
            $url_request = $request->url(); // without query string
            // if (Auth::guard('admin')->user()->email == 'reportadmin@jeeb.tech' 
            //     && !str_contains($url_request, 'admin/report')
            //     && !str_contains($url_request, 'admin/users')
            //     ) {
            //         return redirect('admin/report');
            // }
            // if (Auth::guard('admin')->user()->email == 'affiliateadmin@jeeb.tech' && !str_contains($url_request, 'affiliates')) {
            //     return redirect('admin/affiliates');
            // }
            // if (Auth::guard('admin')->user()->email == 'recipeadmin@jeeb.tech' && !str_contains($url_request, 'recipe') 
            //     && !str_contains($url_request, 'delivery-area')
            //     && !str_contains($url_request, 'update_delivery_area')
            //     ) {
            //     return redirect('admin/recipes');
            // }
           
            // if (Auth::guard('admin')->user()->email == 'fleetadmin@jeeb.tech' && !str_contains($url_request, 'fleet')) {
            //     return redirect('admin/fleet');
            // }
            // if (Auth::guard('admin')->user()->email == 'executiveadmin@jeeb.tech' && 
            //     !(
            //         str_contains($url_request, 'custom_notifications') || 
            //         str_contains($url_request, 'sheduled_notifications') || 
            //         str_contains($url_request, 'delivery-area')
            //     )
            //     ) {
            //     return redirect('admin/custom_notifications');
            // }
        }
        return $next($request);
    }

}
