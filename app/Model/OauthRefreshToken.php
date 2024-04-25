<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OauthRefreshToken extends Model {

    protected $table = 'oauth_refresh_tokens';
    protected $fillable = [
        'id',
        'access_token_id',
        'revoked',
        'expires_at'
    ];
    public $timestamps = false;

}
