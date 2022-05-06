<?php

namespace App\Models\BidMultiplierModels;

use App\Events\BidMultiplierListSaving;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tacos\TacosCampaignModel;
use Illuminate\Notifications\Notifiable;

class BidMultiplierListModel extends Model
{
    public $table = 'tbl_ams_bid_multiplier_list';
    public $timestamps = false;
    protected $fillable = [
        "profileId",
        "campaignId",
        "bid",
        "isActive",
        "userID",
        'startDate',
        'endDate',
        "createdAt",
        "updatedAt"
    ];

    public static function getTableName() : string
    {
        return (new self())->getTable();
    }

    public function campaign()
    {
        return $this->belongsTo(TacosCampaignModel::class, 'campaignId', "campaignId");
    } //end function
}
