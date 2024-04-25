<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRolePermission {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next ,$permission = null,$guard="admin")
    {
        if (!can($permission)) {
             if ($request->ajax()) { 
                return response([
                    'error' => 'Forbidden',
                    'error_description' => 'Permission denied.',
                    'data' => [],
                ], 404);
            } else {

                Auth::guard('admin')->logout();
                return redirect('admin');
            }
        }
        return $next($request);
    }

}
