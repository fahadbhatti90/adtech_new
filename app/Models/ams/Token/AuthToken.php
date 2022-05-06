<?php

namespace App\Models\ams\Token;

use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{
    public $table = "tbl_ams_authtoken";
    public $timestamps = false;
    protected $fillable = [
        "fkConfigId",
        'client_id',
        'access_token',
        "refresh_token",
        "token_type",
        "expires_in",
        "creationDate",
        "updationDate"
    ];
}
