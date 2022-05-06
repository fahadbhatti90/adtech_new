<?php

namespace App\Models\ams\Report\Link;

use Illuminate\Database\Eloquent\Model;

class AmsFailedReportsLinks extends Model
{
    protected $table = "tbl_ams_failed_reports_links";
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
        'expiration',
        'reportType'
    ];
    public $timestamps = false;
}
