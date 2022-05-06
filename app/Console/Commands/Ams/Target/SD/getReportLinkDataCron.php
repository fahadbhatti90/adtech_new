<?php

namespace App\Console\Commands\Ams\Target\SD;

use App\Models\ams\Report\Data\Target\SD\TargetsSDDataModel;
use App\Models\ams\Report\Link\Target\SD\TargetsSDModel;
use App\Models\AMSModel;
use Artisan;
use DB;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getReportLinkDataCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gettargetreportlinkdata:sdtargets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to get Report Location Data.';

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
        Log::info("filePath:Commands\Ams\Target\SD\getReportLinkDataCron. Start Cron.");
        Log::info($this->description);
        $errorType = "0";
        Log::info("Message from a $errorType ");
        $AllGetReportsDownloadLink = AMSModel::getSDTargetsDownloadLink();
        if (!empty($AllGetReportsDownloadLink)) {
            foreach ($AllGetReportsDownloadLink as $single) {
                $clientId = $single->client_id;
                $fkConfigId = $single->fkConfigId;
                if (!empty($clientId)) {
                    // Create a client with a base URI
                    $url = $single->location;
                    a:
                    $obaccess_token = new AMSModel();
                    $getAMSTokenById = $obaccess_token->getAMSTokenById($fkConfigId);
                    $accessToken = $getAMSTokenById->access_token;
                    if (!empty($accessToken)) {
                        try {
                            $client = new Client();
                            $response = $client->request('GET', $url, [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $accessToken,
                                    'Amazon-Advertising-API-ClientId' => $clientId,
                                    'Amazon-Advertising-API-Scope' => $single->profileID],
                                'delay' => Config::get('constants.delayTimeInApi'),
                                'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                                'timeout' => Config::get('constants.timeoutInApi'),
                            ]);
                            $body = array();
                            $body = json_decode(gzdecode($response->getBody()->getContents()));
                            if (!empty($body) && $body != null) {
                                $totalNumberOfRecords = count($body);
                                $reportLink = TargetsSDModel::find($single->id);
                                $reportLink->profileID = $single->profileID;
                                $reportLink->reportDate = $single->reportDate;
                                $reportLink->isDone = 1;
                                $reportLink->save();
                                Log::info("Make Array For Data Insertion");
                                for ($i = 0; $i < $totalNumberOfRecords; $i++) {
                                    $reprotData = new TargetsSDDataModel();
                                    $reprotData->fkBatchId = $single->fkBatchId;
                                    $reprotData->fkAccountId = $single->fkAccountId;
                                    $reprotData->fkReportsDownloadLinksId = $single->id;
                                    $reprotData->fkProfileId = $single->profileID;
                                    $reprotData->fkConfigId = $fkConfigId;
                                    $reprotData->campaignId = $body[$i]->campaignId;
                                    $reprotData->adGroupId = $body[$i]->adGroupId;
                                    $reprotData->targetId = $body[$i]->targetId;
                                    $reprotData->targetingText = $body[$i]->targetingText;
                                    $reprotData->campaignName = $body[$i]->campaignName;
                                    $reprotData->adGroupName = $body[$i]->adGroupName;
                                    $reprotData->targetingExpression = isset($body[$i]->targetingExpression) ? $body[$i]->targetingExpression : 'NA';
                                    $reprotData->targetingType = isset($body[$i]->targetingType) ? $body[$i]->targetingType : 'NA';
                                    $reprotData->impressions = $body[$i]->impressions;
                                    $reprotData->clicks = $body[$i]->clicks;
                                    $reprotData->cost = $body[$i]->cost;
                                    $reprotData->currency = $body[$i]->currency;
                                    $reprotData->attributedConversions1d = $body[$i]->attributedConversions1d;
                                    $reprotData->attributedConversions7d = $body[$i]->attributedConversions7d;
                                    $reprotData->attributedConversions14d = $body[$i]->attributedConversions14d;
                                    $reprotData->attributedConversions30d = $body[$i]->attributedConversions30d;
                                    $reprotData->attributedConversions1dSameSKU = $body[$i]->attributedConversions1dSameSKU;
                                    $reprotData->attributedConversions7dSameSKU = $body[$i]->attributedConversions7dSameSKU;
                                    $reprotData->attributedConversions14dSameSKU = $body[$i]->attributedConversions14dSameSKU;
                                    $reprotData->attributedConversions30dSameSKU = $body[$i]->attributedConversions30dSameSKU;
                                    $reprotData->attributedUnitsOrdered1d = $body[$i]->attributedUnitsOrdered1d;
                                    $reprotData->attributedUnitsOrdered7d = $body[$i]->attributedUnitsOrdered7d;
                                    $reprotData->attributedUnitsOrdered14d = $body[$i]->attributedUnitsOrdered14d;
                                    $reprotData->attributedUnitsOrdered30d = $body[$i]->attributedUnitsOrdered30d;
                                    $reprotData->attributedSales1d = $body[$i]->attributedSales1d;
                                    $reprotData->attributedSales7d = $body[$i]->attributedSales7d;
                                    $reprotData->attributedSales14d = $body[$i]->attributedSales14d;
                                    $reprotData->attributedSales30d = $body[$i]->attributedSales30d;
                                    $reprotData->attributedSales1dSameSKU = $body[$i]->attributedSales1dSameSKU;
                                    $reprotData->attributedSales7dSameSKU = $body[$i]->attributedSales7dSameSKU;
                                    $reprotData->attributedSales14dSameSKU = $body[$i]->attributedSales14dSameSKU;
                                    $reprotData->attributedSales30dSameSKU = $body[$i]->attributedSales30dSameSKU;
                                    $reprotData->reportDate = $single->reportDate;
                                    $reprotData->creationDate = date('Y-m-d');
                                    $reprotData->save();
                                }// end for loop
                                // store report status
                                AMSModel::insertTrackRecord('report name : Target SD Report Data' . ' profile id: ' . $single->profileID, 'record found');
                            } else {
                                // store report status
                                AMSModel::insertTrackRecord('report name : Target SD Report Data' . ' profile id: ' . $single->profileID, 'not record found');
                            }
                        } catch (\Exception $ex) {
                            if ($ex->getCode() == 401) {
                                if (strstr($ex->getMessage(), '401 Unauthorized')) { // if auth token expire
                                    if (strstr($ex->getMessage(), 'Not authorized to access scope')) {
                                        // store profile list not valid
                                        Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                                    } elseif (strstr($ex->getMessage(), 'No matching advertiser found for scope')) {
                                        Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                                    } else {
                                        Log::error('Refresh Access token. In file filePath:Commands\Ams\Target\SD\getReportLinkDataCron. End Cron.');
                                        $authCommandArray = array();
                                        $authCommandArray['fkConfigId'] = $fkConfigId;
                                        \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                                        goto a;
                                    }
                                } elseif (strstr($ex->getMessage(), 'advertiser found for scope')) {
                                    // store profile list not valid
                                    Log::info("Invalid Profile Id: " . $single->profileId);
                                }
                            } else if ($ex->getCode() == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                                sleep(Config::get('constants.sleepTime') + 2);
                                goto a;
                            } else if ($ex->getCode() == 502) {
                                sleep(Config::get('constants.sleepTime') + 2);
                                goto a;
                            }
                            // store report status
                            AMSModel::insertTrackRecord('Target SD Report Data', 'fail');
                            Log::error($ex->getMessage());
                        }
                    } else {
                        Log::info("AMS access token not found.");
                    }
                } else {
                    Log::info("Client Id not found.");
                }
            }// end foreach
        } else {
            Log::info("All Get Reports download link not found.");
        }
        Log::info("filePath:Commands\Ams\Target\SD\getReportLinkDataCron. End Cron.");
    }
}
