<?php

namespace App\Models\Tacos;

use App\Models\AMSApiModel;
use Illuminate\Database\Eloquent\Model;

class keywordList extends Model
{
    protected $table = 'tbl_ams_tacos_keywordid_list';
    protected $primaryKey = 'id';
    protected $fillable = [
        'fkId',
        'fkTacosId',
        'fkConfigId',
        'profileId',
        'reportType',
        'campaignId',
        'adGroupId',
        'keywordId',
        'keywordText',
        'matchType',
        'servingStatus',
        'state',
        'bid',
        'lastUpdatedDate',
        'createdAt',
        'updatedAt'
    ];
    public $timestamps = false;
    public static function getTableName() : string
    {
        return (new self())->getTable();
    }
    public function getConfigId()
    {
        return $this->belongsTo(AMSApiModel::class, 'fkConfigId', 'id');
    }
}
