<?php

namespace App\Console\Commands\BiddingRule;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\AMSModel;
use App\Models\BiddingRuleTracker;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class UpdateKeywordBid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateKeywordbid:updatebid {data*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is use for update the bidding rule value of specific keyword SP.';

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
        Log::info("App\Console\Commands\BiddingRule\UpdateKeywordBid. Start Cron.");
        Log::info($this->description);
        if (env('APP_ENV') == 'production') {
            $dataArgumants = $this->argument('data');
            if (!empty($dataArgumants)) {
                $try = 0;
                $clientId = $dataArgumants['clientId'];
                $fkConfigId = $dataArgumants['fkConfigId'];
                $profileId = $dataArgumants['profileId'];
                if (!empty($clientId)) {
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
                            'keywordId' => $dataArgumants['keywordId'],
                            'adGroupId' => $dataArgumants['adGroupId'],
                            'campaignId' => $dataArgumants['campaignId'],
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
                                    BiddingRuleTracker::create($storeArray);
                                    //Notification and email alert for code = INVALID_ARGUMENT
                                    $code = $body[0]->code;
                                    if ($code == "INVALID_ARGUMENT") {
                                        $notificationData = $this->notificationData($dataArgumants, $code);
                                        $notificationData['errorMessage'] = "Bid is greater than budget.";
                                        $addNotification = new AmsAlertNotificationsController();
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                }
                            } catch (\Exception $ex) {
                                $try += 1;
                                if ($try >= 3) {
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                                    return false;
                                }
                                $errorCode = $ex->getCode();
                                $errorMessage = $ex->getMessage();
                                $notificationData = $this->notificationData($dataArgumants, $errorCode);
                                $notificationData['errorMessage'] = $errorMessage;
                                $addNotification = new AmsAlertNotificationsController();
                                if ($errorCode == 401) {
                                    if (strstr($errorMessage, '401 Unauthorized')) { // if auth token expire
                                        if (strstr($errorMessage, 'Not authorized to access scope')) {
                                            // store profile list not valid
                                            if ($try == 1) {
                                                $addNotification->addAlertNotification($notificationData);
                                            }
                                            Log::info("Invalid Profile Id: " . json_encode($errorMessage));
                                        } elseif (strstr($errorMessage, 'No matching advertiser found for scope')) {
                                            if ($try == 1) {
                                                $addNotification->addAlertNotification($notificationData);
                                            }
                                            Log::info("Invalid Profile Id: " . json_encode($errorMessage));
                                        } else {
                                            $authCommandArray = array();
                                            $authCommandArray['fkConfigId'] = $fkConfigId;
                                            \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                                            if ($try == 2) {
                                                $addNotification->addAlertNotification($notificationData);
                                            }
                                            goto a;
                                        }
                                    } elseif (strstr($errorMessage, 'advertiser found for scope')) {
                                        // store profile list not valid
                                        if ($try == 1) {
                                            $addNotification->addAlertNotification($notificationData);
                                        }
                                        Log::info("Invalid Profile Id: " . $profileId);
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
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                                    sleep(Config::get('constants.sleepTime') + 5);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto a;
                                } else if ($errorCode == 400) {
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '400');
                                    sleep(Config::get('constants.sleepTime') + 2);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto a;
                                } else if ($errorCode == 403) {
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '403');
                                    sleep(Config::get('constants.sleepTime') + 2);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto a;
                                } else if ($errorCode == 404) {
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '404');
                                    sleep(Config::get('constants.sleepTime') + 2);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto a;
                                } else if ($errorCode == 422) {
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '422');
                                    sleep(Config::get('constants.sleepTime') + 2);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto a;
                                } else if ($errorCode == 500) {
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, '500');
                                    sleep(Config::get('constants.sleepTime') + 2);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto a;
                                } else {
                                    AMSModel::insertTrackRecord('BiddingRule\UpdateKeywordBid. profile id:' . $profileId . ' and number try is: ' . $try, $errorCode);
                                    sleep(Config::get('constants.sleepTime') + 2);
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto a;
                                }
                                Log::error($errorMessage);
                            }
                        } else {
                            Log::info("AMS access token not found.");
                        }
                    } else {
                        Log::info("JSON Array Empty");
                    }
                } else {
                    Log::info("Client Id not found.");
                }
            } else {
                Log::info("All Get Reports download link not found.");
            }
        }
        Log::info("filePath:App\Console\Commands\BiddingRule\UpdateKeywordBid. End Cron.");
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function notificationData($data, $errorCode)
    {
        $profileCampaignData = getNotificationProfileCampaignData($data['campaignId']);
        $state = "Update call for keyword bid";
        $notificationData = [];
        $notificationData['errorType'] = $errorCode;
        $notificationData['moduleName'] = "bidding rule";
        $notificationData['notificationTitle'] = "Bidding Rule Error";
        $notificationData['notificationMessage'] = "Error " . $errorCode . " triggered on state : " . $state;
        $notificationData['fkBiddingRuleId'] = $data['fkBiddingRuleId'];
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
