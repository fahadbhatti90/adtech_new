<?php

namespace App\Models\Tacos;

use App\Models\AMSApiModel;
use Illuminate\Database\Eloquent\Model;

class TargetList extends Model
{
    protected $table = 'tbl_ams_tacos_target_list';
    protected $primaryKey = 'id';
    protected $fillable = [
        'fkId',
        'fkTacosId',
        'fkConfigId',
        'profileId',
        'reportType',
        'campaignId',
        'adGroupId',
        'targetId',
        'state',
        'bid',
        'createdAt',
        'updatedAt'
    ];
    public $timestamps = false;

    public function getConfigId()
    {
        return $this->belongsTo(AMSApiModel::class, 'fkConfigId', 'id');
    }
}
