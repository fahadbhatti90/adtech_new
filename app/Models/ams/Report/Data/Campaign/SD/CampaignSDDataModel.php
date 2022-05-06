<?php

namespace App\Models\ams\Report\Data\Campaign\SD;

use Illuminate\Database\Eloquent\Model;

class CampaignSDDataModel extends Model
{
    protected $table = "tbl_ams_campaigns_reports_downloaded_sd";
    protected $primaryKey = 'id';
    protected $fillable = [
        'fkBatchId',
        'fkAccountId',
        'fkReportsDownloadLinksId',
        'fkProfileId',
        'fkConfigId',
        'campaignName',
        'campaignId',
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
        'creationDate'
    ];
    public $timestamps = false;
}
