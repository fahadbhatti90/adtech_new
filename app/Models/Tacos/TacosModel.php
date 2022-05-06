<?php

namespace App\Models\Tacos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tacos\TacosCampaignModel;

class TacosModel extends Model
{
    protected $connection = 'mysql';
    public $table = "tbl_tacos_list";
    public static $tableName = "tbl_tacos_list";
    public $timestamps = false;
    protected $fillable = [
        "profileId",
        "campaignId",
        "metric",
        'tacos',
        'min',
        "max",
        "userID",
        "isActive",
        "createdAt",
        "updatedAt"
    ];

    public function __construct()
    {
        $this->table = getDbAndConnectionName("db1") . "." . $this->table;
        $this->connection = getDbAndConnectionName("c1");
    } //end constructor

    public static function getCompleteTableName()
    {
        return getDbAndConnectionName("db1") . "." . self::$tableName;
    }
    public function campaign()
    {
        return $this->belongsTo(TacosCampaignModel::class, 'campaignId', "campaignId");
    } //end function

    
    public function resolveRouteBinding($value)
    {
        \Log::info("Calue");
        \Log::info($value);
        return $this->where('id', $value)->first() ?? null;
    }//end function
}
