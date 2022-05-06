<?php

namespace App\Models\BudgetRuleModels;

use Illuminate\Database\Eloquent\Model;
use App\Models\ams\campaign\CampaignList;

class BudgetRuleList extends Model
{
    public $table = 'tbl_ams_budget_rule_list';
    public $timestamps = false;
    protected $fillable = [
        'ruleName',
        'fkProfileId',
        'adType',
        'ruleType',
        'eventId',
        'eventName',
        'startDate',
        'endDate',
        'ruleName',
        'recurrence',
        'metric',
        'daysOfWeek',
        'comparisonOperator',
        'mon',
        'tue',
        'wed',
        'thu',
        'fri',
        'sat',
        'sun',
        'sun',
        'apiStatus',
        'ruleState',
        'ruleStatus',
        'ruleStatusDetails',
        'isActive',
        'apiMsg',
        'recurrence',
        'ruleId',
        'threshold',
        'raiseBudget',
        'userID',
        'createdAt',
        'updatedAt',
        'createdDate',
        'lastUpdatedDate',
    ];


    /**
     * Campaigns That belongs to Many budget Rule
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function budgetRuleCampaigns()
    {
        return $this->belongsToMany(
            CampaignList::class,
            'tbl_ams_budget_rule_campaign_ids',
            'fkRuleId',
            'fkCampaignId'
        )->wherePivot('shouldRemove', 0);
    }

    public function budgetRuleDeletedCampaigns()
    {
        return $this->belongsToMany(
            CampaignList::class,
            'tbl_ams_budget_rule_campaign_ids',
            'fkRuleId',
            'fkCampaignId'
        )->wherePivot('shouldRemove', 1);
    }
}
