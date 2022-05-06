<?php

namespace App\Models\ams\Report\Link\Target\SP;

use Illuminate\Database\Eloquent\Model;

class TargetAdsSPModel extends Model
{
    protected $table = "tbl_ams_targets_reports_download_links";
    protected $primaryKey = 'id';
    protected $fillable = [
        'fkBatchId',
        'fkAccountId',
        'profileID',
        'fkConfigId',
        'reportId',
        'status',
        'statusDetails',
        'location',
        'fileSize',
        'reportDate',
        'creationDate',
        'isDone',
        'expiration'
    ];
    public $timestamps = false;
}
