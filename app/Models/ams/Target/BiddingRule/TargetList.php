<?php

namespace App\Models\ams\Target\BiddingRule;

use App\Models\AMSApiModel;
use Illuminate\Database\Eloquent\Model;

class TargetList extends Model
{
    protected $table = 'tbl_ams_bidding_rule_target_list';
    protected $primaryKey = 'id';
    protected $fillable = [
        'fkId',
        'fkBiddingRuleId',
        'fkConfigId',
        'profileId',
        'reportType',
        'campaignId',
        'adGroupId',
        'targetId',
        'state',
        'bid',
        'createdAt',
        'updatedAt'
    ];
    public $timestamps = false;

    public function getConfigId()
    {
        return $this->belongsTo(AMSApiModel::class,'fkConfigId','id');
    }
}
