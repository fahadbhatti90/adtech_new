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

class getPortfolioList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getSandBoxPortfolioDetailData:portfolio';

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        Log::info("filePath:Commands\Ams\Portfolio. Start Cron.");
        Log::info($this->description);
        $allProfileIds = collect();
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
                // Defined Url to get all portfolio against profiles
                $apiUrl = getApiUrlForDiffEnv(env('APP_ENV'));
                $url = $apiUrl . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.amsPortfolioUrl');
                // Goto Statement used
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
                    $responseBody = [];
                    $responseBody = json_decode($response->getBody()->getContents());
                    if (!empty($responseBody) && !is_null($responseBody)) {
                        $PortfolioDataInsert = [];
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
                        if (!empty($PortfolioDataArray)) {
                            Portfolios::insertPortfolioList($PortfolioDataInsert);
                            unset($PortfolioDataInsert);
                            unset($PortfolioDataArray);
                        }
                    } else {
                        AMSModel::insertTrackRecord('Get Portfolios Against Day parting profile id: ' . $profileId, 'not record found');
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    if ($try >= 3) {
                        AMSModel::insertTrackRecord('PortfolioList Day parting profile id:' . $profileId . ' and number try is: ' . $try.' error code :'.$ex->getCode(), 'success');
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
                        AMSModel::insertTrackRecord('Day partying Portfolios Day parting 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                        sleep(Config::get('constants.sleepTime') + 5);
                        goto b;
                    }
                    // store report status
                    AMSModel::insertTrackRecord('Profile List Id :'.$profileId.'. Get Portfolios Day parting and error code :'.$ex->getCode(), 'fail');
                    Log::error($ex->getMessage());
                }// end catch
            } // End Foreach Loop
        }
        Log::info("filePath:Commands\Ams\Portfolio. End Cron.");
    }
}
