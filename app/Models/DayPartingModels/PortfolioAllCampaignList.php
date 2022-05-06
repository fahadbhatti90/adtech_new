<?php

namespace App\Models\DayPartingModels;

use App\Models\DayPartingModels\PfCampaignSchedule;
use App\Models\DayPartingModels\DayPartingCampaignScheduleIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortfolioAllCampaignList extends Model
{
    public $table = 'tbl_ams_campaign_list';
    public $timestamps = false;

    /**
     * This function is used to insert Campaign Details ( Sponsored (Display, Product, Brand))
     * @param $data
     */
    public static function insertCampaignList($data)
    {
        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                $existData = PortfolioAllCampaignList::select('campaignId', 'profileId', 'campaignType')->where([
                    'profileId' => $row['profileId'],
                    'campaignId' => $row['campaignId'],
                    'campaignType' => $row['campaignType'],
                ])->get();

                if ($existData->isEmpty()) {
                    try {
                        PortfolioAllCampaignList::insert($row);
                        Log::info('Portfolio Campaign list Id = ' . DB::getPDO()->lastInsertId() . ' Report ' . $row['campaignType'] . ' Inserted');
                    } catch (\Illuminate\Database\QueryException $ex) {
                        Log::error($ex->getMessage());
                    }
                } else {
                    PortfolioAllCampaignList::where([
                        'profileId' => $row['profileId'],
                        'campaignId' => $row['campaignId'],
                        'campaignType' => $row['campaignType']
                    ])->update($row);
                    Log::info('Already exist in DB. ProfileId :' . $row['profileId']);
                }
            }
        }, 3);

    }

    public static function insertDailyCampaigns($data)
    {
        Log::info('Start Insert data into DB.');
        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                try {
                    $id = DB::table('tbl_ams_day_parting_daily_campaigns')->insertGetId($row);
                    Log::info('Daily fetching Campaign Id = ' . $id . ' Report ' . $row['reportType'] . ' Inserted');
                } catch (\Illuminate\Database\QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }, 3);
        Log::info('End Insertion query.');
    }

    /**
     * This Function is used to update campaign List
     * @param $data
     */
    public static function updateCampaign($data)
    {
        foreach ($data as $row) {
            $existData = PortfolioAllCampaignList::where('campaignId', intval($row['campaignId']))->exists();

            if ($existData) {
                try {
                     PortfolioAllCampaignList::where('campaignId', intval($row['campaignId']))
                        ->update([
                            'state' => $row['state'],
                            'updatedAt' => $row['updatedAt']
                        ]);

                    Log::info('Portfolio Campaign Id = ' . $row['campaignId'] . ' List Updated');
                } catch (\Illuminate\Database\QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }
    }

    /**
     * The schedule that belong to the campaign.
     */
    public function schedules()
    {
        return $this->belongsToMany(PfCampaignSchedule::class, 'tbl_ams_day_parting_campaign_schedule_ids', 'fkCampaignId', 'fkScheduleId');
    }

    /**
     * This schedule is used to get only active campaigns
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function scheduleCampaigns()
    {
        return $this->belongsToMany(PfCampaignSchedule::class, 'tbl_ams_day_parting_campaign_schedule_ids', 'fkCampaignId', 'fkScheduleId')
            ->wherePivot('enablingPausingStatus', NULL);
    }


    public function portfolios()
    {
        return $this->belongsToMany(Portfolios::class, 'portfolioId', 'portfolioId');
    }

    /**
     * This function is used to store campaign list data
     *
     * @param $storeArray
     */
    public static function storeCampaignList($storeArray)
    {
        Log::info('Start Insert data into DB.');
        DB::transaction(function () use ($storeArray) {
            foreach ($storeArray as $row) {
                $existData =
                    DB::table('tbl_ams_campaign_list')->where([
                        'profileId' => $row['profileId'],
                        'campaignId' => $row['campaignId'],
                        'type' => $row['type']
                    ])->get();
                if ($existData->isEmpty()) {
                    try {
                        DB::table('tbl_ams_campaign_list')->insert($row);
                    } catch (\Illuminate\Database\QueryException $ex) {
                        Log::error($ex->getMessage());
                    }
                } else {
                    DB::table('tbl_ams_campaign_list')
                        ->where([
                            'profileId' => $row['profileId'],
                            'campaignId' => $row['campaignId'],
                            'type' => $row['type']
                        ])->update($row);
                    Log::info('Already exist in DB. ProfileId :' . $row['profileId']);
                }
            }
        }, 3);
        Log::info('End Insertion query.');
    }

    /**
     * This is function is used to get specific campaign list of profile with type
     *
     * @param $array
     * @return mixed
     */
    public static function getCampaignListOfSpecificProfile($array)
    {
        return DB::table('tbl_ams_campaign_list')
            ->where('fkProfileId', '=', $array['fkProfileId'])
            ->where('campaignType', '=', $array['sponsored_type'])
            ->where('state', '<>', 'archived')
            ->get();
    }
}
