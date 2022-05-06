<?php

namespace App\Models\ams\campaign;

use Illuminate\Database\Eloquent\Model;

class CampaignList extends Model
{
    public $table = "tbl_ams_campaign_list";
    protected $fillable = [
        'fkProfileId',
        'fkConfigId',
        'profileId',
        'type',
        'campaignType',
        'name',
        'targetingType',
        'premiumBidAdjustment',
        'dailyBudget',
        'budget',
        'endDate',
        'bidOptimization',
        'bidMultiplier',
        'portfolioId',
        'campaignId',
        'strCampaignId',
        'budgetType',
        'startDate',
        'state',
        'servingStatus',
        'createdAt',
        'updatedAt',
        'pageType',
        'url',
        'brandName',
        'brandLogoAssetID',
        'headline',
        'shouldOptimizeAsins',
        'brandLogoUrl',
        'asins',
        'strategy',
        'predicate',
        'percentage',
    ];
    public $timestamps = false;

    public static function getTableName() : string
    {
        return (new self())->getTable();
    }
}
