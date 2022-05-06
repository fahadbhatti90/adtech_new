<?php

namespace App\Console\Commands\Ams\Campaign\SB;

use App\Models\ams\campaign\CampaignList;
use App\Models\ams\ProfileModel;
use App\Models\AMSApiModel;
use Artisan;
use DB;
use App\Models\AMSModel;
use App\Models\DayPartingModels\PortfolioAllCampaignList;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getCampaignList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getCampaignList:campaignSB {isSandBox?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to get Sponsored Brand Campaign List';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info("filePath:App\Console\Commands\Ams\Campaign\SB\getCampaignList. Start Cron.");
        Log::info($this->description);
        $isSandBox = $this->argument('isSandBox');
        $sandBoxValue = 0;
        if (isset($isSandBox) && $isSandBox != null) {
            $sandBoxValue = $isSandBox;
        }
        $AllProfileID = getAmsCampaignProfileList($sandBoxValue);
        if ($AllProfileID->isNotEmpty()) {
            $try = 0;
            foreach ($AllProfileID as $single) {
                if ($single->getTokenDetail == null) { // if is null
                    continue;
                }
                $clientId = $single->getTokenDetail->client_id;
                $fkConfigId = $single->getTokenDetail->fkConfigId;
                // Create a client with a base URI
                $isSandboxProfile = $single->isSandboxProfile;
                $apiUrl = getSandBoxApiUrl($isSandboxProfile);
                $url = $apiUrl . '/' . Config::get('constants.sbCampaignUrl');
                b:
                $singleAmsApiCreds = AMSApiModel::with('getTokenDetail')->where('id', $fkConfigId)->first();
                $accessToken = $singleAmsApiCreds->getTokenDetail->access_token;
                $profileId = $single->profileId;
                try {
                    $client = new Client();
                    $response = $client->request('GET', $url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $clientId,
                            'Amazon-Advertising-API-Scope' => $profileId
                        ],
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi'),
                    ]);
                    $responseBody = array();
                    $responseBody = json_decode($response->getBody()->getContents());
                    if (!empty($responseBody) && !is_null($responseBody)) {
                        // store report status
                        AMSModel::insertTrackRecord('SB Campaign List data found and profile:' . $profileId, 'success');
                        for ($i = 0; $i < count($responseBody); $i++) {
                            $existData = CampaignList::where('profileId', $profileId)
                                ->where('campaignId', $responseBody[$i]->campaignId)
                                ->get();
                            if ($existData->isNotEmpty()) {
                                $dbStore = array();
                                $dbStore['fkProfileId'] = $single->id;
                                $dbStore['fkConfigId'] = $fkConfigId;
                                $dbStore['profileId'] = $profileId;
                                $dbStore['type'] = 'SB';
                                $dbStore['campaignType'] = 'sponsoredBrands';
                                $dbStore['name'] = $responseBody[$i]->name;
                                $dbStore['targetingType'] = isset($responseBody[$i]->targetingType) ? $responseBody[$i]->targetingType : 'NA';
                                $dbStore['premiumBidAdjustment'] = isset($responseBody[$i]->premiumBidAdjustment) ? $responseBody[$i]->premiumBidAdjustment : 'NA';
                                $dbStore['dailyBudget'] = isset($responseBody[$i]->dailyBudget) ? $responseBody[$i]->dailyBudget : '00';
                                $dbStore['budget'] = isset($responseBody[$i]->budget) ? $responseBody[$i]->budget : 'NA';
                                $dbStore['endDate'] = isset($responseBody[$i]->endDate) ? $responseBody[$i]->endDate : 'NA';
                                $dbStore['bidOptimization'] = isset($responseBody[$i]->bidOptimization) ? $responseBody[$i]->bidOptimization : 'NA';
                                $dbStore['bidMultiplier'] = isset($responseBody[$i]->bidMultiplier) ? $responseBody[$i]->bidMultiplier : '00.00';
                                $dbStore['portfolioId'] = (isset($responseBody[$i]->portfolioId) ? $responseBody[$i]->portfolioId : 0);
                                $dbStore['campaignId'] = isset($responseBody[$i]->campaignId) ? $responseBody[$i]->campaignId : 0;
                                $dbStore['strCampaignId'] = isset($responseBody[$i]->campaignId) ? $responseBody[$i]->campaignId : 0;
                                $dbStore['budgetType'] = isset($responseBody[$i]->budgetType) ? $responseBody[$i]->budgetType : 'NA';
                                $dbStore['startDate'] = isset($responseBody[$i]->startDate) ? $responseBody[$i]->startDate : 'NA';
                                $dbStore['state'] = isset($responseBody[$i]->state) ? $responseBody[$i]->state : 'NA';
                                $dbStore['servingStatus'] = isset($responseBody[$i]->servingStatus) ? $responseBody[$i]->servingStatus : 'NA';
                                $dbStore['updatedAt'] = date('Y-m-d H:i:s');
                                $dbStore['pageType'] = 'NA';
                                $dbStore['url'] = 'NA';
                                if (isset($responseBody[$i]->landingPage)) {
                                    $dbStore['pageType'] = isset($responseBody[$i]->landingPage->pageType) ? $responseBody[$i]->landingPage->pageType : 'NA';
                                    $dbStore['url'] = isset($responseBody[$i]->landingPage->url) ? $responseBody[$i]->landingPage->url : 'NA';
                                }  // End check Landing Page
                                $dbStore['brandName'] = 'NA';
                                $dbStore['brandLogoAssetID'] = 'NA';
                                $dbStore['headline'] = 'NA';
                                $dbStore['shouldOptimizeAsins'] = 'NA';
                                $dbStore['brandLogoUrl'] = 'NA';
                                $dbStore['asins'] = 'NA';
                                if (isset($responseBody[$i]->creative)) {
                                    $dbStore['brandName'] = isset($responseBody[$i]->creative->brandName) ? $responseBody[$i]->creative->brandName : 'NA';
                                    $dbStore['brandLogoAssetID'] = isset($responseBody[$i]->creative->brandLogoAssetID) ? $responseBody[$i]->creative->brandLogoAssetID : 'NA';
                                    $dbStore['headline'] = isset($responseBody[$i]->creative->headline) ? $responseBody[$i]->creative->headline : 'NA';
                                    $dbStore['shouldOptimizeAsins'] = isset($responseBody[$i]->creative->shouldOptimizeAsins) ? $responseBody[$i]->creative->shouldOptimizeAsins : 'NA';
                                    $dbStore['brandLogoUrl'] = isset($responseBody[$i]->creative->brandLogoUrl) ? $responseBody[$i]->creative->brandLogoUrl : 'NA';
                                    $dbStore['asins'] = isset($responseBody[$i]->creative->asins) ? implode(',', $responseBody[$i]->creative->asins) : 'NA';
                                }
                                $dbStore['strategy'] = 'NA';
                                $dbStore['predicate'] = 'NA';
                                $dbStore['percentage'] = 0;
                                CampaignList::where('profileId', $profileId)
                                    ->where('campaignId', $responseBody[$i]->campaignId)
                                    ->update($dbStore);
                            } else {
                                $dbStore = new CampaignList();
                                $dbStore->fkProfileId = $single->id;
                                $dbStore->fkConfigId = $fkConfigId;
                                $dbStore->profileId = $profileId;
                                $dbStore->type = 'SB';
                                $dbStore->campaignType = 'sponsoredBrands';
                                $dbStore->name = $responseBody[$i]->name;
                                $dbStore->targetingType = isset($responseBody[$i]->targetingType) ? $responseBody[$i]->targetingType : 'NA';
                                $dbStore->premiumBidAdjustment = isset($responseBody[$i]->premiumBidAdjustment) ? $responseBody[$i]->premiumBidAdjustment : 'NA';
                                $dbStore->dailyBudget = isset($responseBody[$i]->dailyBudget) ? $responseBody[$i]->dailyBudget : '00';
                                $dbStore->budget = isset($responseBody[$i]->budget) ? $responseBody[$i]->budget : 'NA';
                                $dbStore->endDate = isset($responseBody[$i]->endDate) ? $responseBody[$i]->endDate : 'NA';
                                $dbStore->bidOptimization = isset($responseBody[$i]->bidOptimization) ? $responseBody[$i]->bidOptimization : false;
                                $dbStore->bidMultiplier = isset($responseBody[$i]->bidMultiplier) ? $responseBody[$i]->bidMultiplier : '00.00';
                                $dbStore->portfolioId = (isset($responseBody[$i]->portfolioId) ? $responseBody[$i]->portfolioId : 0);
                                $dbStore->campaignId = isset($responseBody[$i]->campaignId) ? $responseBody[$i]->campaignId : 0;
                                $dbStore->strCampaignId = isset($responseBody[$i]->campaignId) ? $responseBody[$i]->campaignId : 0;
                                $dbStore->budgetType = isset($responseBody[$i]->budgetType) ? $responseBody[$i]->budgetType : 'NA';
                                $dbStore->startDate = isset($responseBody[$i]->startDate) ? $responseBody[$i]->startDate : 'NA';
                                $dbStore->state = isset($responseBody[$i]->state) ? $responseBody[$i]->state : 'NA';
                                $dbStore->servingStatus = isset($responseBody[$i]->servingStatus) ? $responseBody[$i]->servingStatus : 'NA';
                                $dbStore->createdAt = date('Y-m-d H:i:s');
                                $dbStore->updatedAt = date('Y-m-d H:i:s');
                                $dbStore->pageType = 'NA';
                                $dbStore->url = 'NA';
                                if (isset($responseBody[$i]->landingPage)) {
                                    $dbStore->pageType = isset($responseBody[$i]->landingPage->pageType) ? $responseBody[$i]->landingPage->pageType : 'NA';
                                    $dbStore->url = isset($responseBody[$i]->landingPage->url) ? $responseBody[$i]->landingPage->url : 'NA';
                                }  // End check Landing Page
                                $dbStore->brandName = 'NA';
                                $dbStore->brandLogoAssetID = 'NA';
                                $dbStore->headline = 'NA';
                                $dbStore->shouldOptimizeAsins = 'NA';
                                $dbStore->brandLogoUrl = 'NA';
                                $dbStore->asins = 'NA';
                                if (isset($responseBody[$i]->creative)) {
                                    $dbStore->brandName = isset($responseBody[$i]->creative->brandName) ? $responseBody[$i]->creative->brandName : 'NA';
                                    $dbStore->brandLogoAssetID = isset($responseBody[$i]->creative->brandLogoAssetID) ? $responseBody[$i]->creative->brandLogoAssetID : 'NA';
                                    $dbStore->headline = isset($responseBody[$i]->creative->headline) ? $responseBody[$i]->creative->headline : 'NA';
                                    $dbStore->shouldOptimizeAsins = isset($responseBody[$i]->creative->shouldOptimizeAsins) ? $responseBody[$i]->creative->shouldOptimizeAsins : 'NA';
                                    $dbStore->brandLogoUrl = isset($responseBody[$i]->creative->brandLogoUrl) ? $responseBody[$i]->creative->brandLogoUrl : 'NA';
                                    $dbStore->asins = isset($responseBody[$i]->creative->asins) ? implode(',', $responseBody[$i]->creative->asins) : 'NA';
                                }
                                $dbStore->strategy = 'NA';
                                $dbStore->predicate = 'NA';
                                $dbStore->percentage = 0;
                                $dbStore->save();
                            }
                        } // End For Loop
                    } else {
                        AMSModel::insertTrackRecord('SB Campaign List data not found and profile:' . $profileId, 'success');
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    if ($try >= 3) {
                        AMSModel::insertTrackRecord('SB profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                        $try = 0;
                        continue;
                    }
                    if ($ex->getCode() == 401) {
                        if (strstr($ex->getMessage(), 'Not authorized to access scope')) {
                            // Not authorized to manage this profile. Please check permissions and consent from the advertiser
                            Log::info("Invalid Profile Id: " . $single->profileId . ' and message:' . json_encode($ex->getMessage()));
                        } elseif (strstr($ex->getMessage(), 'No matching advertiser found for scope')) {
                            // Could not find an advertiser that matches the provided scope
                            Log::info("Invalid Profile Id: " . $single->profileId . ' and message:' . json_encode($ex->getMessage()));
                        } elseif (strstr($ex->getMessage(), '401 Unauthorized')) {
                            $authCommandArray = array();
                            $authCommandArray['fkConfigId'] = $fkConfigId;
                            \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                            goto b;
                        } elseif (strstr($ex->getMessage(), 'Scope header is missing')) {
                            Log::info("Invalid Profile Id: " . $single->profileId);
                        }
                    } else if ($ex->getCode() == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 502) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 503) {
                        AMSModel::insertTrackRecord('SB getCampaignList 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                        sleep(Config::get('constants.sleepTime') + 5);
                        goto b;
                    }
                    // store report status
                    AMSModel::insertTrackRecord('App\Console\Commands\Ams\Campaign\SB\getCampaignList and error code :' . $ex->getCode(), 'fail');
                    Log::error($ex->getMessage());
                }// end catch
            }// end foreach
        }
        Log::info("filePath:App\Console\Commands\Ams\Campaign\SB\getCampaignList. End Cron.");
    }
}
