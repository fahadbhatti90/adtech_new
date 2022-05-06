<?php

namespace App\Models\ams;

use App\Models\ams\Report\ReportIdModel;
use App\Models\ams\Token\AuthToken;
use App\Models\AMSApiModel;
use Illuminate\Database\Eloquent\Model;

class ProfileModel extends Model
{
    protected $table = "tbl_ams_profiles";
    protected $fillable = [
        'fkConfigId',
        'profileId',
        'countryCode',
        'currencyCode',
        'timezone',
        'marketplaceStringId',
        'entityId',
        'type',
        'name',
        'adGroupSpSixtyDays',
        'aSINsSixtyDays',
        'campaignSpSixtyDays',
        'keywordSbSixtyDays',
        'keywordSpSixtyDays',
        'productAdsSixtyDays',
        'productTargetingSixtyDays',
        'creationDate',
        'SponsoredBrandCampaignsSixtyDays',
        'SponsoredDisplayCampaignsSixtyDays',
        'SponsoredDisplayAdgroupSixtyDays',
        'SponsoredDisplayProductAdsSixtyDays',
        'isActive',
        'isSandboxProfile',
        'SponsoredBrandAdgroupSixtyDays',
        'SponsoredBrandTargetingSixtyDays',
        'adGroupSdSixtyDays',
        'SponsoredDisplayTargetSixtyDays'
    ];
    public $timestamps = false;

    /**
     * @param $reportType
     * @param $reportDateSingleDay
     * @return ProfileModel[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getProfileListForLink($reportType, $reportDateSingleDay)
    {
	    $callback = function($query) use  ($reportType, $reportDateSingleDay){
		    $query->where('reportType', '=', $reportType);
		    $query->where('reportDate', '=', $reportDateSingleDay);
		    $query->where('isDone', '=', 0);
	    };
	    return ProfileModel::whereHas('getReportId', $callback)->with(['getTokenDetail','getReportId' => $callback])->get();
    }

    public function getTokenDetail()
    {
        return $this->hasOne(AuthToken::class, 'fkConfigId', 'fkConfigId');
    }

    public function getReportId()
    {
        return $this->hasOne(ReportIdModel::class, 'profileID', 'profileId');
    }
}
