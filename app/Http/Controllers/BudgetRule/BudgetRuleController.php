<?php

namespace App\Http\Controllers\BudgetRule;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\AccountModels\AccountModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ams\campaign\CampaignList;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetRuleModels\BudgetRuleList;
use Illuminate\Support\Facades\DB;
use App\Models\BudgetRuleModels\BudgetRuleCampaignIds;
use Illuminate\Support\Facades\Validator;
use App\Helpers\BudgetRuleHelper;

class BudgetRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BudgetRuleList::where('isActive', 1)
            ->with('budgetRuleCampaigns:id,name,campaignId,profileId,fkConfigId')
            ->whereIn('fkProfileId', $this->getGBSProfiles())
            ->orderBy('id', 'Desc')
            ->get();
    }

    /**
     * @return mixed
     */
    private function getGBSProfiles()
    {
        return AccountModel::where("fkBrandId", getBrandId())
            ->select("id", "fkId")
            ->where("fkAccountType", 1)
            ->get()
            ->map(function ($item, $value) {
                return $item->fkId;
            });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    private function getInputData($request)
    {

        $endDate = $request->input('endDate');

        $mon = ($request->input('mon') == 1) ? "MONDAY," : '';
        $tue = ($request->input('tue') == 1) ? "TUESDAY," : '';
        $wed = ($request->input('wed') == 1) ? "WEDNESDAY," : '';
        $thu = ($request->input('thu') == 1) ? "THURSDAY," : '';
        $fri = ($request->input('fri') == 1) ? "FRIDAY," : '';
        $sat = ($request->input('sat') == 1) ? "SATURDAY," : '';
        $sun = ($request->input('sun') == 1) ? "SUNDAY," : '';

        $daysOfWeek = $mon . $tue . $wed . $thu . $fri . $sat . $sun;
        $daysOfWeek = rtrim($daysOfWeek, ',');

        return [
            'ruleName' => $request->input('ruleName'),
            'eventId' => $request->input('eventId'),
            'eventName' => $request->input('eventName'),
            'fkProfileId' => $request->input('fkProfileId'),
            'profileId' => $request->input('profileId'),
            'configId' => $request->input('configId'),
            'adType' => $request->input('selectedAdType'),
            'ruleType' => $request->input('ruleType'),
            'startDate' => date('Y-m-d', strtotime($request->input('startDate'))),
            'endDate' => (!is_null($endDate)) ? date('Y-m-d', strtotime($endDate)) : null,
            'recurrence' => $request->input('recurrence'),
            'metric' => $request->input('metric'),
            'daysOfWeek' => (!empty($daysOfWeek) && $request->input('recurrence') != 'DAILY') ? $daysOfWeek : null,
            'comparisonOperator' => $request->input('comparisonOperator'),
            'mon' => ($request->input('mon') == 1 && $request->input('recurrence') != 'DAILY') ? 1 : 0,
            'tue' => ($request->input('tue') == 1 && $request->input('recurrence') != 'DAILY') ? 1 : 0,
            'wed' => ($request->input('wed') == 1 && $request->input('recurrence') != 'DAILY') ? 1 : 0,
            'thu' => ($request->input('thu') == 1 && $request->input('recurrence') != 'DAILY') ? 1 : 0,
            'fri' => ($request->input('fri') == 1 && $request->input('recurrence') != 'DAILY') ? 1 : 0,
            'sat' => ($request->input('sat') == 1 && $request->input('recurrence') != 'DAILY') ? 1 : 0,
            'sun' => ($request->input('sun') == 1 && $request->input('recurrence') != 'DAILY') ? 1 : 0,
            'threshold' => $request->input('threshold'),
            'raiseBudget' => $request->input('raiseBudget'),
            'userID' => Auth::user()->id
        ];
    }

    private function validateForm($request)
    {

        $messages = [
            'ruleName.unique' => 'This rule name is already exist.'
        ];
        // Validations
        $validator = Validator::make($request->all(), [
            'ruleName' => 'required|max:50|unique:tbl_ams_budget_rule_list,ruleName',
        ], $messages);

        if ($validator->passes()) {

            $data = $this->getInputData($request);
            $this->sendAlertNotification($data, 'addRuleJobStarted');
            $addResponse = BudgetRuleHelper::addRule($data);
//            $addResponse['code'] = "Ok";
//            $addResponse['details'] = "Budget rule updated";
//            $addResponse['ruleId'] = "6afe127c-ae45-41f2-b1c6-053bb90337f0";
//            $addResponse = (object)$addResponse;

            if (!empty($addResponse->code)) {
                if ($addResponse->code == "Ok") {
                    $data['ruleId'] = $addResponse->ruleId;
                    $data['apiStatus'] = 1;
                    $data['apiMsg'] = json_encode($addResponse->details);
                    $data['createdAt'] = date('Y-m-d h:i:s');
                    $data['updatedAt'] = date('Y-m-d h:i:s');

                    unset($data['profileId']);
                    unset($data['configId']);
                    BudgetRuleList::create($data);
                    $ruleId = DB::getPDO()->lastInsertId();

                    if ($ruleId != null) {

                        $campaigns = $request->input('selectedCampaigns');

                        $campaignStore = [];
                        foreach ($campaigns as $campaign) {
                            $campaignArray = [];
                            $campaignArray['fkCampaignId'] = $campaign;
                            $campaignArray['fkRuleId'] = $ruleId;
                            $campaignArray['createdAt'] = date('Y-m-d h:i:s');
                            array_push($campaignStore, $campaignArray);
                        }
                        BudgetRuleCampaignIds::insert($campaignStore);
                    }

                    unset($data);
                    unset($campaignStore);
                    unset($campaignArray);

                    $budgetRule = BudgetRuleList::where('id', $ruleId)
                        ->where('isActive', 1)
                        ->with('budgetRuleCampaigns:id,name,campaignId,profileId,fkConfigId')
                        ->first();

                    BudgetRuleHelper::associateCampaigns($budgetRule);
                    $this->sendAlertNotification($budgetRule, 'addRuleJobCompleted');
                    $responseData = ['success' => 'Budget Rule has been added successfully!', 'status' => true];
                } else {

                    $responseData = ['error' => [$addResponse->details], 'status' => false];
                }
            } else {
                $responseData = ['error' => ['Please try again...'], 'status' => false];
            }
        } else {
            $responseData = ['error' => $validator->errors()->all(), 'status' => false];
        }

        return $responseData;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        switch ($request->input('recurrence')) {
            case 'WEEKLY':
                if ($request->input('mon') || $request->input('tue') || $request->input('wed') || $request->input('thu') || $request->input('fri') || $request->input('sat') || $request->input('sun')) {

                    $responseData = $this->validateForm($request);
                } else {

                    $responseData = ['error' => ['please select atleast one day of week'], 'status' => false];
                }

                break;
            case 'DAILY':

                $responseData = $this->validateForm($request);

                break;
        }

        return response()->json($responseData);
    }

    private function campaignList($campaignType, $fkProfileId)
    {

        return CampaignList::where('fkProfileId', $fkProfileId)
            ->where('campaignType', $campaignType)
            ->get(['id', 'name', 'campaignId']);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getCampaignList(Request $request)
    {
        $profileId = explode("|", $request->input('fkProfileId'));
        $allCampaigns = $this->campaignList($request->input('campaignType'), $profileId[0]);

        return [
            'text' => $allCampaigns,
            'ajax_status' => true
        ];

    }

    public function getRecommendationEvent(Request $request)
    {
        $response = BudgetRuleHelper::getRecommendationEvents($request->all());
        if (!empty($response)){
            $responseData = ['events' => $response, 'status' => true];
        }else{
            $responseData = ['error' => 'Please try again...', 'status' => false];
        }
        return $responseData;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    private function validateUpdateForm($request)
    {

        $ruleId = $request->input('id');
        $messages = [
            'ruleName.unique' => 'This rule name is already exist.'
        ];
        // Validations
        $validator = Validator::make($request->all(), [
            'ruleName' => 'required|max:50|unique:tbl_ams_budget_rule_list,ruleName,' . $ruleId . ',id,isActive,1',
        ], $messages);
        if ($validator->passes()) {
            $data = $this->getInputData($request);
            $data['ruleId'] = $request->input('ruleId');
            $data['updatedAt'] = date('Y-m-d h:i:s');
            $this->sendAlertNotification($data, 'updateRuleJobStarted');
            $updateResponse = BudgetRuleHelper::updateRule($data);
            if (!empty($updateResponse)) {
                if ($updateResponse->code == "Ok") {
                    unset($data['profileId']);
                    unset($data['ruleId']);
                    unset($data['configId']);
                    BudgetRuleList::where('id', $ruleId)->update($data);

                    $campaigns = $request->input('selectedCampaigns');

                    $campaignStore = [];
                    foreach ($campaigns as $campaign) {
                        $campaignArray = [];
                        $campaignArray['fkCampaignId'] = $campaign;
                        $campaignArray['fkRuleId'] = $ruleId;
                        $campaignArray['createdAt'] = date('Y-m-d h:i:s');
                        array_push($campaignStore, $campaignArray);
                    }

                    if ($request->has('removeCampaigns') && !empty($request->input('removeCampaigns'))) {
                        $removeCampaigns = $request->input('removeCampaigns');
                        BudgetRuleCampaignIds::whereIn('fkCampaignId', $removeCampaigns)
                            ->where('fkRuleId', $ruleId)
                            ->update(['shouldRemove' => 1]);
                    }

                    BudgetRuleCampaignIds::whereIn('fkCampaignId', $campaigns)
                        ->where('fkRuleId', $ruleId)
                        ->delete();
                    BudgetRuleCampaignIds::insert($campaignStore);

                    unset($data);
                    unset($campaignStore);
                    unset($campaignArray);
                    $budgetRule = BudgetRuleList::where('id', $ruleId)
                        ->where('isActive', 1)
                        ->with('budgetRuleCampaigns:id,name,campaignId,profileId,fkConfigId', 'budgetRuleDeletedCampaigns:id,name,campaignId,profileId,fkConfigId')
                        ->first();
                    BudgetRuleHelper::disAssociateCampaigns($budgetRule);
                    BudgetRuleHelper::associateCampaigns($budgetRule);
                    $this->sendAlertNotification($budgetRule, 'updateRuleJobCompleted');
                    $responseData = ['success' => 'Budget Rule has been updated successfully!', 'status' => true];
                } else {
                    $responseData = ['error' => [$updateResponse->details], 'status' => false];
                }
            } else {
                $responseData = ['error' => ['Please try again...'], 'status' => false];
            }

        } else {
            $responseData = ['error' => $validator->errors()->all(), 'status' => false];
        }

        return $responseData;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        switch ($request->input('recurrence')) {
            case 'WEEKLY':
                if ($request->input('mon') || $request->input('tue') || $request->input('wed') || $request->input('thu') || $request->input('fri') || $request->input('sat') || $request->input('sun')) {

                    $responseData = $this->validateUpdateForm($request);
                } else {

                    $responseData = ['error' => ['please select atleast one day of week'], 'status' => false];
                }

                break;
            case 'DAILY':

                $responseData = $this->validateUpdateForm($request);

                break;
        }

        return response()->json($responseData);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        $ruleId = $request->input('id');
        BudgetRuleList::where('id', $ruleId)->update(['isActive' => 0]);
        BudgetRuleCampaignIds::where('fkRuleId', intval($ruleId))->update(['shouldRemove' => 1]);
        $budgetRule = BudgetRuleList::where('id', $ruleId)
            ->with('budgetRuleDeletedCampaigns:id,name,campaignId,profileId,fkConfigId')
            ->first();
        $this->sendAlertNotification($budgetRule, 'pauseRuleJobStarted');
        $pausedResponse = BudgetRuleHelper::rulePaused($budgetRule);
        if (!empty($pausedResponse)) {
            BudgetRuleHelper::disAssociateCampaigns($budgetRule);
        }
        $this->sendAlertNotification($budgetRule, 'pauseRuleJobCompleted');

        return response()->json([
            'status' => true,
            'message' => "Budget Rule has been Deleted Successfully"
        ]);
    }


    /**
     * @return array
     */
    public function getProfileList(Request $request): array
    {

        if ($request->has('campaignType')) {
            $data["profiles"] = getAmsAllProfileList();
            $data["allCampaigns"] = $this->campaignList($request->input('campaignType'), $request->input('fkProfileId'));
            return [$data];
        }
        $data["profiles"] = getAmsAllProfileList();
        $data["allCampaigns"] = [];
        return [$data];
    }
    /**
     *   notificationData
     * @param $singleData
     * @param $type
     * @return $array
     */
    private function sendAlertNotification($data, $type)
    {
        $notificationData = [];
            switch ($type) {
                case "addRuleJobStarted":
                    $ruleName = $data['ruleName'];
                    $fkProfileId = $data['fkProfileId'];
                    $notificationTitle = "Budget Multiplier Job Started.";
                    $notificationMessage = "Budget Multiplier Rule Name: " . $ruleName . " Started";
                    $notificationData['budgetRuleFunction'] = "Add Rule";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    $notificationData['budgetMultiplierCrudSubject'] = "Budget Multiplier Cron Job started.";
                    $notificationData['budgetMultiplierCrudBody'] = "This email notification is to inform you that budget multiplier cron job started.";
                    break;
                case "addRuleJobCompleted":
                    $ruleName = $data['ruleName'];
                    $fkProfileId = $data['fkProfileId'];
                    $notificationTitle = "Budget Multiplier Job Completed.";
                    $notificationMessage = "Budget Multiplier Rule Name: " . $ruleName . " Completed";
                    $notificationData['budgetRuleFunction'] = "Add Rule";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    $notificationData['budgetMultiplierCrudSubject'] = "Budget Multiplier Cron Job Completed.";
                    $notificationData['budgetMultiplierCrudBody'] = "This email notification is to inform you that budget multiplier cron job completed.";
                    break;
                case "updateRuleJobStarted":
                    $ruleName = $data['ruleName'];
                    $fkProfileId = $data['fkProfileId'];
                    $notificationTitle = "Budget Multiplier Job Started.";
                    $notificationMessage = "Budget Multiplier Rule Name: " . $ruleName . " Started";
                    $notificationData['budgetRuleFunction'] = "Update Rule";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    $notificationData['budgetMultiplierCrudSubject'] = "Budget Multiplier Cron Job Started.";
                    $notificationData['budgetMultiplierCrudBody'] = "This email notification is to inform you that budget multiplier cron job started.";
                    break;
                case "updateRuleJobCompleted":
                    $ruleName = $data['ruleName'];
                    $fkProfileId = $data['fkProfileId'];
                    $notificationTitle = "Budget Multiplier Job Completed.";
                    $notificationMessage = "Budget Multiplier Rule Name: " . $ruleName . " Completed";
                    $notificationData['budgetRuleFunction'] = "Update Rule";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    $notificationData['budgetMultiplierCrudSubject'] = "Budget Multiplier Cron Job Completed.";
                    $notificationData['budgetMultiplierCrudBody'] = "This email notification is to inform you that budget multiplier cron job completed.";
                    break;
                case "pauseRuleJobStarted":
                    $ruleName = $data->ruleName;
                    $fkProfileId = $data->fkProfileId;
                    $notificationTitle = "Budget Multiplier Job Started.";
                    $notificationMessage = "Budget Multiplier Rule Name: " . $ruleName . " Started";
                    $notificationData['budgetRuleFunction'] = "Delete Rule";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    $notificationData['budgetMultiplierCrudSubject'] = "Budget Multiplier Cron Job Started.";
                    $notificationData['budgetMultiplierCrudBody'] = "This email notification is to inform you that budget multiplier cron job started.";
                    break;
                case "pauseRuleJobCompleted":
                    $ruleName = $data->ruleName;
                    $fkProfileId = $data->fkProfileId;
                    $notificationTitle = "Budget Multiplier Job Completed.";
                    $notificationMessage = "Budget Multiplier Rule Name: " . $ruleName . " Completed";
                    $notificationData['budgetRuleFunction'] = "Delete Rule";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    $notificationData['budgetMultiplierCrudSubject'] = "Budget Multiplier Cron Job Completed.";
                    $notificationData['budgetMultiplierCrudBody'] = "This email notification is to inform you that budget multiplier cron job completed.";
                    break;
            }
            $notificationData['type'] = "budgetMultiplierCrudEmails";
            $notificationData['moduleName'] = "Budget Multiplier";
            $notificationData['notificationTitle'] = $notificationTitle;
            $notificationData['notificationMessage'] = $notificationMessage;
            $notificationData['budgetRuleName'] = $ruleName;
            $notificationData['fkProfileId'] = $fkProfileId;
            $notificationData['sendEmail'] = 1;
        if (!empty($notificationData)) {
            $addNotification = new AmsAlertNotificationsController();
            $addNotification->addAlertNotification($notificationData);
        }
    }
}
