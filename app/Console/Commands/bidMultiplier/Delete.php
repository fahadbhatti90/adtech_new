<?php

namespace App\Console\Commands\bidMultiplier;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\ams\campaign\CampaignList;
use App\Models\AMSModel;
use App\Models\BidMultiplierModels\BidMultiplierListModel;
use App\Models\BidMultiplierModels\BidMultiplierTracker;
use App\Models\BidMultiplierModels\KeywordBidValue;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Delete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:bidMultiplier {data*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to delete and update record on ams of specific campaign.';

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
     * @return false
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $dataArgumants = $this->argument('data');
        if (!empty($dataArgumants)) {
            $clientId = $dataArgumants->getTokenDetail['client_id'];
            $fkConfigId = $dataArgumants->getTokenDetail['fkConfigId'];
            $profileId = $dataArgumants['profileId'];
            $fkMultiplierId = $dataArgumants['fkMultiplierId'];
            $bidMultiplierActualRecord = BidMultiplierListModel::find($fkMultiplierId);
            $campaignId = $bidMultiplierActualRecord['campaignId'];
            $type = $dataArgumants['type'];
            $jsonArray = array();
            $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.sbCampaignUrl');
            $jsonArrayObj = [
                'campaignId' => (int)$campaignId,
                'bidOptimization' => true,
            ];
            array_push($jsonArray, $jsonArrayObj);
            $loopValue = 1; // loop iterate if keyword data more then 1000 in case adtype if SB than loop run one time
            if ($type == 'SP') {
                unset($jsonArray);
                $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spKeywordUpdateBid');
                // get all keyword bid data
                $ActuallistOfKeywordWithBid = KeywordBidValue::where('fkMultiplierId', $fkMultiplierId)
                    ->where('campaignId', $campaignId)
                    ->where('isEligible', 1)
                    ->get();
                $totalKeywordRecordCount = count($ActuallistOfKeywordWithBid); // total count of keyword data
                if ($totalKeywordRecordCount >= 1000 && $totalKeywordRecordCount < 9999) {
                    $digitArray = str_split($totalKeywordRecordCount);
                    $loopValue = $digitArray[0];
                    if ($digitArray[1] > 0 || $digitArray[2] > 0 || $digitArray[3] > 0) {
                        $loopValue++;
                    }
                }
                $jsonArray = array();
                for ($i = 0; $i < $totalKeywordRecordCount; $i++) {
                    $jsonArrayObj = (object)[
                        'keywordId' => $ActuallistOfKeywordWithBid[$i]['keywordId'],
                        'state' => $ActuallistOfKeywordWithBid[$i]['state'],
                        'bid' => $ActuallistOfKeywordWithBid[$i]['bid']
                    ];
                    array_push($jsonArray, $jsonArrayObj);
                }
            }// if Check
            if (!empty($jsonArray)) {
                for ($i = 0; $i < $loopValue; $i++) {
                    $chunkValue = count($jsonArray);
                    if ($chunkValue >= 1000 && $chunkValue < 9999) {
                        $batch1000value = array_chunk($jsonArray, 1000);
                    }
                    $try = 0;
                    a:
                    $obaccess_token = new AMSModel();
                    $getAMSTokenById = $obaccess_token->getAMSTokenById($fkConfigId);
                    $accessToken = $getAMSTokenById->access_token;
                    if (empty($accessToken)) {
                        return;
                    }
                    try {
                        $client = new Client();
                        $response = $client->request('PUT', $url, [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $accessToken,
                                'Content-Type' => 'application/json',
                                'Amazon-Advertising-API-ClientId' => $clientId,
                                'Amazon-Advertising-API-Scope' => $profileId
                            ],
                            'json' => ($loopValue > 1) ? $batch1000value[$i] : $jsonArray,
                            'delay' => Config::get('constants.delayTimeInApi'),
                            'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                            'timeout' => Config::get('constants.timeoutInApi'),
                        ]);
                        $body = json_decode($response->getBody()->getContents());
                        if (!empty($body) && $body != null) {
                            if ($type == 'SB') { //adtype SB
                                if ($body[0]->code == 'SUCCESS') {
                                    BidMultiplierTracker::where('fkMultiplierId', $fkMultiplierId)
                                        ->update(array('bidOptimizationValue' => true, 'bid' => 0));
                                    CampaignList::where('campaignId', $campaignId)
                                        ->update(array('bidOptimization' => true, 'bidMultiplier' => 0));
                                }
                            } elseif ($type == 'SP') { // adtype SP
                                $mainArray = array();
                                foreach ($body as $singleRecord) {
                                    $oldBidValue = KeywordBidValue::where('fkMultiplierId', $fkMultiplierId)->where('keywordId', $singleRecord->keywordId)->first();
                                    $storeArray = [
                                        'fkMultiplierId' => $fkMultiplierId,
                                        'fkConfigId' => $fkConfigId,
                                        'profileId' => $profileId,
                                        'campaignId' => $campaignId,
                                        'bidOptimizationValue' => false,
                                        'keywordId' => $singleRecord->keywordId,
                                        'oldBid' => $oldBidValue->tempBid,
                                        'bid' => $oldBidValue->bid,
                                        'code' => $singleRecord->code,
                                        'creationDate' => date('Y-m-d H:i:s')
                                    ];
                                    array_push($mainArray, $storeArray);
                                }
                                BidMultiplierTracker::insertRecord($mainArray);
                            }
                        }
                    } catch (\Exception $ex) {
                        if ($try >= 3) {
                            AMSModel::insertTrackRecord('App\Console\Commands\bidMultiplier profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                            return false;
                        }
                        $try += 1;
                        $errorCode = $ex->getCode();
                        $errorMessage = $ex->getMessage();
                        $notificationData = $this->notificationData($dataArgumants, $errorCode, $errorMessage);
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
                                    if ($try == 2) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    $authCommandArray = array();
                                    $authCommandArray['fkConfigId'] = $fkConfigId;
                                    \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
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
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            sleep(Config::get('constants.sleepTime') + 2);
                            goto a;
                        } else if ($errorCode == 502) {
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            sleep(Config::get('constants.sleepTime') + 2);
                            goto a;
                        } else if ($errorCode == 503) {
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            AMSModel::insertTrackRecord('App\Console\Commands\bidMultiplier 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                            sleep(Config::get('constants.sleepTime') + 5);
                            goto a;
                        }
                        // store report status
                        AMSModel::insertTrackRecord('App\Console\Commands\bidMultiplier and error code :' . $ex->getCode(), 'fail');
                        // store report status
                        AMSModel::insertTrackRecord(json_encode($errorMessage), 'fail');
                        Log::error($errorMessage);
                    }
                }// endfor loop
            }//end if
        }
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function notificationData($data, $errorCode, $errorMessage)
    {
        $profileId = $data->profileId;
        $fkMultiplierId = $data->fkMultiplierId;
        $campaignId = $data->campaignId;
        $getNotificationFkProfileId = getNotificationFkProfileId($profileId);
        $fkProfileId = $getNotificationFkProfileId->id;
        $profileCampaignData = getNotificationProfileCampaignData($campaignId);
        $state = "Bid Multiplier Delete Cron";
        $notificationData = [];
        $notificationData['errorType'] = $errorCode;
        $notificationData['moduleName'] = "Bid Multiplier";
        $notificationData['notificationTitle'] = "Bid Multiplier Error";
        $notificationData['notificationMessage'] = "Error " . $errorCode . " triggered on state : " . $state;
        $notificationData['fkMultiplierId'] = $fkMultiplierId;
        $notificationData['campaignId'] = $campaignId;
        $notificationData['campaignName'] = $profileCampaignData->name;
        $notificationData['fkProfileId'] = $fkProfileId;
        $notificationData['state'] = $state;
        $notificationData['sendEmail'] = 1;
        $notificationData['type'] = $errorCode;
        $notificationData['errorMessage'] = $errorMessage;
        return $notificationData;
    }
}
