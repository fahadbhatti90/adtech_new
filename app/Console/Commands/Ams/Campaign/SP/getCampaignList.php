<?php

namespace App\Console\Commands\Ams\Campaign\SP;

use App\Models\ams\ProfileModel;
use App\Models\AMSApiModel;
use Artisan;
use DB;
use App\Models\AMSModel;
use App\Models\DayPartingModels\PortfolioAllCampaignList;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getCampaignList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getCampaignList:campaignSP {isSandBox?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to get Sponsored Product Campaign List';

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
        Log::info("filePath:App\Console\Commands\Ams\Campaign\SP\getCampaignList. Start Cron.");
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
                $profileId = $single->profileId;
                // Create a client with a base URI
                $isSandboxProfile = $single->isSandboxProfile;
                $apiUrl = getSandBoxApiUrl($isSandboxProfile);
                $url = $apiUrl . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spCampaignUrl');
                b:
                $singleAmsApiCreds = AMSApiModel::with('getTokenDetail')->where('id', $fkConfigId)->first();
                $accessToken = $singleAmsApiCreds->getTokenDetail->access_token;
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
                        AMSModel::insertTrackRecord('SP Campaign List data found and profile:' . $profileId, 'success');
                        $campaignStoreArray = array();
                        for ($i = 0; $i < count($responseBody); $i++) {
                            $dbStore = [];
                            $dbStore['fkProfileId'] = $single->id;
                            $dbStore['fkConfigId'] = $fkConfigId;
                            $dbStore['profileId'] = $profileId;
                            $dbStore['type'] = 'SP';
                            $dbStore['campaignType'] = $responseBody[$i]->campaignType;
                            $dbStore['name'] = $responseBody[$i]->name;
                            $dbStore['targetingType'] = $responseBody[$i]->targetingType;
                            $dbStore['premiumBidAdjustment'] = $responseBody[$i]->premiumBidAdjustment;
                            $dbStore['dailyBudget'] = $responseBody[$i]->dailyBudget;
                            $dbStore['budget'] = isset($responseBody[$i]->budget) ? $responseBody[$i]->budget : 00.00;
                            $dbStore['endDate'] = isset($responseBody[$i]->endDate) ? $responseBody[$i]->endDate : 'NA';
                            $dbStore['bidOptimization'] = isset($responseBody[$i]->bidOptimization) ? $responseBody[$i]->bidOptimization : 'NA';
                            $dbStore['portfolioId'] = (isset($responseBody[$i]->portfolioId) ? $responseBody[$i]->portfolioId : 0);
                            $dbStore['campaignId'] = isset($responseBody[$i]->campaignId) ? $responseBody[$i]->campaignId : 0;
                            $dbStore['strCampaignId'] = isset($responseBody[$i]->campaignId) ? $responseBody[$i]->campaignId : 0;
                            $dbStore['budgetType'] = isset($responseBody[$i]->budgetType) ? $responseBody[$i]->budgetType : 'NA';
                            $dbStore['startDate'] = isset($responseBody[$i]->startDate) ? $responseBody[$i]->startDate : 'NA';
                            $dbStore['state'] = isset($responseBody[$i]->state) ? $responseBody[$i]->state : 'NA';
                            $dbStore['servingStatus'] = isset($responseBody[$i]->servingStatus) ? $responseBody[$i]->servingStatus : 'NA';
                            $dbStore['createdAt'] = date('Y-m-d H:i:s');
                            $dbStore['updatedAt'] = date('Y-m-d H:i:s');
                            $dbStore['pageType'] = 'NA';
                            $dbStore['url'] = 'NA';
                            $dbStore['brandName'] = 'NA';
                            $dbStore['brandLogoAssetID'] = 'NA';
                            $dbStore['headline'] = 'NA';
                            $dbStore['shouldOptimizeAsins'] = 'NA';
                            $dbStore['brandLogoUrl'] = 'NA';
                            $dbStore['asins'] = 'NA';
                            $dbStore['strategy'] = 'NA';
                            $dbStore['predicate'] = 'NA';
                            $dbStore['percentage'] = 0;
                            if (isset($responseBody[$i]->bidding)) {
                                $dbStore['strategy'] = isset($responseBody[$i]->bidding->strategy) ? $responseBody[$i]->bidding->strategy : 'NA';
                                if (isset($responseBody[$i]->bidding->adjustments)) {
                                    $predicate = isset($responseBody[$i]->bidding->adjustments[0]->predicate) ? $responseBody[$i]->bidding->adjustments[0]->predicate : 'NA';
                                    $percentage = isset($responseBody[$i]->bidding->adjustments[0]->percentage) ? $responseBody[$i]->bidding->adjustments[0]->percentage : 0;
                                    $dbStore['predicate'] = $predicate;
                                    $dbStore['percentage'] = $percentage;
                                }
                            }  // End check Bidding Property
                            array_push($campaignStoreArray, $dbStore);
                        } // End For Loop
                        PortfolioAllCampaignList::storeCampaignList($campaignStoreArray);
                        unset($campaignStoreArray);
                        unset($dbStore);
                    } else {
                        AMSModel::insertTrackRecord('SP Campaign List data not found and profile:' . $profileId, 'success');
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    if ($try >= 3) {
                        AMSModel::insertTrackRecord('SP profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
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
                        AMSModel::insertTrackRecord('SP getCampaignList 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                        sleep(Config::get('constants.sleepTime') + 5);
                        goto b;
                    }
                    // store report status
                    AMSModel::insertTrackRecord('App\Console\Commands\Ams\Campaign\SP\getCampaignList and error code :' . $ex->getCode(), 'fail');
                    Log::error($ex->getMessage());
                }// end catch
            }// end foreach
        } else {
            Log::info("Profile List not found.");
        }
        Log::info("filePath:App\Console\Commands\Ams\Campaign\SP\getCampaignList. End Cron.");
    }
}
