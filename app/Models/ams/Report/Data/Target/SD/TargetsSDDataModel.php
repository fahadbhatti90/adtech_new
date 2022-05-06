<?php

namespace App\Models\ams\Report\Data\Target\SD;

use Illuminate\Database\Eloquent\Model;

class TargetsSDDataModel extends Model
{
    //
    protected $table = "tbl_ams_targets_reports_downloaded_data_sd";
    protected $primaryKey = 'id';
    protected $fillable = [
        'fkBatchId',
        'fkAccountId',
        'fkReportsDownloadLinksId',
        'fkProfileId',
        'fkConfigId',
        'campaignId',
        'adGroupId',
        'targetId',
        'targetingText',
        'impressions',
        'clicks',
        'cost',
        'currency',
        'attributedConversions1d',
        'attributedConversions7d',
        'attributedConversions14d',
        'attributedConversions30d',
        'attributedConversions1dSameSKU',
        'attributedConversions7dSameSKU',
        'attributedConversions14dSameSKU',
        'attributedConversions30dSameSKU',
        'attributedUnitsOrdered1d',
        'attributedUnitsOrdered7d',
        'attributedUnitsOrdered14d',
        'attributedUnitsOrdered30d',
        'attributedSales1d',
        'attributedSales7d',
        'attributedSales14d',
        'attributedSales30d',
        'attributedSales1dSameSKU',
        'attributedSales7dSameSKU',
        'attributedSales14dSameSKU',
        'attributedSales30dSameSKU',
        'reportDate',
        'creationDate',
        'campaignName',
        'adGroupName',
        'targetingExpression',
        'targetingType'
    ];
    public $timestamps = false;
}
