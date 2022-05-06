<?php

namespace App\Console\Commands\bidMultiplier;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\AMSApiModel;
use App\Models\AMSModel;
use App\Models\BidMultiplierModels\KeywordBidValue;
use App\Models\BidMultiplierModels\Cron as bidMultiplierCronModel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getKeywordData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keywordlist:bidMultiplier {data*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to get keyword BID values.';

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
        $array = $this->argument('data');
        if ($array == null || $array['dataBidMultiplierSP'] == null || $array['cronData'] == null) {
            exit;
        }
        $campaignId = $array['dataBidMultiplierSP']->campaignId;
        $clientId = $array['cronData']->getTokenDetail['client_id'];
        $fkConfigId = $array['cronData']->getTokenDetail['fkConfigId'];
        $profileId = $array['cronData']->profileId;
        $fkMultiplierId = $array['cronData']->fkMultiplierId;
        $sponsoredType = $array['cronData']->sponsoredType;
        $cronId = $array['cronData']->id;
        $frontEnd = $array['frontEnd'];
        $bidValue = str_replace('%', '', $array['dataBidMultiplierSP']->bid);
        $valuePercentage = explode('+', $bidValue);
        $sign = '+';
        if (strpos($bidValue, '-') !== false) {
            $valuePercentage = explode('-', $bidValue);
            $sign = '-';
        }
        $reportType = $array['cronData']->type;
        if ($frontEnd == 1) { // if get value from frontEnd side
            $listOfKeyword = KeywordBidValue::where('fkMultiplierId', $fkMultiplierId)
                ->where('campaignId', $campaignId)
                ->where('isEligible', 1)
                ->get();
            foreach ($listOfKeyword as $singleKeyword) {
                $storeKeywordObj = array();
                $storeKeywordObj['tempBid'] = $this->valueCalculate($singleKeyword->tempBid, $sign, (double)$valuePercentage[1]);
                $storeKeywordObj['updatedAt'] = date('Y-m-d H:i:s');
                KeywordBidValue::where('fkMultiplierId', $fkMultiplierId)
                    ->where('keywordId', $singleKeyword->keywordId)
                    ->update($storeKeywordObj);
            }
            return;
        }
        $url = ''; // Create a client with a base URI
        $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spKeywordList') . '?startIndex=0&campaignType=' . $sponsoredType . '&campaignIdFilter=' . $campaignId;
        $try = 0;
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
            $body = json_decode($response->getBody()->getContents());
            if (!empty($body)) {
                bidMultiplierCronModel::where('id', $cronId)
                    ->update(['isData' => 1, 'updatedAt' => date('Y-m-d H:i:s')]);
                for ($i = 0; $i < count($body); $i++) {
                    if (isset($body[$i]->bid)) {
                        $checkExistValue = KeywordBidValue::where('fkMultiplierId', $fkMultiplierId)
                            ->where('keywordId', $body[$i]->keywordId)
                            ->get();
                        if ($checkExistValue->isEmpty()) {
                            $storeKeywordObj = new KeywordBidValue();
                            $storeKeywordObj->fkMultiplierId = $fkMultiplierId;
                            $storeKeywordObj->fkConfigId = $fkConfigId;
                            $storeKeywordObj->profileId = $profileId;
                            $storeKeywordObj->campaignId = $campaignId;
                            $storeKeywordObj->reportType = $reportType;
                            $storeKeywordObj->adGroupId = $body[$i]->adGroupId;
                            $storeKeywordObj->keywordId = $body[$i]->keywordId;
                            $storeKeywordObj->state = $body[$i]->state;
                            $storeKeywordObj->bid = $body[$i]->bid;
                            $storeKeywordObj->tempBid = $this->valueCalculate($body[$i]->bid, $sign, (double)$valuePercentage[1]);
                            $storeKeywordObj->keywordText = $body[$i]->keywordText;
                            $storeKeywordObj->matchType = $body[$i]->matchType;
                            $storeKeywordObj->servingStatus = isset($body[$i]->servingStatus) ? $body[$i]->servingStatus : 'NA';
                            $storeKeywordObj->creationDate = isset($body[$i]->creationDate) ? $body[$i]->creationDate : 'NA';
                            $storeKeywordObj->lastUpdatedDate = isset($body[$i]->lastUpdatedDate) ? $body[$i]->lastUpdatedDate : 'NA';
                            $storeKeywordObj->isEligible = 0;
                            $storeKeywordObj->createdAt = date('Y-m-d H:i:s');
                            $storeKeywordObj->updatedAt = date('Y-m-d H:i:s');
                            $storeKeywordObj->save();
                        }
                    }// end if
                }// end for loop
            }// end if
        } catch (\Exception $ex) {
            if ($try >= 3) {
                AMSModel::insertTrackRecord('App\Console\Commands\bidMultiplier\keywordList profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                return false;
            }
            $try += 1;
            $errorCode = $ex->getCode();
            $errorMessage = $ex->getMessage();
            $notificationData = $this->notificationData($array, $errorCode, $errorMessage);
            $addNotification = new AmsAlertNotificationsController();
            if ($errorCode == 401) {
                if (strstr($ex->getMessage(), 'Not authorized to access scope')) {
                    // Not authorized to manage this profile. Please check permissions and consent from the advertiser
                    if ($try == 1) {
                        $addNotification->addAlertNotification($notificationData);
                    }
                    Log::info("Invalid Profile Id: " . $profileId . ' and message:' . json_encode($ex->getMessage()));
                } elseif (strstr($ex->getMessage(), 'No matching advertiser found for scope')) {
                    // Could not find an advertiser that matches the provided scope
                    if ($try == 1) {
                        $addNotification->addAlertNotification($notificationData);
                    }
                    Log::info("Invalid Profile Id: " . $profileId . ' and message:' . json_encode($ex->getMessage()));
                } elseif (strstr($ex->getMessage(), '401 Unauthorized')) {
                    $authCommandArray = array();
                    $authCommandArray['fkConfigId'] = $fkConfigId;
                    \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                    if ($try == 2) {
                        $addNotification->addAlertNotification($notificationData);
                    }
                    goto b;
                } elseif (strstr($ex->getMessage(), 'Scope header is missing')) {
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
                goto b;
            } else if ($errorCode == 502) {
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                sleep(Config::get('constants.sleepTime') + 2);
                goto b;
            } else if ($errorCode == 503) {
                if ($try == 1) {
                    $addNotification->addAlertNotification($notificationData);
                }
                AMSModel::insertTrackRecord('App\Console\Commands\bidMultiplier\keywordList 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                sleep(Config::get('constants.sleepTime') + 5);
                goto b;
            }
            // store report status
            Log::error($ex->getMessage());
        }// end catch
    }

    /**
     * @param $originalBidValue
     * @param $sign
     * @param $valuePercentage
     * @return float
     */
    private function valueCalculate($originalBidValue, $sign, $valuePercentage): float
    {
        if ($sign == '+') {
            return round(abs((($valuePercentage / 100) * $originalBidValue) + $originalBidValue), 2);
        }
        return round(abs((($valuePercentage / 100) * $originalBidValue) - $originalBidValue), 2);
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function notificationData($single, $errorCode, $errorMessage)
    {
        $profileId = $single['cronData']->profileId;
        $fkMultiplierId = $single['cronData']->fkMultiplierId;
        $campaignId = $single['dataBidMultiplierSP']->campaignId;
        $getNotificationFkProfileId = getNotificationFkProfileId($profileId);
        $fkProfileId = $getNotificationFkProfileId->id;
        $profileCampaignData = getNotificationProfileCampaignData($campaignId);
        $state = "Bid Multiplier Keyword Data Cron";
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
