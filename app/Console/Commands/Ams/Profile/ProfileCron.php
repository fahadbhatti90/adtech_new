<?php

namespace App\Console\Commands\Ams\Profile;

use App\Models\ams\ProfileModel;
use App\Models\ams\Report\Link\Campaign\SD\CampaignSDModel;
use App\Models\ams\Token\AuthToken;
use App\Models\AMSApiModel;
use Artisan;
use DB;
use App\Models\AMSModel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;


class ProfileCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getprofileid:amsprofile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to get all profile list';

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
     *
     */
    public function handle()
    {
        Log::info("filePath:Commands\Ams\Profile. Start Cron.");
        Log::info($this->description);
        Log::info("AMS Auth token get from DB Start!");
        $getToken = AMSApiModel::with('getTokenDetail')->get();
        if ($getToken->isNotEmpty()) {
            foreach ($getToken as $singleAmsApiCreds) {
                if ($singleAmsApiCreds->getTokenDetail == null) { // if is null
                    continue;
                }
                a:
                $singleAmsApiCreds = AMSApiModel::with('getTokenDetail')->where('id', $singleAmsApiCreds->id)->first();
                $fkConfigId = $singleAmsApiCreds->id;
                try {
                    $apiClient_id = $singleAmsApiCreds->getTokenDetail->client_id;
                    $access_token = $singleAmsApiCreds->getTokenDetail->access_token;
                    // Create a client with a base URI
                    $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.amsProfileUrl');
                    $client = new Client();
                    $response = $client->request('GET', $url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $apiClient_id],
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi'),
                    ]);
                    $body = array();
                    $body = json_decode($response->getBody()->getContents());
                    $totalValue = count($body);
                    if (!empty($body) && $totalValue > 0) {
                        ProfileModel::where('fkConfigId', $fkConfigId)
                            ->update(['isActive' => 0]);
                        for ($i = 0; $i < $totalValue; $i++) {
                            $existProfile = ProfileModel::where('profileID', $body[$i]->profileId)->get();
                            if ($existProfile->isEmpty()) {
                                $profileObject = new ProfileModel();
                                $profileObject->profileId = $body[$i]->profileId;
                                $profileObject->countryCode = $body[$i]->countryCode;
                                $profileObject->currencyCode = $body[$i]->currencyCode;
                                $profileObject->timezone = $body[$i]->timezone;
                                $profileObject->marketplaceStringId = (isset($body[$i]->accountInfo->marketplaceStringId) ? $body[$i]->accountInfo->marketplaceStringId : 'NA');
                                $profileObject->entityId = (isset($body[$i]->accountInfo->id) ? $body[$i]->accountInfo->id : 'NA');
                                $profileObject->type = (isset($body[$i]->accountInfo->type) ? $body[$i]->accountInfo->type : 'NA');
                                $profileObject->name = (isset($body[$i]->accountInfo->name) ? $body[$i]->accountInfo->name : 'NA');
                                $profileObject->adGroupSpSixtyDays = 0; //0
                                $profileObject->aSINsSixtyDays = 0; //0
                                $profileObject->campaignSpSixtyDays = 0;
                                $profileObject->keywordSbSixtyDays = 0;
                                $profileObject->keywordSpSixtyDays = 0;
                                $profileObject->productAdsSixtyDays = 0;
                                $profileObject->productTargetingSixtyDays = 0;
                                $profileObject->SponsoredBrandCampaignsSixtyDays = 0;
                                $profileObject->SponsoredDisplayCampaignsSixtyDays = 0;
                                $profileObject->SponsoredDisplayAdgroupSixtyDays = 0;
                                $profileObject->SponsoredDisplayProductAdsSixtyDays = 0;
                                $profileObject->SponsoredBrandAdgroupSixtyDays = 0;
                                $profileObject->SponsoredBrandTargetingSixtyDays = 0;
                                $profileObject->SponsoredDisplayTargetSixtyDays = 0;
                                $profileObject->creationDate = date('Y-m-d H:i:s');
                                $profileObject->isSandboxProfile = 0;
                                $profileObject->isActive = 1;
                                $profileObject->fkConfigId = $fkConfigId;
                                $profileObject->save();
                            } else {
                                ProfileModel::where('profileId', $existProfile[0]->profileId)
                                    ->update(['name' => (isset($body[$i]->accountInfo->name) ? $body[$i]->accountInfo->name : 'NA'), 'isActive' => 1]);
                            }//end if else
                        }//end foreach
                    } else {
                        // store status
                        AMSModel::insertTrackRecord('Profile Id', 'not record found');
                    }
                } catch (\Exception $ex) {
                    if ($ex->getCode() == 401) {
                        if (strstr($ex->getMessage(), '401 Unauthorized')) { // if auth token expire
                            Log::error('Refresh Access token. In file filePath:Commands\Ams\Profile');
                            $authCommandArray = array();
                            $authCommandArray['fkConfigId'] = $fkConfigId;
                            \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                            goto a;
                        } elseif (strstr($ex->getMessage(), 'advertiser found for scope')) {
                            // store profile list not valid
                            Log::info("Invalid Profile Id: ");
                        }
                    } else if ($ex->getCode() == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                        sleep(Config::get('constants.sleepTime'));
                        goto a;
                    }
                    // store status
                    AMSModel::insertTrackRecord('Profile Id', 'fail');
                    Log::error($ex->getMessage());
                }
            }
        } else {
            Log::info("AMS access token not found.");
        }
        Log::info("filePath:Commands\Ams\Profile. End Cron.");
    }
}
