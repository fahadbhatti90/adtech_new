<?php

namespace App\Console\Commands\Ams\Keyword\SB;

use App\Models\ams\ProfileModel;
use App\Models\ams\Report\Link\AmsFailedReportsLinks;
use App\Models\ams\Report\Link\Campaign\SP\CampaignSPModel;
use App\Models\ams\Report\Link\Keyword\SB\KeywordSBModel;
use App\Models\ams\Report\ReportIdModel;
use App\Models\AMSApiModel;
use Artisan;
use DB;
use App\Models\AMSModel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getReportLinkCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getkeywordreportlink:sbkeyword {day?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to get Keyword SB report link location.';

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
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("filePath:Commands\Ams\Keyword\SB\getReportLinkCron. Start Cron.");
        Log::info($this->description);
        $reportType = 'Keyword_SB';
        // get Specific Report Type ID
        $day = $this->argument('day');
        $dayMinus = 1;
        if (isset($day) && $day != null) {
            $dayMinus = $day;
        }
        $reportDateSingleDay = date('Ymd', strtotime('-' . $dayMinus . ' day', time()));
        $profileListObject = new ProfileModel();
        $AllProfileID = $profileListObject->getProfileListForLink($reportType, $reportDateSingleDay);
        if ($AllProfileID->isNotEmpty()) {
            foreach ($AllProfileID as $single) {
                if ($single->getTokenDetail == null || $single->getReportId == null) { // if is null
                    continue;
                }
                $clientId = $single->getTokenDetail->client_id;
                $fkConfigId = $single->getTokenDetail->fkConfigId;
                $getReportId = $single->getReportId->id;
                $profileID = $single->getReportId->profileID;
                $existReport = KeywordSBModel::where('profileID', $profileID)
                    ->where('reportDate', $reportDateSingleDay)
                    ->get();
                if ($existReport->isNotEmpty()) {
                    ReportIdModel::updateReportIdStatus($getReportId, $profileID, $reportType, $reportDateSingleDay, 1);
                    continue;
                } // if already report id generated
                // Create a client with a base URI
                a:
                $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.downloadReport');
                $singleAmsApiCreds = AMSApiModel::with('getTokenDetail')->where('id', $fkConfigId)->first();
                $accessToken = $singleAmsApiCreds->getTokenDetail->access_token;
                try {
                    $client = new Client();
                    $response = $client->request('GET', $url . '/' . $single->getReportId->reportId, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $clientId,
                            'Amazon-Advertising-API-Scope' => $profileID],
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi'),
                    ]);
                    $body = json_decode($response->getBody()->getContents());
                    if (!empty($body) && $body != null) {
                        ReportIdModel::updateReportIdStatus($getReportId, $profileID, $reportType, $reportDateSingleDay, 1);
                        Log::info("Make Array For Data Insertion");
                        $reportLinkStatus = $body->status;
                        $batchId = $single->getReportId->fkBatchId;
                        switch ($reportLinkStatus) {
                            case "FAILURE":
                                Log::info('Report Link Failed.Report Type:' . $reportType . 'Batch Id:' . $batchId);
                                deleteAmsFailedLinkReportId($reportType, $batchId);
                                $reportLink = new AmsFailedReportsLinks();
                                $reportLink->reportType = $reportType;
                                $reportLink->location = 'not available';
                                $reportLink->fileSize = 'not available';
                                $reportLink->isDone = 3; // not find URL
                                break;
                            default:
                                $reportLink = new KeywordSBModel();
                                if (isset($body->location)) {
                                    $reportLink->location = $body->location;
                                } else {
                                    ReportIdModel::updateReportIdStatus($getReportId, $profileID, $reportType, $reportDateSingleDay, 0);
                                    goto a;
                                }
                                $reportLink->isDone = 0;
                                $reportLink->fileSize = $body->fileSize;
                                if ($body->fileSize == 22) {
                                    $reportLink->isDone = 2; // FILE SIZE is 22 because its empty not record found
                                }
                        }
                        $reportLink->fkBatchId = $batchId;
                        $reportLink->fkAccountId = $single->getReportId->fkAccountId;
                        $reportLink->profileID = $profileID;
                        $reportLink->fkConfigId = $fkConfigId;
                        $reportLink->reportId = $body->reportId;
                        $reportLink->status = $body->status;
                        $reportLink->statusDetails = $body->statusDetails;
                        $reportLink->expiration = isset($body->expiration) ? date("M/d/Y H:i:s", substr($body->expiration, 0, 10)) : 'NA';
                        $reportLink->reportDate = $reportDateSingleDay;
                        $reportLink->creationDate = date('Y-m-d');
                        $reportLink->save();
                        // store report status
                        AMSModel::insertTrackRecord('report name : Keyword SB Report Link' . ' profile id: ' . $single->profileId, 'record found');
                    } else {
                        // store report status
                        AMSModel::insertTrackRecord('report name : Keyword SB Report Link' . ' profile id: ' . $single->profileId, 'not record found');
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
                                Log::error('Refresh Access token. In file filePath:Commands\Ams\Keyword\SB\getReportLinkCron');
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
                    AMSModel::insertTrackRecord('Keyword SB Report Link', 'fail');
                    Log::error($ex->getMessage());
                }//end catch
            }// end foreach
        } else {
            Log::info("All Get Reports download link not found.");
        }
        Log::info("filePath:Commands\Ams\Keyword\SB\getReportLinkCron. End Cron.");
    }
}
