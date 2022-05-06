<?php

namespace App\Models\Tacos;

use Illuminate\Database\Eloquent\Model;

class TacosListActivityTrackerModel extends Model
{
    public $table = 'tbl_tacos_list_activity_tracker';
    public $timestamps = false;
    
    protected $fillable = [
        'fkTacosId',
        "profileId",
        "campaignId",
        "metric",
        'tacos',
        'min',
        "max",
        "userID",
        "isActive",
        "updatedAt"
    ];

    public static function getTableName() : string
    {
        return (new self())->getTable();
    }

}
