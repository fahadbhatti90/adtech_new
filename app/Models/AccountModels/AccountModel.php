<?php

namespace App\Models\AccountModels;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClientModels\ClientModel;
use App\Models\ClientModels\CampaignsIdModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AccountModels\AccountTypeModel;
use App\Models\Inventory\InventoryBrandsModel;
use App\Models\BuyBoxModels\BuyBoxFailStatusModel;
use App\Models\Vissuals\VissualsProfile;
use App\Models\MWSModel;
use App\Models\VCModel;

class AccountModel extends Model
{
    use SoftDeletes;
    public $table = "tbl_account";
    public static $tableName = "tbl_account";
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'fkBrandId',
        'marketPlaceID',
        'fkAccountType',
        'fkId',
        'creationDate',
    ];

    public static function getTableName(){
      return \getDbAndConnectionName("db1").".".self::$tableName;
    }//end funciton
    public static function getHirarchyBaseAsinFailStatus(){
        $dataAccountBased = [];
        $data = AccountModel::with("fail_status:failed_data,failed_reason,fkAccountId")->get();
        foreach ($data as $dataKey => $dataValue) {
            foreach ($dataValue->fail_status as $key => $value) {
                $dataAccountBased["null"]["null"][] = $value;
            }
        }
        return $dataAccountBased;
    }
    public static function getHirarchyBaseBuyBoxFailStatus(){
        $dataAccountBased = [];
        $data = AccountModel::with("buy_box_fail_status:failed_data,failed_reason,fkAccountId")->get();
        foreach ($data as $dataKey => $dataValue) {
            foreach ($dataValue->buy_box_fail_status as $key => $value) {
                $dataAccountBased["null"]["null"][] = $value;
            }
        }
        return $dataAccountBased;
    }
    public static function getAccountList(){
        return \DB::select('select * from tbl_account');
    }
    public function accountType()
    {
        return $this->belongsTo(AccountTypeModel::class, 'fkAccountType');
    }//end function

    public function client()
    {
        return $this->belongsTo(ClientModel::class, 'fkBrandId');
    }//end function
    public function brand()
    {
        return $this->belongsTo(ClientModel::class, 'fkBrandId');
    }//end function
    public function ams()
    {
        return $this->belongsTo('App\Models\AMSModel', 'fkId');
    }//end function
    public function mws()
    {
        return $this->belongsTo('App\Models\MWSModel', 'fkId')->where("fkAccountType",2);
    }//end function
    public function vc()
    {
        return $this->belongsTo('App\Models\VCModel', 'fkId')->where("fkAccountType",3);
    }//end function

    public function relationBatchId()
    {
        return $this->hasMany('App\Models\BatchIdModels\BatchIdModel', 'fkAccountId')->whereDate("created_at", date('Y-m-d'));
    }//end function
    public function brand_alias()
    {
        return $this->setConnection(\getDbAndConnectionName("c2"))->hasMany(InventoryBrandsModel::class,"fkAccountId");
    }//e
    public function campaigns()
    {
        return $this->setConnection(\getDbAndConnectionName("c2"))->hasMany(CampaignsIdModel::class,"fkProfileId","fkId");
    }//e
    public function amsChildBrandData()
    {
        return $this->setConnection(\getDbAndConnectionName("c1"))->hasMany(VissualsProfile::class,"id","fkId");
    }//end function
    public function mwsChildBrandData()
    {
        //return $this->belongsTo('App\Models\MWSModel', 'fkId')->where("fkAccountType",2);
        return $this->setConnection(\getDbAndConnectionName("c1"))->hasMany(MWSModel::class,"mws_config_id","fkId");
    }//end function
    public function vcChildBrandData()
    {
        //return $this->belongsTo('App\Models\VCModel', 'fkId')->where ("fkAccountType",3);
        return $this->setConnection(\getDbAndConnectionName("c1"))->hasMany(VCModel::class,"vendor_id","fkId");
    }//end function
    public function customManager()
    {
        return $this->belongsTo('App\Models\CustomUserModel', 'fkManagerId');
    }//end function
    public function notifications()
    {
        return $this->hasMany('App\Models\NotificationModel', 'fkAccountId');
    }//end function
    public function fail_status()
    {
        return $this->hasMany('App\Models\FailStatus',"fkAccountId","id")->where("isNew",1);
    }
    public function buy_box_fail_status()
    {
        return $this->hasMany(BuyBoxFailStatusModel::class,"fkAccountId","id")->where("isNew",1);
    }
}
