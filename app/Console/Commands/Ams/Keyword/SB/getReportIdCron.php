<?php

namespace App\Console\Commands\Ams\Keyword\SB;

use App\Models\ams\ProfileModel;
use App\Models\ams\profileReportStatus;
use App\Models\ams\Report\ReportIdModel;
use App\Models\AMSApiModel;
use App\Models\BatchIdModels\BatchIdModel;
use Artisan;
use DB;
use App\Models\AMSModel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class getReportIdCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getkeywordreportid:sbkeyword {day?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to get SB Keyword ReportId.';

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
        Log::info("filePath:Commands\Ams\Keyword\SB\getReportIdCron. Start Cron.");
        Log::info($this->description);
        $reportType = 'Keyword_SB';
        $day = $this->argument('day');
        $dayMinus = 1;
        $currentDay = 0;
        if (isset($day) && $day != null) {
            $dayMinus = $day;
            $currentDay = (1-$day);
        }
        $reportDateSingleDay = date('Ymd', strtotime('-' . $dayMinus . ' day', time()));
        $currentDayTime = date('Y-m-d', strtotime($currentDay . ' day', time()));
        a:
        $AllProfileID = ProfileModel::with(['getTokenDetail'])
            ->where('isActive', 1)
            ->where('type', '<>', 'agency')
            ->get();
        if ($AllProfileID->isNotEmpty()) {
            $try = 0;
            foreach ($AllProfileID as $single) {
                if ($single->getTokenDetail == null) { // if is null
                    continue;
                }
                $clientId = $single->getTokenDetail->client_id;
                $fkConfigId = $single->getTokenDetail->fkConfigId;
                $existReport = ReportIdModel::where('profileID', $single->profileId)
                    ->where('reportType', $reportType)
                    ->where('reportDate', $reportDateSingleDay)
                    ->get();
                if ($existReport->isNotEmpty()) {
                    continue;
                } // if already report id generated
                // Create a client with a base URI
                $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.HSAKeywordReport');
                $getAccountId['batchId'] = AMSModel::getSpecificAccountId($single->id, 1, $reportDateSingleDay);
                if ($getAccountId['batchId'] == FALSE) {
                    continue; // if account id not found then continue.
                }
                $existReportBatch = profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                    ->where('reportType', 'report_id')
                    ->where('adType', $reportType)
                    ->get();
                if ($existReportBatch->isEmpty()) {
                    profileReportStatus::create([
                        'batchId' => $getAccountId['batchId']->batchId,
                        'profileId' => $single->profileId,
                        'adType' => $reportType,
                        'reportType' => 'report_id',
                        'status' => 0,
                        'error_description' => 'NA'
                    ]);
                } elseif ($existReportBatch[0]['status'] == 5 || $existReportBatch[0]['status'] == 7 || $existReportBatch[0]['status'] == 8 || $existReportBatch[0]['status'] == 9 ) {
                    profileReportStatus::where('id', $existReportBatch[0]['id'])
                        ->update(['status' => 0, 'error_description' => 'NA']);
                } elseif ($existReportBatch[0]['status'] != 0) {
                    continue;
                }
                b:
                $singleAmsApiCreds = AMSApiModel::with('getTokenDetail')->where('id', $fkConfigId)->first();
                $accessToken = $singleAmsApiCreds->getTokenDetail->access_token;
                try {
                    $client = new Client();
                    $response = $client->request('POST', $url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $clientId,
                            'Amazon-Advertising-API-Scope' => $single->profileId],
                        'json' => [
                            'segment' => '',
                            'reportDate' => $reportDateSingleDay,
                            'metrics' => Config::get('constants.sbKeywordMetrics')],
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi'),
                    ]);
                    $body = json_decode($response->getBody()->getContents());
                    if (!empty($body) && $body != null) {
                        $reportId = new ReportIdModel();
                        $reportId->fkBatchId = $getAccountId['batchId']->batchId;
                        $reportId->fkAccountId = $getAccountId['batchId']->fkAccountId;
                        $reportId->profileID = $single->profileId;
                        $reportId->fkConfigId = $fkConfigId;
                        $reportId->reportId = $body->reportId;
                        $reportId->recordType = $body->recordType;
                        $reportId->reportType = $reportType;
                        $reportId->status = $body->status;
                        $reportId->statusDetails = $body->statusDetails;
                        $reportId->reportDate = $reportDateSingleDay;
                        $reportId->creationDate = date('Y-m-d');
                        if ($reportId->save()) {
                            profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                                ->where('reportType', 'report_id')
                                ->where('adType', $reportType)
                                ->update(['status' => 1, 'error_description' => 'NA']);
                        }
                        // store report status
                        AMSModel::insertTrackRecord('report name : ' . $reportType . ' Report Id' . ' profile id: ' . $single->id, 'record found');
                    } else {
                        profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                            ->where('reportType', 'report_id')
                            ->where('adType', $reportType)
                            ->update(['status' => 2, 'error_description' => 'data not found']);
                        // store report status
                        AMSModel::insertTrackRecord('report name : ' . $reportType . ' Report Id' . ' profile id: ' . $single->id, 'not record found');
                    }
                } catch (\Exception $ex) {
                    if ($try >= 3) {
                        $try = 0;
                        continue;
                    }
                    $try += 1;
                    if ($ex->getCode() == 401) {
                        if (strstr($ex->getMessage(), 'Not authorized to access scope')) {
                            profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                                ->where('reportType', 'report_id')
                                ->where('adType', $reportType)
                                ->update(['status' => 3, 'error_description' => $ex->getMessage()]);
                            // Not authorized to manage this profile. Please check permissions and consent from the advertiser
                            Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                        } elseif (strstr($ex->getMessage(), 'No matching advertiser found for scope')) {
                            profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                                ->where('reportType', 'report_id')
                                ->where('adType', $reportType)
                                ->update(['status' => 4, 'error_description' => $ex->getMessage()]);
                            // Could not find an advertiser that matches the provided scope
                            Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                        } elseif (strstr($ex->getMessage(), '401 Unauthorized')) {
                            profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                                ->where('reportType', 'report_id')
                                ->where('adType', $reportType)
                                ->update(['status' => 5, 'error_description' => $ex->getMessage()]);
                            $authCommandArray = array();
                            $authCommandArray['fkConfigId'] = $fkConfigId;
                            \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                            goto b;
                        } elseif (strstr($ex->getMessage(), 'Scope header is missing')) {
                            profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                                ->where('reportType', 'report_id')
                                ->where('adType', $reportType)
                                ->update(['status' => 6, 'error_description' => $ex->getMessage()]);
                            // store profile list not valid
                            Log::info("Invalid Profile Id: " . $single->profileId);
                        }
                    } else if ($ex->getCode() == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                        profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                            ->where('reportType', 'report_id')
                            ->where('adType', $reportType)
                            ->update(['status' => 7, 'error_description' => $ex->getMessage()]);
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 502) {
                        profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                            ->where('reportType', 'report_id')
                            ->where('adType', $reportType)
                            ->update(['status' => 8, 'error_description' => $ex->getMessage()]);
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 500) {
                        profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                            ->where('reportType', 'report_id')
                            ->where('adType', $reportType)
                            ->update(['status' => 9, 'error_description' => $ex->getMessage()]);
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else {
                        profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                            ->where('reportType', 'report_id')
                            ->where('adType', $reportType)
                            ->update(['status' => 10, 'error_description' => $ex->getMessage()]);
                    }
                    if (strstr($ex->getMessage(), 'OpenSSL SSL_connect')) {
                        profileReportStatus::where('batchId', $getAccountId['batchId']->batchId)
                            ->where('reportType', 'report_id')
                            ->where('adType', $reportType)
                            ->update(['status' => 11, 'error_description' => $ex->getMessage()]);
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    }
                    // store report status
                    AMSModel::insertTrackRecord('AdGroup Report Id', 'fail');
                    Log::error($ex->getMessage());
                }// end catch
            }// end foreach
        } else {
            Log::info("Profile not found.");
        }
        $reRunStatus = false;
        $reportBatchWithZero = profileReportStatus::where('reportType', 'report_id')
            ->where('adType', $reportType)
            ->where('created_at', 'like', $currentDayTime . '%')
            ->get();
        if ($reportBatchWithZero->isNotEmpty()) {
            foreach ($reportBatchWithZero as $singleProfile) {
                $errorStatus = $singleProfile['status'];
                $batchResponse = BatchIdModel::where('batchId', $singleProfile['batchId'])
                    ->where('reportDate', $reportDateSingleDay)
                    ->get();
                if ($batchResponse->isNotEmpty() && $batchResponse[0]->batchId != $singleProfile['batchId']) {
                    continue;
                }
                $existReport = ReportIdModel::where('profileID', $singleProfile['profileId'])
                    ->where('reportType', $reportType)
                    ->where('reportDate', $reportDateSingleDay)
                    ->get();
                if ($existReport->isEmpty()) {
                    if ($errorStatus == 0 || $errorStatus == 1) {
                        profileReportStatus::where('id', $singleProfile['id'])
                            ->update(['status' => 0, 'error_description' => 'NA']);
                        $reRunStatus = true;
                    }
                }else{
                    if ($errorStatus == 0 || $errorStatus == 1) {
                        profileReportStatus::where('id', $singleProfile['id'])
                            ->update(['status' => 1, 'error_description' => 'NA']);
                    }
                }
            }// endforeach
            if ($reRunStatus) {
                goto a;
            }
        }
        Log::info("filePath:Commands\Ams\Keyword\SB\getReportIdCron. End Cron.");
    }
}
