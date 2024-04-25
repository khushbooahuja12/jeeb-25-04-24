<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class ApiAuth {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if ($request->hasHeader('Authorization')) {
            $lang = $request->header('lang');
            $access_token = $request->header('Authorization');
        } else {
            return response()->json([
                        'error' => true,
                        'status_code' => 301,
                        'message' => "Access token empty",
                        'result' => (object) []
            ]);
        }
        $api_token_exist = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->orderBy('created_at', 'desc')
                ->exists();
        if ($api_token_exist != 1) {
            return response()->json([
                        'error' => true,
                        'status_code' => 301,
                        'message' => $lang=='ar' ? "حسابك مسجل حاليا على جهاز آخر. الرجاء تسجيل الخروج من الجهاز الآخر والمحاولة مرة أخرى" : "Your account is currently logged onto another device. Please log out from the other device and try again",
                        'result' => (object) []
            ]);
        } else {
//            $currdate = \Carbon\Carbon::now();
//            $access_token_data = DB::table('oauth_access_tokens')
//                    ->where('id', "$access_token")
//                    ->orderBy('created_at', 'desc')
//                    ->first();
//
//            if ($currdate > $access_token_data->expires_at) {
//                return response()->json([
//                            'error' => true,
//                            'status_code' => 301,
//                            'message' => "Access token expired",
//                            'result' => (object) []
//                ]);
//            }
        }
        return $next($request);
    }

}
