<?php

namespace App\Models\BidMultiplierModels;

use Illuminate\Database\Eloquent\Model;

class KeywordBidValue extends Model
{
    /**
     * @var string
     */
    private static $tableStatic = "tbl_ams_bid_multiplier_keyword";
    public $table = "tbl_ams_bid_multiplier_keyword";
    public $timestamps = false;
    protected $fillable = [
        'fkMultiplierId',
        'fkConfigId',
        'profileId',
        'reportType',
        'campaignId',
        'adGroupId',
        'keywordId',
        'keywordText',
        'matchType',
        'state',
        'bid',
        'tempBid',
        'servingStatus',
        'creationDate',
        'lastUpdatedDate',
        'isEligible',
        'createdAt',
        'updatedAt'
    ];
    public static function getTableName() : string
    {
        return (new self())->getTable();
    }

}
