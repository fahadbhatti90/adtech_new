<?php

namespace App\Console\Commands\AMS\Portfolio\SP;

use App\Models\ams\ProfileModel;
use App\Models\AMSApiModel;
use Artisan;
use App\Models\DayPartingModels\PortfolioAllCampaignList;
use App\Models\AMSModel;
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
    //protected $signature = 'getSPCampaignlist:portfolio';
    protected $signature = 'getSandBoxSPCampaignlist:portfolio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to get Day partying Sponsored Product Campaign List';

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
        Log::info("filePath:Commands\Ams\Portfolio\SP\getSPCampaignlist. Start Cron.");
        Log::info($this->description);
        Log::info("Auth token get from DB Start!");
        //update all auth tokens before this cron

        $allProfileIds = ProfileModel::with(['getTokenDetail'])
            ->where('isSandboxProfile', 1)
            ->get(['profileId', 'id', 'fkConfigId']);

        if ($allProfileIds->isNotEmpty()) {

            $try = 0;
            foreach ($allProfileIds as $single) {

                if ($single->getTokenDetail == null) { // if is null
                    continue;
                }
                $clientId = $single->getTokenDetail->client_id;
                $fkConfigId = $single->getTokenDetail->fkConfigId;
                $profileId = $single->profileId;

                // Create a client with a base URI
                $apiUrl = getApiUrlForDiffEnv(env('APP_ENV'));
                $url = $apiUrl . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spCampaignUrl');

                Log::info('Url = ' . $url);
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
                            'Amazon-Advertising-API-Scope' => $profileId],
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi'),
                    ]);
                    $responseBody = json_decode($response->getBody()->getContents());

                    if (!empty($responseBody) && !is_null($responseBody)) {
                        $campaignStoreArray = [];
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
                            // End check creative field else
                        } // End For Loop

                        // Insertion In Database
                        if (!empty($campaignStoreArray)) {
                            PortfolioAllCampaignList::insertCampaignList($campaignStoreArray);
                            unset($dbStore);
                            unset($campaignStoreArray);
                        }
                    } else {
                        Log::info('no record found In file filePath:Commands\Ams\Portfolio\SP\getCampaignList ');
                        // store report status
                        AMSModel::insertTrackRecord(Config::get('constants.portfolioSponsoredBrand') . ' Data' . ' profile id: ' . $profileId, 'not record found');
                    } // End Else
                } catch (\Exception $ex) {
                    $try += 1;
                    if ($try >= 3) {
                        AMSModel::insertTrackRecord('SP Day partying profile id:' . $profileId . ' and number try is: ' . $try.' error code :'.$ex->getCode(), 'success');
                        $try = 0;
                        continue;
                    }
                    if ($ex->getCode() == 401) {
                        if (strstr($ex->getMessage(), '401 Unauthorized')) { // if auth token expire
                            if (strstr($ex->getMessage(), 'Not authorized to access scope')) {
                                // store profile list not valid
                                Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                            } elseif (strstr($ex->getMessage(), 'No matching advertiser found for scope')) {
                                Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                            } else {
                                $authCommandArray = array();
                                $authCommandArray['fkConfigId'] = $fkConfigId;
                                \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                                goto b;
                            }
                        } elseif (strstr($ex->getMessage(), 'advertiser found for scope')) {
                            // store profile list not valid
                            Log::info("Invalid Profile Id: " . $profileId);
                        }
                    } else if ($ex->getCode() == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 502) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 503) {
                        AMSModel::insertTrackRecord('SP day partying  getCampaignList 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                        sleep(Config::get('constants.sleepTime') + 5);
                        goto b;
                    }
                    // store report status
                    AMSModel::insertTrackRecord('App\Console\Commands\Ams\Portfolio\SP\getSBCampaignlist and error code :'.$ex->getCode(), 'fail');
                    Log::error($ex->getMessage());
                }// End catch
            }// end foreach
        } else {
            Log::info("Profile List not found.");
        }
        Log::info("filePath:Commands\Ams\Portfolio\SP\getSPCampaignlist. End Cron.");
    }
}
