<?php

namespace App\Models\Tacos;

use Illuminate\Database\Eloquent\Model;

class TacosBidTracker extends Model
{
    /**
     * @var string
     */
    protected $table = 'tbl_ams_tacos_bid_tracker';
    protected $primaryKey = 'id';
    protected $fillable = ['fkTacosId','fkConfigId','profileId','adGroupId','campaignId','state','reportType','oldBid','bid','keywordId','targetId','code','creationDate'];
    public $timestamps = false;
    public static function getTableName() : string
    {
        return (new self())->getTable();
    }
}
