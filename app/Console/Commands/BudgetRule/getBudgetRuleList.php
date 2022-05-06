<?php

namespace App\Console\Commands\BudgetRule;

use App\Models\ams\ProfileModel;
use App\Models\AMSApiModel;
use App\Models\AMSModel;
use App\Models\BudgetRuleModels\BudgetRuleList;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class getBudgetRuleList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getAllBudgetRules:Budget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to fetch all budget rules from amazon Api';

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

        $allProfileIds = ProfileModel::with(['getTokenDetail'])
            ->where('isActive', 1)
            ->where('type', '<>', 'agency')
            ->get();

        if ($allProfileIds->isNotEmpty()) {

            $try = 0;
            foreach ($allProfileIds as $single) {
                if ($single->getTokenDetail == null) { // if is null
                    continue;
                }

                $clientId = $single->getTokenDetail->client_id;
                $fkConfigId = $single->getTokenDetail->fkConfigId;
                $profileId = $single->profileId;

                $url = Config::get('constants.amsApiUrl') . Config::get('constants.fetchSPBudgetRuleList');
                Log::info('Url = ' . $url);
                b:
                $singleAmsApiCred = AMSApiModel::with('getTokenDetail')->where('id', $fkConfigId)->first();
                $accessToken = $singleAmsApiCred->getTokenDetail->access_token;

                try {


                    $client = new Client();

                    $response = $client->request('GET', $url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $clientId,
                            'Amazon-Advertising-API-Scope' => $profileId],
                        'query' => ['pageSize' => '30'],
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi'),
                    ]);
                    $responseBody = json_decode($response->getBody()->getContents());

                    if (!empty($responseBody) && $responseBody != null) {

                        foreach ($responseBody->budgetRulesForAdvertiserResponse as $single) {

                            $singleArray = [];
                            $singleArray['ruleId'] = $ruleId = $single->ruleId;
                            $existingBRules = BudgetRuleList::where('ruleId', $ruleId)->get();
                            if (!$existingBRules->isEmpty()) {
                                $singleArray['createdAt'] = date('Y-m-d h:i:s');
                                $singleArray['updatedAt'] = date('Y-m-d h:i:s');
                                $singleArray['createdDate'] = $single->createdDate;
                                $singleArray['lastUpdatedDate'] = $single->lastUpdatedDate;
                                $singleArray['ruleState'] = $single->ruleState;
                                $singleArray['ruleStatus'] = $single->ruleStatus;
                                $singleArray['ruleStatusDetails'] = $single->ruleStatusDetails;
                                $ruleDetails = $single->ruleDetails;
                                if (isset($ruleDetails)) {
                                    $singleArray['ruleName'] = $ruleDetails->name;
                                    $singleArray['ruleType'] = $ruleDetails->ruleType;
                                    if (isset($ruleDetails->budgetIncreaseBy)) {
                                        //$singleArray['type'] = $ruleDetails->budgetIncreaseBy->type;
                                        $singleArray['raiseBudget'] = $ruleDetails->budgetIncreaseBy->value;
                                    }
                                    if (isset($ruleDetails->duration->dateRangeTypeRuleDuration)) {
                                        $singleArray['startDate'] = $ruleDetails->duration->dateRangeTypeRuleDuration->startDate;
                                        $singleArray['endDate'] = $ruleDetails->duration->dateRangeTypeRuleDuration->endDate;
                                    }
                                    if (isset($ruleDetails->performanceMeasureCondition)) {
                                        $singleArray['comparisonOperator'] = $ruleDetails->performanceMeasureCondition->comparisonOperator;
                                        $singleArray['metric'] = $ruleDetails->performanceMeasureCondition->metricName;
                                        $singleArray['threshold'] = $ruleDetails->performanceMeasureCondition->threshold;
                                    }
                                    if (isset($ruleDetails->recurrence)) {
                                        $singleArray['recurrence'] = $ruleDetails->recurrence->type;
                                        $singleArray['daysOfWeek'] = $ruleDetails->recurrence->daysOfWeek;
                                    }
                                }

                                BudgetRuleList::where('ruleId', $ruleId)->update($singleArray);
                            }
                        }
                    }
                } catch (\Exception $ex) {

                    if ($try >= 3) {
                        AMSModel::insertTrackRecord('Budget Rule profile id:' . $profileId . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                        $try = 0;
                        continue;
                    }

                    if ($ex->getCode() == 401) {
                        if (strstr($ex->getMessage(), '401 Unauthorized')) { // if auth token expire
                            if (strstr($ex->getMessage(), 'Not authorized to access scope')) {
                                // store profile list not valid
                                Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                            } elseif (strstr($ex->getMessage(), 'No matching advertiser found for scope')) {
                                Log::info("Invalid Profile Id: " . json_encode($ex->getMessage()));
                            } else {
                                $authCommandArray = array();
                                $authCommandArray['fkConfigId'] = $fkConfigId;
                                Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                                goto b;
                            }
                        } elseif (strstr($ex->getMessage(), 'advertiser found for scope')) {
                            // store profile list not valid
                            Log::info("Invalid Profile Id: " . $profileId);
                        }
                    } else if ($ex->getCode() == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 502) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        goto b;
                    } else if ($ex->getCode() == 503) {
                        AMSModel::insertTrackRecord('Budget Multiplier 503. profile id:' . $profileId . ' and number try is: ' . $try, '503');
                        sleep(Config::get('constants.sleepTime') + 5);
                        goto b;
                    }
                }
            }
        }

    }
}
