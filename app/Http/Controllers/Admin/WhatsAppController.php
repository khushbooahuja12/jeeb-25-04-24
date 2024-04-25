<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\WhatsAppUser;
use App\Model\OauthAccessToken;

class WhatsAppController extends CoreApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        
        $users = WhatsAppUser::join('oauth_access_tokens','oauth_access_tokens.user_id','=','whatsapp_users.id')
            ->select(
                'oauth_access_tokens.id as token',
                'oauth_access_tokens.expires_at as expires_at',
                'whatsapp_users.status',
                'whatsapp_users.created_at as joined',
                'whatsapp_users.name',
                'whatsapp_users.id',
            )
            ->where('oauth_access_tokens.user_type',4)
            ->get();
        return view('admin.whatsapp.users.index', [
            'users' => $users, 
        ]);
    }

    public function create(Request $request) {
        
        $create = WhatsAppUser::create([
            'name' => 'Whatsapp Client',
            'email' => 'whatsapp@jeeb.tech',
        ]);

        OauthAccessToken::create([
            'id' => \Str::random(80),
            'user_id' => $create->id,
            'user_type' => 4,
            'client_id' => 1,
            'name' => 'jeebToken',
            'scopes' => '',
            'revoked' => 0,
            'expires_at' => \Carbon\Carbon::now()->addYears(1)
        ]);

        return back()->withInput()->with('success', 'User Created Successfully');
    }

    public function delete($id) {

        $id = base64url_decode($id);
        $delete = WhatsAppUser::where('id',$id)->delete();

        if($delete){
            OauthAccessToken::where(['user_id' => $id, 'user_type' => 4])->delete();
        }

        return back()->withInput()->with('success', 'User Deleted Successfully');
    }

    public function order_payment(Request $request) {
        
        return view('admin.whatsapp.payment.index');
    }
}
