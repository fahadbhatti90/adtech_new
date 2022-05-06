<?php

namespace App\Models\BidMultiplierModels;

use App\Models\ams\Token\AuthToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cron extends Model
{
    /**
     * @var string
     */
    private static $tableStatic = "tbl_ams_bid_multiplier_cron";
    public $table = "tbl_ams_bid_multiplier_cron";
    public $timestamps = false;
    protected $fillable = [
        "fkMultiplierId",
        "profileId",
        "fkConfigId",
        "campaignId",
        "type",
        "sponsoredType",
        "isActive",
        "runStatus",
        "checkRule",
        "ruleResult",
        "isData",
        "currentRunTime",
        "lastRunTime"
    ];

    /**
     * This function is used to update all rule status update
     */
    public static function updateRecord()
    {
        DB::table(Cron::$tableStatic)
            ->update(['isActive' => 0]);
    }

    public function getTokenDetail()
    {
        return $this->hasOne(AuthToken::class, 'fkConfigId', 'fkConfigId');
    }
}
