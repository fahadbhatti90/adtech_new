<?php

use App\Models\AccountModels\AccountModel;
use App\Models\ams\Report\ReportIdModel;
use App\Models\Tacos\TacosCampaignModel;
use App\Models\Vissuals\VissualsProfile;
use App\Models\ams\Report\Link\AmsFailedReportsLinks;
use App\Models\amsAlerts\amsAlerts;
use Illuminate\Support\Facades\Config;

if (!function_exists('getAmsAllProfileList')) {
    /**
     *   getAmsAllProfileList
     * @return array
     * @uses in App\Http\Controller\DayParting\getProfileList
     * @uses in App\Http\Controller\BiddingController\getProfileList
     * @uses in App\Http\Controller\Vissuals\VissualsController\loadVissuals
     * @uses in App\Http\Controller\Vissuals\VissualsController\AsinPerformanceVisuals
     */
    function getAmsAllProfileList()
    {
        $profileIds = getAllAmsAccountProfileIds();
        $childBrands = VissualsProfile::with("accounts:id,fkId", "accounts.brand_alias:fkAccountId,overrideLabel")
            ->select("id", "profileId", "name", "type", "fkConfigId")->whereIn("id", $profileIds)->get();
        return $childBrands;
    }//end function
}//end if
/**
 *   getAllAmsAccountIds
 * @return array
 *
 */
if (!function_exists('getAllAmsAccountProfileIds')) {
    /**
     *   getAmsProfileList
     * @return array
     *
     */
    function getAllAmsAccountProfileIds()
    {
        $profileIds = AccountModel::select("id", "fkId")
            ->where("fkBrandId", getBrandId())
            ->where("fkAccountType", 1)
            ->get()
            ->map(function ($item, $value) {
                return $item->fkId;
            });
        return $profileIds;
    }//end function
}//end if
if (!function_exists('deleteAmsFailedLinkReportId')) {
    /**
     *   deleteAmsFailedLinkReportId
     * @param $reportType
     * @param $batchId
     * @return $res
     * @uses in App\Console\Commands\Ams\AdGroup\SB\getReportLinkCron
     */
    function deleteAmsFailedLinkReportId($reportType, $batchId)
    {
        $res = false;
        if (ReportIdModel::where('reportType', $reportType)->where('fkBatchId', $batchId)->count() > 0) {
            $res = ReportIdModel::where('reportType', $reportType)->where('fkBatchId', $batchId)->delete();
        }
        $res;
    }//end function
}//end if

if (!function_exists('checkNotificationAlertExists')) {
    /**
     *   deleteAmsFailedLinkReportId
     * @param $fkAccountId
     * @param $moduleType
     * @return $res
     * @uses in App\Console\Commands\Ams\AdGroup\SB\getReportLinkCron
     */
    function checkNotificationAlertExists($accountId, $moduleType)
    {
        switch ($moduleType) {
            case "day parting":
                $columnName = "dayPartingAlertsStatus";
                break;
            case "bidding rule":
                $columnName = "biddingRuleAlertsStatus";
                break;
            case "tacos":
                $columnName = "tacosAlertsStatus";
                break;
            case "Bid Multiplier":
                $columnName = "bidMultiplierAlertsStatus";
                break;
            case "Budget Multiplier":
                $columnName = "budgetMultiplierAlertsStatus";
                break;
            default:
                return false;
        }
        return $result = amsAlerts::where('fkAccountId', $accountId)->where($columnName, 1)->count();
    }//end function
    if (!function_exists('getNotificationProfileCampaignData')) {
        /**
         *   getNotificationProfileCampaignData
         * @param $campaignid
         * @return $res
         * @uses in App\Console\Commands\Tacos\getKeywordData;
         */
        function getNotificationProfileCampaignData($campaignid)
        {
            $profileCampaignData = TacosCampaignModel::select('name', 'fkProfileId')->where('campaignId', $campaignid)->first();
            return $profileCampaignData;
        }
    }
    if (!function_exists('getNotificationFkProfileId')) {
        /**
         *   getNotificationFkProfileId
         * @param $profileId
         * @return $res
         * @uses in App\Console\Commands\Tacos\getKeywordData;
         */
        function getNotificationFkProfileId($profileId)
        {
            $profileData = VissualsProfile::select('id')->where('profileId', $profileId)->first();
            return $profileData;
        }
    }
}//end if
if (!function_exists('getAmsCampaignProfileList')) {
    /**
     * getAmsCampaignProfileList
     * @param $isSandboxProfile
     * @return array
     */
    function getAmsCampaignProfileList($sandBoxValue)
    {
        if ($sandBoxValue == 0) {
            $isActive = 1;
            $isSandboxProfile = 0;
        } else {
            $isActive = 0;
            $isSandboxProfile = 1;
        }
        $profileIds = getAmsCampaignAccountList();
        $childBrands = VissualsProfile::with("getTokenDetail")
            ->select("id", "profileId", "name", "type", "fkConfigId", "isSandboxProfile")
            ->whereIn("id", $profileIds)
            ->where('isActive', $isActive)
            ->where('type', '<>', 'agency')
            ->where('isSandboxProfile', $isSandboxProfile)
            ->get();
        return $childBrands;
    }//end function
}//end if
/**
 *   getAllAmsAccountIds
 * @return array
 *
 */
if (!function_exists('getAmsCampaignAccountList')) {
    /**
     *   getAmsProfileList
     * @return array
     *
     */
    function getAmsCampaignAccountList()
    {
        $profileIds = AccountModel::select("id", "fkId")
            ->where("fkAccountType", 1)
            ->get()
            ->map(function ($item, $value) {
                return $item->fkId;
            });
        return $profileIds;
    }//end function
}//end if
if (!function_exists('getSandBoxApiUrl')) {
    /**
     * @param $isSandboxProfile
     * @return array
     */
    function getSandBoxApiUrl($isSandboxProfile)
    {
        switch ($isSandboxProfile) {
            case 0:
                $url = Config::get('constants.amsApiUrl');
                break;
            case 1:
                $url = Config::get('constants.testingAmsApiUrl');
                break;
            default:
                $url = Config::get('constants.testingAmsApiUrl');
        }
        return $url;
    }//end function
}//end if
?>