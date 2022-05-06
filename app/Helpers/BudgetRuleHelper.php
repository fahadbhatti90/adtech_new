<?php


namespace App\Helpers;


use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\BudgetRuleModels\BudgetRuleCampaignIds;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\AMSModel;

class BudgetRuleHelper
{

    public static function disAssociateCampaigns($budgetRule)
    {

        if (!$budgetRule->budgetRuleDeletedCampaigns->isEmpty()) {

            $profileId = $budgetRule->budgetRuleDeletedCampaigns[0]->profileId;
            $fkConfigId = $budgetRule->budgetRuleDeletedCampaigns[0]->fkConfigId;
            $try = 0;
            b:
            $obAccessToken = new AMSModel();
            $dataAccessTakenData = $obAccessToken->getParameterAndAuthById($fkConfigId);

            if (!empty($dataAccessTakenData)) {
                $ruleId = $budgetRule->ruleId;
                $budgetRuleCampaigns = $budgetRule->budgetRuleDeletedCampaigns;

                foreach ($budgetRuleCampaigns as $removeCampaigns) {
                    $clientId = $dataAccessTakenData->client_id;
                    $accessToken = $dataAccessTakenData->access_token;

                    try {
                        $client = new Client();
                        // Header
                        $headers = [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $clientId,
                            'Amazon-Advertising-API-Scope' => $profileId
                        ];

                        $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.spCampaignUrl') . '/' . $removeCampaigns->campaignId . '/budgetRules/' . $ruleId;

                        $response = $client->request('DELETE', $url, [
                            'headers' => $headers,
                            'delay' => Config::get('constants.delayTimeInApi'),
                            'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                            'timeout' => Config::get('constants.timeoutInApi')
                        ]);

                        $responseBodyDis = json_decode($response->getBody()->getContents());

                        BudgetRuleCampaignIds::where('fkCampaignId', $removeCampaigns->id)
                            ->where('fkRuleId', $budgetRule->id)
                            ->where('shouldRemove', 1)
                            ->delete();

                    } catch (\Exception $ex) {
                        $try += 1;
                        $errorCode = $ex->getCode();
                        $errorMessage = $ex->getMessage();
                        $notificationData = self::notificationData($budgetRule, $errorCode, $errorMessage , $removeCampaigns->campaignId, 'disAssociateCampaigns');
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
                                    goto b;
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
                        } else if ($errorCode == 502) {
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 503) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 503 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 400) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 400 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } elseif ($errorCode == 403) {
                            sleep(Config::get('constants.sleepTime') + 2);
                            Log::info("Budget Rule Forbidden " . json_encode($ex->getMessage()));
                            goto b;
                        } else if ($errorCode == 404) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 404 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 422) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 422 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 500) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 500 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . 'error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        }
                        Log::error($errorMessage);
                    }
                }
            }
        }
    }

    public static function associateCampaigns($budgetRule)
    {
        if (!$budgetRule->budgetRuleCampaigns->isEmpty()) {

            $profileId = $budgetRule->budgetRuleCampaigns[0]->profileId;
            $fkConfigId = $budgetRule->budgetRuleCampaigns[0]->fkConfigId;
            $try = 0;
            b:
            $obAccessToken = new AMSModel();
            $dataAccessTakenData = $obAccessToken->getParameterAndAuthById($fkConfigId);

            if (!empty($dataAccessTakenData)) {
                $ruleId = $budgetRule->ruleId;
                $budgetRuleCampaigns = $budgetRule->budgetRuleCampaigns;
                foreach ($budgetRuleCampaigns as $campaigns) {
                    $clientId = $dataAccessTakenData->client_id;
                    $accessToken = $dataAccessTakenData->access_token;

                    $apiAssociationCampaigns['budgetRuleIds'] = [
                        $ruleId
                    ];

                    try {
                        $client = new Client();
                        // Header
                        $headers = [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $clientId,
                            'Amazon-Advertising-API-Scope' => $profileId
                        ];

                        $url = Config::get('constants.amsApiUrl') . '/' . Config::get('constants.spCampaignUrl') . '/' . $campaigns->campaignId . '/budgetRules';
                        Log::info('Url Budget Rule Association Campaigns  Post Data ' . json_encode($apiAssociationCampaigns));
                        $responseAssociation = $client->request('POST', $url, [
                            'headers' => $headers,
                            'body' => json_encode($apiAssociationCampaigns),
                            'delay' => Config::get('constants.delayTimeInApi'),
                            'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                            'timeout' => Config::get('constants.timeoutInApi')
                        ]);
                        $responseBodyAssociation = json_decode($responseAssociation->getBody()->getContents());
                        if (!empty($responseBodyAssociation)) {
                            // echo '<pre> assoication Function'. $campaigns->campaignId;
                            // print_r($responseBodyAssociation);
                        }
                    } catch (\Exception $ex) {
                        $try += 1;
                        $errorCode = $ex->getCode();
                        $errorMessage = $ex->getMessage();
                        $notificationData = self::notificationData($budgetRule, $errorCode, $errorMessage , $campaigns->campaignId, 'associateCampaigns');
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
                                    goto b;
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
                        } else if ($errorCode == 502) {
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 503) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 503 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 400) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 400 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } elseif ($errorCode == 403) {
                            sleep(Config::get('constants.sleepTime') + 2);
                            Log::info("Budget Rule Forbidden " . json_encode($ex->getMessage()));
                            goto b;
                        } else if ($errorCode == 404) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 404 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 422) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 422 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else if ($errorCode == 500) {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 500 ,error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        } else {
                            Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . 'error description:'.json_encode($ex->getMessage()));
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                        }
                        Log::error($errorMessage);
                    }
                }
            }
        }
    }

    public static function rulePaused($budgetRule)
    {

        if (!empty($budgetRule)) {

            $profileId = $budgetRule->budgetRuleDeletedCampaigns[0]->profileId;
            $fkConfigId = $budgetRule->budgetRuleDeletedCampaigns[0]->fkConfigId;
            $try = 0;
            b:
            $obAccessToken = new AMSModel();
            $dataAccessTakenData = $obAccessToken->getParameterAndAuthById($fkConfigId);

            if (!empty($dataAccessTakenData)) {
                $clientId = $dataAccessTakenData->client_id;
                $accessToken = $dataAccessTakenData->access_token;


                $apiPostDataToSend['budgetRulesDetails'][] = [
                    "ruleState" => 'PAUSED',
                    "ruleId" => $budgetRule->ruleId
                ];

                try {
                    $client = new Client();
                    // Header
                    $headers = [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'Amazon-Advertising-API-ClientId' => $clientId,
                        'Amazon-Advertising-API-Scope' => $profileId
                    ];

                    $url = Config::get('constants.amsApiUrl') . Config::get('constants.fetchSPBudgetRuleList');
                    Log::info('Url Budget Rule Update Post Data ' . json_encode($apiPostDataToSend));
                    $response = $client->request('PUT', $url, [
                        'headers' => $headers,
                        'body' => json_encode($apiPostDataToSend),
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi')
                    ]);
                    $responseBody = json_decode($response->getBody()->getContents());
                    if (!empty($responseBody)) {
                        return $responseBody->responses[0];
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                    $notificationData = self::notificationData($budgetRule, $errorCode, $errorMessage , NULL, 'rulePaused');
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
                                goto b;
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
                    } else if ($errorCode == 502) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 503) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 503 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 400) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 400 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } elseif ($errorCode == 403) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        Log::info("Budget Rule Forbidden " . json_encode($ex->getMessage()));
                        goto b;
                    } else if ($errorCode == 404) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 404 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 422) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 422 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 500) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 500 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . 'error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    }
                    Log::error($errorMessage);
                }
            } else {
                Log::info("AMS client Id or access token not found BudgetRule");
            }
        }
    }

    public static function getRecommendationEvents($requestInput)
    {

        $profileId = $requestInput['profileId'];
        $fkConfigId = $requestInput['configId'];

        b:
        $obAccessToken = new AMSModel();
        $dataAccessTakenData = $obAccessToken->getParameterAndAuthById($fkConfigId);

        if (!empty($dataAccessTakenData)) {
            $clientId = $dataAccessTakenData->client_id;
            $accessToken = $dataAccessTakenData->access_token;

            foreach ($requestInput['selectedCampaigns'] as $campaignId) {
                $apiPostDataToSend = [
                    "campaignId" => $campaignId
                ];
                try {
                    $client = new Client();
                    // Header
                    $headers = [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'Amazon-Advertising-API-ClientId' => $clientId,
                        'Amazon-Advertising-API-Scope' => $profileId
                    ];

                    $url = Config::get('constants.amsApiUrl') . Config::get('constants.getRecommendedEvents');

                    Log::info('Url Budget Rule Recommended Event Post Data ' . json_encode($apiPostDataToSend));
                    $response = $client->request('POST', $url, [
                        'headers' => $headers,
                        'body' => json_encode($apiPostDataToSend),
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi')
                    ]);
                    $responseBody = json_decode($response->getBody()->getContents());
                   // dd($responseBody->recommendedBudgetRuleEvents);
                    if (!empty($responseBody->recommendedBudgetRuleEvents)) {
                        return $responseBody->recommendedBudgetRuleEvents;
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
                                $authCommandArray = array();
                                $authCommandArray['fkConfigId'] = $fkConfigId;
                                Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                                goto b;
                            }
                        } elseif (strstr($ex->getMessage(), 'advertiser found for scope')) {
                            // store profile list not valid
                            Log::info("Invalid Profile Id: " . $profileId);
                        } elseif ($ex->getCode() == 403) {
                            sleep(Config::get('constants.sleepTime') + 2);
                            Log::info("Budget Rule Forbidden " . json_encode($ex->getMessage()));
                            goto b;
                        }
                    }
                }
            }

            return [];
        } else {
            Log::info("AMS client Id or access token not found BudgetRule");
        }
    }


    public static function addRule($budgetRule)
    {
        if (!empty($budgetRule)) {

            $try = 0;
            $profileId = $budgetRule['profileId'];
            $fkConfigId = $budgetRule['configId'];
            $try = 0;
            b:
            $obAccessToken = new AMSModel();
            $dataAccessTakenData = $obAccessToken->getParameterAndAuthById($fkConfigId);

            if (!empty($dataAccessTakenData)) {
                $clientId = $dataAccessTakenData->client_id;
                $accessToken = $dataAccessTakenData->access_token;


                // Making Array to send over PUT Call


                $ruleType = $budgetRule['ruleType'];

                if($ruleType == 'SCHEDULE'){

                    if (!empty($budgetRule['eventName'])){
                        $dateRange['eventTypeRuleDuration'] =[
                            "eventId" =>  $budgetRule['eventId'],
                            "eventName"=> $budgetRule['eventName'],
                            "endDate" =>  date('Ymd', strtotime($budgetRule['endDate'])),
                            "startDate" =>  date('Ymd', strtotime($budgetRule['startDate'])),
                        ];
                    }else{
                        $dateRange["dateRangeTypeRuleDuration"] =  [
                            "endDate" => (!is_null($budgetRule['endDate'])) ? date('Ymd', strtotime($budgetRule['endDate'])) : null,
                            "startDate" => date('Ymd', strtotime($budgetRule['startDate']))
                        ];
                    }

                }else{
                    $dateRange["dateRangeTypeRuleDuration"] =  [
                        "endDate" => (!is_null($budgetRule['endDate'])) ? date('Ymd', strtotime($budgetRule['endDate'])) : null,
                        "startDate" => date('Ymd', strtotime($budgetRule['startDate']))
                    ];
                }

                if (!is_null($budgetRule['daysOfWeek'])) {
                    $days = explode(',', $budgetRule['daysOfWeek']);
                    $rec = [
                        "type" => $budgetRule['recurrence'],
                        "daysOfWeek" => $days
                    ];
                } else {
                    $rec = [
                        "type" => $budgetRule['recurrence']
                    ];
                }

                $apiPostDataToSend['budgetRulesDetails'][] = [
                    "duration" => $dateRange,
                    "recurrence" => $rec,
                    "ruleType" => $budgetRule['ruleType'],
                    "budgetIncreaseBy" => [
                        "type" => "PERCENT",
                        "value" => floatval(number_format($budgetRule['raiseBudget'], 2))
                    ]
                ];

                $apiPostDataToSend['budgetRulesDetails'][0]['name'] = $budgetRule['ruleName'];

                if($ruleType == 'PERFORMANCE'){
                    $apiPostDataToSend['budgetRulesDetails'][0]['name'] = $budgetRule['ruleName'];
                    $apiPostDataToSend['budgetRulesDetails'][0]['performanceMeasureCondition'] = [
                        "metricName" => $budgetRule['metric'],
                        "comparisonOperator" => $budgetRule['comparisonOperator'],
                        "threshold" => floatval(number_format($budgetRule['threshold'], 2)),
                    ];
                }

                try {
                    $client = new Client();
                    // Header
                    $headers = [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'Amazon-Advertising-API-ClientId' => $clientId,
                        'Amazon-Advertising-API-Scope' => $profileId
                    ];

                    $url = Config::get('constants.amsApiUrl') . Config::get('constants.fetchSPBudgetRuleList');
                    Log::info('Url Budget Rule Create Post Data ' . json_encode($apiPostDataToSend));
                    $response = $client->request('POST', $url, [
                        'headers' => $headers,
                        'body' => json_encode($apiPostDataToSend),
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi')
                    ]);
                    $responseBody = json_decode($response->getBody()->getContents());
                    if (!empty($responseBody)) {
                        return $responseBody->responses[0];
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                    $notificationData = self::notificationData($budgetRule, $errorCode, $errorMessage , NULL, 'addRule');
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
                                goto b;
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
                    } else if ($errorCode == 502) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 503) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 503 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 400) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 400 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } elseif ($errorCode == 403) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        Log::info("Budget Rule Forbidden " . json_encode($ex->getMessage()));
                        goto b;
                    } else if ($errorCode == 404) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 404 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 422) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 422 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 500) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 500 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . 'error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    }
                    Log::error($errorMessage);
                }
            } else {
                Log::info("AMS client Id or access token not found BudgetRule");
            }
        }
    }

    private static function setPostData($budgetRule){

        $ruleType = $budgetRule['ruleType'];

        if($ruleType == 'SCHEDULE'){

            if (!empty($budgetRule['eventName'])){
                $dateRange['eventTypeRuleDuration'] =[
                    "eventId" =>  $budgetRule['eventId'],
                    "eventName"=> $budgetRule['eventName'],
                    "endDate" =>  date('Ymd', strtotime($budgetRule['endDate'])),
                    "startDate" =>  date('Ymd', strtotime($budgetRule['startDate'])),
                ];
            }else{
                $dateRange["dateRangeTypeRuleDuration"] =  [
                    "endDate" => (!is_null($budgetRule['endDate'])) ? date('Ymd', strtotime($budgetRule['endDate'])) : null,
                    "startDate" => date('Ymd', strtotime($budgetRule['startDate']))
                ];
            }

        }else{
            $dateRange["dateRangeTypeRuleDuration"] =  [
                "endDate" => (!is_null($budgetRule['endDate'])) ? date('Ymd', strtotime($budgetRule['endDate'])) : null,
                "startDate" => date('Ymd', strtotime($budgetRule['startDate']))
            ];
        }

        if (!is_null($budgetRule['daysOfWeek'])) {
            $days = explode(',', $budgetRule['daysOfWeek']);
            $rec = [
                "type" => $budgetRule['recurrence'],
                "daysOfWeek" => $days
            ];
        } else {
            $rec = [
                "type" => $budgetRule['recurrence']
            ];
        }

        $apiPostDataToSend['budgetRulesDetails'][] = [
            "ruleDetails" => [
                "duration" => $dateRange,
                "recurrence" => $rec,
                "ruleType" => $budgetRule['ruleType'],
                "budgetIncreaseBy" => [
                    "type" => "PERCENT",
                    "value" => floatval(number_format($budgetRule['raiseBudget'], 2))
                ],
            ],
            "ruleId" => $budgetRule['ruleId']
        ];

        $apiPostDataToSend['budgetRulesDetails'][0]["ruleDetails"]['name'] = $budgetRule['ruleName'];

        if($ruleType == 'PERFORMANCE'){
            $apiPostDataToSend['budgetRulesDetails'][0]["ruleDetails"]['name'] = $budgetRule['ruleName'];
            $apiPostDataToSend['budgetRulesDetails'][0]["ruleDetails"]['performanceMeasureCondition'] = [
                "metricName" => $budgetRule['metric'],
                "comparisonOperator" => $budgetRule['comparisonOperator'],
                "threshold" => floatval(number_format($budgetRule['threshold'], 2)),
            ];
        }

        return $apiPostDataToSend;
    }

    public static function updateRule($budgetRule)
    {
        if (!empty($budgetRule)) {

            $profileId = $budgetRule['profileId'];
            $fkConfigId = $budgetRule['configId'];
            $try = 0;
            b:
            $obAccessToken = new AMSModel();
            $dataAccessTakenData = $obAccessToken->getParameterAndAuthById($fkConfigId);

            if (!empty($dataAccessTakenData)) {

                $clientId = $dataAccessTakenData->client_id;
                $accessToken = $dataAccessTakenData->access_token;

                // Making Array to send over PUT Call

                $apiPostDataToSend =  BudgetRuleHelper::setPostData($budgetRule);

                try {
                    $client = new Client();
                    // Header
                    $headers = [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'Amazon-Advertising-API-ClientId' => $clientId,
                        'Amazon-Advertising-API-Scope' => $profileId
                    ];

                    $url = Config::get('constants.amsApiUrl') . Config::get('constants.fetchSPBudgetRuleList');
                    //Log::info('Url Budget Rule Update Post Data ' . json_encode($apiPostDataToSend));
                    $response = $client->request('PUT', $url, [
                        'headers' => $headers,
                        'body' => json_encode($apiPostDataToSend),
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi')
                    ]);
                    $responseBody = json_decode($response->getBody()->getContents());
                    Log::info('Url Budget Rule Update Post Data Response ' . json_encode($responseBody));
                    //dd($responseBody);
                    if (!empty($responseBody)) {
                        return $responseBody->responses[0];
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                    $notificationData = self::notificationData($budgetRule, $errorCode, $errorMessage , NULL, 'updateRule');
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
                                goto b;
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
                    } else if ($errorCode == 502) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 503) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 503 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 400) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 400 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } elseif ($errorCode == 403) {
                        sleep(Config::get('constants.sleepTime') + 2);
                        Log::info("Budget Rule Forbidden " . json_encode($ex->getMessage()));
                        goto b;
                    } else if ($errorCode == 404) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 404 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 422) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 422 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else if ($errorCode == 500) {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . ' and error code is: 500 ,error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    } else {
                        Log::info('app/Helpers/BudgetRuleHelper.php. profile id:' . $profileId . 'error description:'.json_encode($ex->getMessage()));
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                    }
                    Log::error($errorMessage);
                }

            } else {
                Log::info("AMS client Id or access token not found Ams\BudgetRule\updateBudgetRule.");
            }
        }
    }
    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private static function notificationData($data, $errorCode, $errorMessage, $campaignId, $alertType)
    {
        $notificationData = [];

        switch ($alertType) {
            case "associateCampaigns":
                $ruleName = $data->ruleName;
                $fkProfileId = $data->fkProfileId;
                $profileCampaignData = getNotificationProfileCampaignData($campaignId);
                $state = "Budget Multiplier Associate Campaigns";
                $notificationData['type'] = "budgetMultiplierError";
                $notificationData['budgetRuleFunction'] = "Associate Campaigns";
                $notificationData['campaignId'] = $campaignId;
                $notificationData['campaignName'] = $profileCampaignData->name;
                break;
            case "rulePaused":
                $ruleName = $data->ruleName;
                $fkProfileId = $data->fkProfileId;
                $state = "Budget Multiplier Rule Paused";
                $notificationData['type'] = "budgetMultiplierError";
                $notificationData['budgetRuleFunction'] = "Rule Paused";
                break;
            case "updateRule":
                $ruleName = $data['ruleName'];
                $fkProfileId = $data['fkProfileId'];
                $state = "Budget Multiplier Update Rule";
                $notificationData['type'] = "budgetMultiplierError";
                $notificationData['budgetRuleFunction'] = "Update Rule";
                break;
            case "addRule":
                $ruleName = $data['ruleName'];
                $fkProfileId = $data['fkProfileId'];
                $state = "Budget Multiplier Add Rule";
                $notificationData['type'] = "budgetMultiplierError";
                $notificationData['budgetRuleFunction'] = "Add Rule";
                break;
            case "disAssociateCampaigns":
                $ruleName = $data->ruleName;
                $fkProfileId = $data->fkProfileId;
                $profileCampaignData = getNotificationProfileCampaignData($campaignId);
                $state = "Budget Multiplier Disassociate Campaigns";
                $notificationData['type'] = "budgetMultiplierError";
                $notificationData['budgetRuleFunction'] = "Disassociate Campaigns";
                $notificationData['campaignId'] = $campaignId;
                $notificationData['campaignName'] = $profileCampaignData->name;
                break;

            default:
                $state = "Budget Multiplier";
                $notificationData['type'] = $errorCode;
        }
        $notificationData['errorType'] = $errorCode;
        $notificationData['moduleName'] = "Budget Multiplier";
        $notificationData['notificationTitle'] = "Budget Multiplier Error";
        $notificationData['notificationMessage'] = "Error " . $errorCode . " triggered on state : " . $state;
        $notificationData['budgetRuleName'] = $ruleName;
        $notificationData['fkProfileId'] = $fkProfileId;
        $notificationData['state'] = $state;
        $notificationData['sendEmail'] = 1;
        $notificationData['errorMessage'] = $errorMessage;
        return $notificationData;
    }
}