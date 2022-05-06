<?php

namespace App\Console\Commands\Tacos;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\AMSModel;
use App\Models\Tacos\TacosBidTracker;
use App\Models\Tacos\TacosCronModel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class UpdateKeywordBid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateKeywordbid:tacos {data*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is use for update the tacos bidding rule value of specific keyword SP.';

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
        Log::info("App\Console\Commands\Tacos\UpdateKeywordBid. Start Cron.");
        Log::info($this->description);
        if (env('APP_ENV') == 'production') {
            $dataArgumants = $this->argument('data');
            if (!empty($dataArgumants)) {
                $try = 0;
                $clientId = $dataArgumants['clientId'];
                $fkConfigId = $dataArgumants['fkConfigId'];
                $profileId = $dataArgumants['profileId'];
                $fkTacosId = $dataArgumants['fkTacosId'];
                TacosCronModel::where('fkTacosId', $fkTacosId)->update(['ruleResult' => 1, 'updatedAt' => date('Y-m-d H:i:s')]);
                $jsonArray = array();
                $url = ''; // Create a client with a base URI
                if ($dataArgumants['reportType'] == 'SP') {
                    $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spKeywordUpdateBid');
                    $jsonArrayObj = (object)[
                        'keywordId' => $dataArgumants['keywordId'],
                        'state' => $dataArgumants['state'],
                        'bid' => $dataArgumants['newbid']
                    ];
                    array_push($jsonArray, $jsonArrayObj);
                } else if ($dataArgumants['reportType'] == 'SB') {
                    $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.sbKeywordList');
                    $jsonArrayObj = (object)[
                        'keywordId' => (int)$dataArgumants['keywordId'],
                        'adGroupId' => (int)$dataArgumants['adGroupId'],
                        'campaignId' => (int)$dataArgumants['campaignId'],
                        'state' => $dataArgumants['state'],
                        'bid' => $dataArgumants['newbid']
                    ];
                    array_push($jsonArray, $jsonArrayObj);
                }
                if (!empty($jsonArray)) {
                    a:
                    $obaccess_token = new AMSModel();
                    $getAMSTokenById = $obaccess_token->getAMSTokenById($fkConfigId);
                    $accessToken = $getAMSTokenById->access_token;
                    if (!empty($accessToken)) {
                        try {
                            $client = new Client();
                            $response = $client->request('PUT', $url, [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $accessToken,
                                    'Content-Type' => 'application/json',
                                    'Amazon-Advertising-API-ClientId' => $clientId,
                                    'Amazon-Advertising-API-Scope' => $profileId
                                ],
                                'json' => $jsonArray,
                                'delay' => Config::get('constants.delayTimeInApi'),
                                'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                                'timeout' => Config::get('constants.timeoutInApi'),
                            ]);
                            $body = json_decode($response->getBody()->getContents());
                            if (!empty($body) && $body != null) {
                                Log::info("Make Array For Data Insertion");
                                $storeArray = [
                                    'fkTacosId' => $fkTacosId,
                                    'fkConfigId' => $fkConfigId,
                                    'profileId' => $profileId,
                                    'adGroupId' => $dataArgumants['adGroupId'],
                                    'campaignId' => $dataArgumants['campaignId'],
                                    'state' => $dataArgumants['state'],
                                    'reportType' => $dataArgumants['reportType'],
                                    'oldBid' => $dataArgumants['oldbid'],
                                    'bid' => $dataArgumants['newbid'],
                                    'keywordId' => $body[0]->keywordId,
                                    'code' => $body[0]->code,
                                    'creationDate' => date('Y-m-d')
                                ];
                                TacosBidTracker::create($storeArray);
                            }
                        } catch (\Exception $ex) {
                            $try += 1;
                            if ($try >= 3) {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');

                                return false;
                            }
                            $errorCode = $ex->getCode();
                            $errorMessage = $ex->getMessage();
                            $notificationData = $this->notificationData($dataArgumants, $errorCode);
                            $notificationData['errorMessage'] = $errorMessage;
                            $addNotification = new AmsAlertNotificationsController();
                            if ($errorCode == 401) {
                                if (strstr($ex->getMessage(), '401 Unauthorized')) { // if auth token expire
                                    if (strstr($ex->getMessage(), 'Not authorized to access scope')) {
                                        // store profile list not valid
                                        Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                                        if ($try == 1) {
                                            $addNotification->addAlertNotification($notificationData);
                                        }
                                    } elseif (strstr($ex->getMessage(), 'No matching advertiser found for scope')) {
                                        Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                                        if ($try == 1) {
                                            $addNotification->addAlertNotification($notificationData);
                                        }
                                    } else {
                                        $authCommandArray = array();
                                        $authCommandArray['fkConfigId'] = $fkConfigId;
                                        \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                                        if ($try == 2) {
                                            $addNotification->addAlertNotification($notificationData);
                                        }
                                        goto a;
                                    }
                                } elseif (strstr($ex->getMessage(), 'advertiser found for scope')) {
                                    // store profile list not valid
                                    Log::info("Invalid Profile Id: " . $profileId);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                }
                            } else if ($errorCode == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else if ($errorCode == 502) {
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else if ($errorCode == 503) {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                                sleep(Config::get('constants.sleepTime') + 5);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else if ($errorCode == 400) {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '400');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else if ($errorCode == 403) {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '403');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else if ($errorCode == 404) {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '404');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else if ($errorCode == 422) {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '422');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else if ($errorCode == 500) {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '500');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            } else {
                                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, $errorCode);
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto a;
                            }
                            // store report status
                            AMSModel::insertTrackRecord('App\Console\Commands\Tacos\UpdateKeywordBid and error code :' . $ex->getCode(), 'fail');
                            // store report status
                            AMSModel::insertTrackRecord(json_encode($ex->getMessage()), 'fail');
                            Log::error($ex->getMessage());
                        }
                    } else {
                        Log::info("AMS access token not found.");
                    }
                } else {
                    Log::info("JSON Array Empty");
                }
            } else {
                Log::info("All Get Reports download link not found.");
            }
        }
        Log::info("filePath:App\Console\Commands\Tacos\UpdateKeywordBid. End Cron.");
    }

    /**
     *   deleteAmsFailedLinkReportId
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function notificationData($data, $errorCode)
    {
        $profileCampaignData = getNotificationProfileCampaignData($data['campaignId']);
        $state = "Update call for Keyword bid";
        $notificationData = [];
        $notificationData['errorType'] = $errorCode;
        $notificationData['moduleName'] = "tacos";
        $notificationData['notificationTitle'] = "Tacos Error";
        $notificationData['notificationMessage'] = "Error " . $errorCode . " triggered on state : " . $state;
        $notificationData['campaignId'] = $data['campaignId'];
        $notificationData['campaignName'] = $profileCampaignData->name;
        $notificationData['fkProfileId'] = $profileCampaignData->fkProfileId;
        $notificationData['profileId'] = $data['profileId'];
        $notificationData['state'] = $state;
        $notificationData['sendEmail'] = 1;
        $notificationData['type'] = $errorCode;
        return $notificationData;
    }
}
