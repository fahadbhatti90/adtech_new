<?php

namespace App\Console\Commands\Tacos;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\ams\ProfileModel;
use App\Models\AMSApiModel;
use App\Models\AMSModel;
use App\Models\Tacos\keywordList;
use App\Models\Tacos\TacosCronModel;
use App\Models\Tacos\TargetList;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getKeywordData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keywordlist:tacos {array*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $array = $this->argument('array');
        if ($array == null) {
            exit;
        }
        $try = 0;
        $url = ''; // Create a client with a base URI
        $reportType = $array[0]->type;
        if ($reportType == 'SP') {
            $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spKeywordList') . '?startIndex=0&campaignType=' . $array[0]->sponsoredType . '&campaignIdFilter=' . $array[0]->campaignId;
        } else if ($reportType == 'SB') {
            $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.sbKeywordList') . '?campaignIdFilter=' . $array[0]->campaignId;
        } else if ($reportType == 'SD') {
            $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.sdTargetsList') . '?campaignIdFilter=' . $array[0]->campaignId;
        }
        b:
        $clientId = $array[0]->getTokenDetail['client_id'];
        $fkConfigId = $array[0]->getTokenDetail['fkConfigId'];
        $profileId = $array[0]->profileId;
        $fkTacosId = $array[0]->fkTacosId;
        $cronId = $array[0]->id;
        $singleAmsApiCreds = AMSApiModel::with('getTokenDetail')->where('id', $fkConfigId)->first();
        $accessToken = $singleAmsApiCreds->getTokenDetail->access_token;
        $this->checkTimeAbove3();
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
            $body = json_decode($response->getBody()->getContents());
            if (!empty($body)) {
                TacosCronModel::where('id', $cronId)
                    ->update(['isData' => 1]);
                if ($reportType == 'SD') {
                    for ($i = 0; $i < count($body); $i++) {
                        if (isset($body[$i]->bid)) {
                            $checkExistValue = TargetList::where('fkTacosId', $fkTacosId)
                                ->where('targetId', $body[$i]->targetId)
                                ->get();
                            if ($checkExistValue->isEmpty()) {
                                $storeTargetObj = new TargetList();
                                $storeTargetObj->fkId = $cronId;
                                $storeTargetObj->fkTacosId = $fkTacosId;
                                $storeTargetObj->fkConfigId = $fkConfigId;
                                $storeTargetObj->profileId = $profileId;
                                $storeTargetObj->campaignId = $array[0]->campaignId;
                                $storeTargetObj->reportType = $reportType;
                                $storeTargetObj->adGroupId = $body[$i]->adGroupId;
                                $storeTargetObj->targetId = $body[$i]->targetId;
                                $storeTargetObj->state = $body[$i]->state;
                                $storeTargetObj->bid = $body[$i]->bid;
                                $storeTargetObj->createdAt = date('Y-m-d H:i:s');
                                $storeTargetObj->updatedAt = date('Y-m-d H:i:s');
                                $storeTargetObj->save();
                            } else {
                                TargetList::where('fkTacosId', $fkTacosId)
                                    ->where('targetId', $body[$i]->targetId)
                                    ->update(['bid' => $body[$i]->bid, 'updatedAt' => date('Y-m-d H:i:s')]);
                            }
                        }// end if
                    }// end for loop
                } else {
                    for ($i = 0; $i < count($body); $i++) {
                        if (isset($body[$i]->bid)) {
                            $checkExistValue = keywordList::where('fkTacosId', $fkTacosId)
                                ->where('keywordId', $body[$i]->keywordId)
                                ->get();
                            if ($checkExistValue->isEmpty()) {
                                $storeKeywordObj = new keywordList();
                                $storeKeywordObj->fkId = $cronId;
                                $storeKeywordObj->fkTacosId = $fkTacosId;
                                $storeKeywordObj->fkConfigId = $fkConfigId;
                                $storeKeywordObj->profileId = $profileId;
                                $storeKeywordObj->campaignId = $array[0]->campaignId;
                                $storeKeywordObj->reportType = $reportType;
                                $storeKeywordObj->adGroupId = $body[$i]->adGroupId;
                                $storeKeywordObj->keywordId = $body[$i]->keywordId;
                                $storeKeywordObj->state = $body[$i]->state;
                                $storeKeywordObj->bid = isset($body[$i]->bid) ? $body[$i]->bid : '0.00';
                                $storeKeywordObj->keywordText = $body[$i]->keywordText;
                                $storeKeywordObj->matchType = $body[$i]->matchType;
                                $storeKeywordObj->servingStatus = isset($body[$i]->servingStatus) ? $body[$i]->servingStatus : 'NA';
                                $storeKeywordObj->creationDate = isset($body[$i]->creationDate) ? $body[$i]->creationDate : 'NA';
                                $storeKeywordObj->lastUpdatedDate = isset($body[$i]->lastUpdatedDate) ? $body[$i]->lastUpdatedDate : 'NA';
                                $storeKeywordObj->createdAt = date('Y-m-d H:i:s');
                                $storeKeywordObj->updatedAt = date('Y-m-d H:i:s');
                                $storeKeywordObj->save();
                            } else {
                                keywordList::where('fkTacosId', $fkTacosId)
                                    ->where('keywordId', $body[$i]->keywordId)
                                    ->update(['bid' => $body[$i]->bid, 'updatedAt' => date('Y-m-d H:i:s')]);
                            }// end if
                        }// end if
                    }// end for loop
                }// end if
            } else {
                // if body is empty
            }
        } catch (\Exception $ex) {
            $try += 1;
            if ($try >= 3) {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');

                return false;
            }
            $errorCode = $ex->getCode();
            $errorMessage = $ex->getMessage();
            $notificationData = $this->notificationData($array[0], $errorCode);
            $notificationData['errorMessage'] = $errorMessage;
            $addNotification = new AmsAlertNotificationsController();
            if ($errorCode == 401) {
                if (strstr($errorMessage, '401 Unauthorized')) { // if auth token expire
                    if (strstr($errorMessage, 'Not authorized to access scope')) {
                        // store profile list not valid
                        Log::info("Invalid Profile Id: " . json_encode($errorMessage));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } elseif (strstr($errorMessage, 'No matching advertiser found for scope')) {
                        Log::info("Invalid Profile Id: " . json_encode($errorMessage));
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
                        goto b;
                    }
                } elseif (strstr($errorMessage, 'advertiser found for scope')) {
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
                goto b;
            } else if ($errorCode == 502) {
                sleep(Config::get('constants.sleepTime') + 2);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            } else if ($errorCode == 503) {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                sleep(Config::get('constants.sleepTime') + 5);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            } else if ($errorCode == 400) {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList. profile id:' . $profileId . ' and number try is: ' . $try, '400');
                sleep(Config::get('constants.sleepTime') + 2);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            } else if ($errorCode == 403) {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList. profile id:' . $profileId . ' and number try is: ' . $try, '403');
                sleep(Config::get('constants.sleepTime') + 2);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            } else if ($errorCode == 404) {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList. profile id:' . $profileId . ' and number try is: ' . $try, '404');
                sleep(Config::get('constants.sleepTime') + 2);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            } else if ($errorCode == 422) {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList. profile id:' . $profileId . ' and number try is: ' . $try, '422');
                sleep(Config::get('constants.sleepTime') + 2);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            } else if ($errorCode == 500) {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList. profile id:' . $profileId . ' and number try is: ' . $try, '500');
                sleep(Config::get('constants.sleepTime') + 2);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            } else {
                AMSModel::insertTrackRecord('App\Console\Commands\Tacos\keywordList. profile id:' . $profileId . ' and number try is: ' . $try, $errorCode);
                sleep(Config::get('constants.sleepTime') + 2);
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                goto b;
            }
            // store report status
            Log::error($errorMessage);
        }// end catch
    }


    /**
     * This function is used to validate day and time
     */
    private function checkTimeAbove3()
    {
        $currentDay = date('D');
        $nextDay = date('D', strtotime('+1 day', time()));
        // check if day change and it time is less 3:00 am then it will not run
        if ($currentDay == 'Mon' || $currentDay == 'Tue' || $nextDay == 'Tue') {
            if ($currentDay == 'Tue' && date('H:i:s') >= '03:00:00') {
                die;
            }
        } elseif ($currentDay == 'Fri' || $currentDay == 'Sat' || $nextDay == 'Sat') {
            if ($currentDay == 'Sat' && date('H:i:s') >= '03:00:00') {
                die;
            }
        }
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function notificationData($data, $errorCode)
    {
        $profileCampaignData = getNotificationProfileCampaignData($data->campaignId);
        $state = "Data availability (bid)";
        $notificationData = [];
        $notificationData['errorType'] = $errorCode;
        $notificationData['moduleName'] = "tacos";
        $notificationData['notificationTitle'] = "Tacos Error";
        $notificationData['notificationMessage'] = "Error " . $errorCode . " triggered on state : " . $state;
        $notificationData['fkTacosId'] = $data->fkTacosId;
        $notificationData['campaignId'] = $data->campaignId;
        $notificationData['campaignName'] = $profileCampaignData->name;
        $notificationData['sponsoredType'] = $data->sponsoredType;
        $notificationData['fkProfileId'] = $profileCampaignData->fkProfileId;
        $notificationData['profileId'] = $data->profileId;
        $notificationData['state'] = $state;
        $notificationData['sendEmail'] = 1;
        $notificationData['type'] = $errorCode;
        return $notificationData;
    }
}
