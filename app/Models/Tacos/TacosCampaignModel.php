<?php

namespace App\Models\Tacos;

use Illuminate\Database\Eloquent\Model;

class TacosCampaignModel extends Model
{
    protected $connection = 'mysql';
    public $table = "tbl_ams_campaign_list";
    public static $tableName = "tbl_ams_campaign_list";

    public function __construct()
    {
        $this->table = getDbAndConnectionName("db1") . "." . $this->table;
        $this->connection = getDbAndConnectionName("c1");
    } //end constructor

    public static function getCompleteTableName()
    {
        return getDbAndConnectionName("db1") . "." . self::$tableName;
    }//end function


}//end class
