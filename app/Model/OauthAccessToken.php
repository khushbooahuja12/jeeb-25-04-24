<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OauthAccessToken extends Model
{

    protected $table = 'oauth_access_tokens';
    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'client_id',
        'name',
        'scopes',
        'revoked',
        'expires_at',
        'device_type',
        'device_token',
        'latitude',
        'longitude',
        'nearest_store',
        'ivp',
        'expected_eta'
    ];
}
