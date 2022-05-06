<?php

namespace App\Models\ScModels;

use App\Models\AccountModels\AccountModel;
use Illuminate\Database\Eloquent\Model;

class scSellers extends Model
{
    protected $tb_ams_api = 'tbl_sc_config';
    public $table = "tbl_sc_config";
    protected $primaryKey = 'mws_config_id';
    public function accounts()
    {
        return $this->setConnection(\getDbAndConnectionName("c1"))->belongsTo(AccountModel::class, 'mws_config_id',"fkId")->where ("fkAccountType",2);
    }//end function
}
