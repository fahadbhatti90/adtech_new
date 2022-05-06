<?php

namespace App\Models\ams;

use Illuminate\Database\Eloquent\Model;

class profileReportStatus extends Model
{
    protected $table = "tbl_ams_profile_report_status";
    protected $fillable = [
        'batchId',
        'profileId',
        'adType',
        'reportType',
        'status',
        'error_description'
    ];
    public $timestamps = true;
}
