<?php

namespace App\Models\amsAlerts;

use App\Models\AccountModels\AccountModel;
use Illuminate\Database\Eloquent\Model;

class amsAlerts extends Model
{
    public $table = "tbl_ams_alerts";
    protected $fillable = [
        'alertName',
        'fkAccountId',
        'fkProfileId',
        'dayPartingAlertsStatus',
        'biddingRuleAlertsStatus',
        'tacosAlertsStatus',
        'budgetMultiplierAlertsStatus',
        'bidMultiplierAlertsStatus',
        'addCC',
        'createdBy'
    ];
    public function accounts()
    {
        return $this->setConnection(\getDbAndConnectionName("c1"))->belongsTo(AccountModel::class, 'fkProfileId', "fkId")->where("fkAccountType", 1);
    }//end function
}
