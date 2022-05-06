<?php

namespace App\Console\Commands\AMS\Portfolio;

use App\Models\ams\ProfileModel;
use App\Models\AMSApiModel;
use Artisan;
use App\Models\AMSModel;
use App\Models\DayPartingModels\Portfolios;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getPortfolioListLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getPortfolioListLive:portfolio {isSandBox?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to get portfolio details';

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
        Log::info("filePath:Commands\Ams\Portfolio\getPortfolioListLive. Start Cron.");
        Log::info($this->description);
        Log::info("Auth Access token get from DB Start!");
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
                // Defined Url to get all portfolio against profiles
                $isSandboxProfile = $single->isSandboxProfile;
                $apiUrl = getSandBoxApiUrl($isSandboxProfile);
                $url = $apiUrl . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.amsPortfolioUrl');
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
                    $responseBody = [];
                    $responseBody = json_decode($response->getBody()->getContents());
                    if (!empty($responseBody) && !is_null($responseBody)) {
                        $PortfolioDataInsert = [];
                        Log::info('Portfolio Record Found');
                        foreach ($responseBody as $singleResponseRecord) {
                            $PortfolioDataArray = [];
                            $PortfolioDataArray['profileId'] = $profileId;
                            $PortfolioDataArray['fkProfileId'] = $single->id;
                            $PortfolioDataArray['fkConfigId'] = $fkConfigId;
                            $PortfolioDataArray['portfolioId'] = $singleResponseRecord->portfolioId;
                            $PortfolioDataArray['name'] = $singleResponseRecord->name;
                            if (isset($singleResponseRecord->budget)) {
                                $PortfolioDataArray['amount'] = $singleResponseRecord->budget->amount;
                                $PortfolioDataArray['currencyCode'] = $singleResponseRecord->budget->currencyCode;
                                $PortfolioDataArray['policy'] = $singleResponseRecord->budget->policy;
                            } else {
                                $PortfolioDataArray['amount'] = 0.00;
                                $PortfolioDataArray['currencyCode'] = 'NA';
                                $PortfolioDataArray['policy'] = 'NA';
                            }
                            $PortfolioDataArray['inBudget'] = $singleResponseRecord->inBudget;
                            $PortfolioDataArray['state'] = $singleResponseRecord->state;
                            $PortfolioDataArray['sandBox'] = 0;
                            $PortfolioDataArray['live'] = 1;
                            $PortfolioDataArray['createdAt'] = date('Y-m-d H:i:s');
                            $PortfolioDataArray['updatedAt'] = date('Y-m-d H:i:s');
                            array_push($PortfolioDataInsert, $PortfolioDataArray);
                        } // End Foreach Loop for making insertion data of portfolis
                        Portfolios::storePortfolios($PortfolioDataInsert);
                        Log::info('Portfolio inserted against Profile Id : ' . $profileId);
                    } else {
                        // Portfolios status
                        AMSModel::insertTrackRecord('Get Portfolios Against profile id: ' . $profileId, 'not record found');
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    if ($try >= 3) {
                        AMSModel::insertTrackRecord('PortfolioListLive profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
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
                        AMSModel::insertTrackRecord('PortfolioListLive 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                        sleep(Config::get('constants.sleepTime') + 5);
                        goto b;
                    }
                    // store report status
                    AMSModel::insertTrackRecord('Profile List Id :' . $profileId . '. Get Portfolios and error code :' . $ex->getCode(), 'fail');
                    Log::error($ex->getMessage());
                }// end catch
            } // End Foreach Loop
        } else {
            Log::info("Profile List not found.");
        }
        Log::info("filePath:Commands\Ams\Portfolio\getPortfolioListLive. End Cron.");
    }
}
