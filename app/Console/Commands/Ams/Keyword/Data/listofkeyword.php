<?php

namespace App\Console\Commands\Ams\Keyword\Data;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\ams\ProfileModel;
use App\Models\ams\Target\BiddingRule\TargetList;
use Artisan;
use DB;
use App\models\BiddingRule;
use App\Models\AMSModel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class listofkeyword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keywordlist:amsKeywordlist {fkBiddingRuleId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to get keyword list of specific campaign type.';

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
        $fkBiddingRuleId = $this->argument('fkBiddingRuleId');
        Log::info("filePath:App\Console\Commands\Ams\Keyword\ListData\listofkeyword. Start Cron.");
        Log::info($this->description);
        $getDataForBiddingRuleCorn = BiddingRule::getDataForBiddingRuleCorn($fkBiddingRuleId);
        if (!empty($getDataForBiddingRuleCorn)) {
            $try = 0;
            foreach ($getDataForBiddingRuleCorn as $single) {
                $clientId = $single->client_id;
                $fkConfigId = $single->fkConfigId;
                if (!empty($clientId)) {
                    $url = ''; // Create a client with a base URI
                    $reportType = '';
                    if ($single->sponsoredType == 'sponsoredProducts') {
                        $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spKeywordList') . '?startIndex=0&campaignType=' . $single->sponsoredType . '&campaignIdFilter=' . $single->campaignId;
                        $reportType = 'SP';
                    } else if ($single->sponsoredType == 'sponsoredBrands') {
                        $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.sbKeywordList') . '?campaignIdFilter=' . $single->campaignId;
                        $reportType = 'SB';
                    } else if ($single->sponsoredType == 'sponsoredDisplay') {
                        $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.sdTargetsList') . '?campaignIdFilter=' . $single->campaignId;
                        $reportType = 'SD';
                    }
                    b:
                    $obaccess_token = new AMSModel();
                    $getAMSTokenById = $obaccess_token->getAMSTokenById($fkConfigId);
                    $accessToken = $getAMSTokenById->access_token;
                    if (!empty($accessToken)) {
                        $client = new Client();
                        $body = array();
                        try {
                            $response = $client->request('GET', $url, [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $accessToken,
                                    'Content-Type' => 'application/json',
                                    'Amazon-Advertising-API-ClientId' => $clientId,
                                    'Amazon-Advertising-API-Scope' => $single->profileId],
                                'delay' => Config::get('constants.delayTimeInApi'),
                                'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                                'timeout' => Config::get('constants.timeoutInApi'),
                            ]);
                            $body = json_decode($response->getBody()->getContents());
                            if (!empty($body)) {
                                BiddingRule::updateBiddingCampaignApiStatus($single->id);
                                $DataArray = array();
                                if ($reportType == 'SD') {
                                    for ($i = 0; $i < count($body); $i++) {
                                        if (isset($body[$i]->bid)) {
                                            $checkExistValue = TargetList::where('fkBiddingRuleId', $single->fkBiddingRuleId)
                                                ->where('targetId', $body[$i]->targetId)
                                                ->get();
                                            if ($checkExistValue->isEmpty()) {
                                                $storeTargetObj = new TargetList();
                                                $storeTargetObj->fkId = $single->id;
                                                $storeTargetObj->fkBiddingRuleId = $single->fkBiddingRuleId;
                                                $storeTargetObj->fkConfigId = $fkConfigId;
                                                $storeTargetObj->profileId = $single->profileId;
                                                $storeTargetObj->campaignId = $single->campaignId;
                                                $storeTargetObj->reportType = $reportType;
                                                $storeTargetObj->adGroupId = $body[$i]->adGroupId;
                                                $storeTargetObj->targetId = $body[$i]->targetId;
                                                $storeTargetObj->state = $body[$i]->state;
                                                $storeTargetObj->bid = $body[$i]->bid;
                                                $storeTargetObj->createdAt = date('Y-m-d H:i:s');
                                                $storeTargetObj->updatedAt = date('Y-m-d H:i:s');
                                                $storeTargetObj->save();
                                            } else {
                                                TargetList::where('fkBiddingRuleId', $single->fkBiddingRuleId)
                                                    ->where('targetId', $body[$i]->targetId)
                                                    ->update(['bid' => $body[$i]->bid, 'updatedAt' => date('Y-m-d H:i:s')]);
                                            }
                                        }// end for loop
                                    }
                                } else {
                                    for ($i = 0; $i < count($body); $i++) {
                                        $storeArray = [];
                                        $storeArray['fkId'] = $single->id;
                                        $storeArray['fkBiddingRuleId'] = $single->fkBiddingRuleId;
                                        $storeArray['fkConfigId'] = $fkConfigId;
                                        $storeArray['profileId'] = $single->profileId;
                                        $storeArray['reportType'] = $reportType;
                                        $storeArray['keywordId'] = $body[$i]->keywordId;
                                        $storeArray['adGroupId'] = $body[$i]->adGroupId;
                                        $storeArray['campaignId'] = $body[$i]->campaignId;
                                        $storeArray['keywordText'] = $body[$i]->keywordText;
                                        $storeArray['matchType'] = $body[$i]->matchType;
                                        $storeArray['state'] = $body[$i]->state;
                                        $storeArray['bid'] = isset($body[$i]->bid) ? $body[$i]->bid : '0.00';
                                        $storeArray['servingStatus'] = isset($body[$i]->servingStatus) ? $body[$i]->servingStatus : 'NA';
                                        $storeArray['creationDate'] = isset($body[$i]->creationDate) ? $body[$i]->creationDate : 'NA';
                                        $storeArray['lastUpdatedDate'] = isset($body[$i]->lastUpdatedDate) ? $body[$i]->lastUpdatedDate : 'NA';
                                        $storeArray['createdAt'] = date('Y-m-d H:i:s');
                                        $storeArray['updatedAt'] = date('Y-m-d H:i:s');
                                        if ($single->campaignId == $body[$i]->campaignId && $reportType == 'SB') {
                                            array_push($DataArray, $storeArray);
                                        } elseif ($single->campaignId == $body[$i]->campaignId && $reportType == 'SP') {
                                            array_push($DataArray, $storeArray);
                                        }
                                    }// end for loop
                                    // store profile list not valid
                                    BiddingRule::storeKeywordData($DataArray);
                                }
                            } else {
                                // if body is empty
                            }
                        } catch (\Exception $ex) {
                            $try += 1;
                            if ($try >= 3) {
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword profile id:' . $single->profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                                $try = 0;
                                continue;
                            }
                            $errorCode = $ex->getCode();
                            $errorMessage = $ex->getMessage();
                            $notificationData = $this->notificationData($single, $errorCode);
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
                                        BiddingRule::inValidProfile($single->id, $single->fkBiddingRuleId, $single->profileId, $single->campaignId);
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
                                        goto b;
                                    }
                                } elseif (strstr($errorMessage, 'advertiser found for scope')) {
                                    // store profile list not valid
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    Log::info("Invalid Profile Id: " . $single->profileId);
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
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword 503. profile id:' . $single->profileId . ' and number try is: ' . $try, '503');
                                sleep(Config::get('constants.sleepTime') + 5);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto b;
                            } else if ($errorCode == 400) {
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword. profile id:' . $single->profileId . ' and number try is: ' . $try, '400');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto b;
                            } else if ($errorCode == 403) {
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword. profile id:' . $single->profileId . ' and number try is: ' . $try, '403');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto b;
                            } else if ($errorCode == 404) {
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword. profile id:' . $single->profileId . ' and number try is: ' . $try, '404');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto b;
                            } else if ($errorCode == 422) {
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword. profile id:' . $single->profileId . ' and number try is: ' . $try, '422');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto b;
                            } else if ($errorCode == 500) {
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword. profile id:' . $single->profileId . ' and number try is: ' . $try, '500');
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto b;
                            } else {
                                AMSModel::insertTrackRecord('Ams\Keyword\ListData\listofkeyword. profile id:' . $single->profileId . ' and number try is: ' . $try, $errorCode);
                                sleep(Config::get('constants.sleepTime') + 2);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                goto b;
                            }
                            Log::error($errorMessage);
                        }// end catch
                    } else {
                        Log::info("AMS access token not found.");
                    }
                } else {
                    Log::info("Client Id not found.");
                }
            }// end foreach
        } else {
            Log::info("bidding rule campaign not found.");
        }
        Log::info("filePath:App\Console\Commands\Ams\Keyword\ListData\listofkeyword. End Cron.");
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function notificationData($single, $errorCode)
    {
        $profileCampaignData = getNotificationProfileCampaignData($single->campaignId);
        $state = "Data availability (bid)";
        $notificationData = [];
        $notificationData['errorType'] = $errorCode;
        $notificationData['moduleName'] = "bidding rule";
        $notificationData['notificationTitle'] = "Bidding Rule Error";
        $notificationData['notificationMessage'] = "Error " . $errorCode . " triggered on state : " . $state;
        $notificationData['fkBiddingRuleId'] = $single->fkBiddingRuleId;
        $notificationData['campaignId'] = $single->campaignId;
        $notificationData['campaignName'] = $profileCampaignData->name;
        $notificationData['fkProfileId'] = $single->fkProfileId;
        $notificationData['state'] = $state;
        $notificationData['sendEmail'] = 1;
        $notificationData['type'] = $errorCode;
        return $notificationData;
    }
}