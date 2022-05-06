<?php

namespace App\Models\BidMultiplierModels;

use Illuminate\Database\Eloquent\Model;

class BidMulitplierListActivityTrackerModel extends Model
{
    
    public $table = 'tbl_ams_bid_multiplier_list_activity_tracker';
    public $timestamps = false;
    
    protected $fillable = [
        "fkMultiplierListId",
        "profileId",
        "campaignId",
        "bid",
        "isActive",
        "userID",
        'startDate',
        'endDate',
        "updatedAt"
    ];

    public static function getTableName() : string
    {
        return (new self())->getTable();
    }

}
