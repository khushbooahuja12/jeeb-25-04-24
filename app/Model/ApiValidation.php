<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Support\Facades\DB;

class ApiValidation extends Model {

    public function validate_user_mobile($phone) {
        $exists = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->exists();
        if ($exists) {
            return true;
        } else {
            return false;
        }
    }

}
