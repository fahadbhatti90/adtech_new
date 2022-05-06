<?php

namespace App\Models\BidMultiplierModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BidMultiplierTracker extends Model
{
    protected $table = 'tbl_ams_bid_multiplier_tracker';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['fkMultiplierId', 'fkConfigId', 'profileId', 'campaignId', 'keywordId', 'bidOptimizationValue', 'oldBid', 'bid', 'code', 'creationDate'];


    public static function getTableName() : string
    {
        return (new self())->getTable();
    }

    /**
     * @param $dataArray
     */
    static public function insertRecord($dataArray)
    {
        DB::beginTransaction();
        try {
            DB::table('tbl_ams_bid_multiplier_tracker')->insert($dataArray);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}
