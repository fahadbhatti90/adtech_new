<?php

namespace App\Models\Tacos;

use App\Models\ams\Token\AuthToken;
use App\models\BiddingRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TacosCronModel extends Model
{
    /**
     * @var string
     */
    private static $tableStatic = "tbl_tacos_cron";
    public $table = "tbl_tacos_cron";
    public $timestamps = false;
    protected $fillable = [
        "fkTacosId",
        "type",
        "profileId",
        "fkConfigId",
        "sponsoredType",
        "campaignId",
        "lookBackPeriodDays",
        "frequency",
        "runStatus",
        "checkRule",
        "ruleResult",
        "isActive",
        "isData",
        "currentRunTime",
        "lastRunTime",
        "nextRunTime",
        "emailSent"
    ];

    /**
     * This function is used to update all rule status update
     */
    public static function updateRecord()
    {
        DB::table(TacosCronModel::$tableStatic)
            ->update(['isActive' => 0]);
    }

    public function getTokenDetail()
    {
        return $this->hasOne(AuthToken::class, 'fkConfigId', 'fkConfigId');
    }
}
