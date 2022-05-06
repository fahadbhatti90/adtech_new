<?php

namespace App\Models\VcModels;

use App\Models\AccountModels\AccountModel;
use Illuminate\Database\Eloquent\Model;

class vcVendors extends Model
{
    public $table = "tbl_vc_vendors";
    protected $primaryKey = 'vendor_id';
    public function accounts()
    {
        return $this->setConnection(\getDbAndConnectionName("c1"))->belongsTo(AccountModel::class, 'vendor_id',"fkId")->where ("fkAccountType",3);
    }//end function
}
