<?php

namespace App\Http\Controllers;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\DayPartingModels\DayPartingSchedulesTime;
use Illuminate\Http\Request;
use DB;
use Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\DayPartingModels\PfCampaignSchedule;
use App\Models\DayPartingModels\Portfolios;
use App\Models\DayPartingModels\PortfolioAllCampaignList;
use App\Models\DayPartingModels\DayPartingCampaignScheduleIds;
use App\Models\DayPartingModels\DayPartingPortfolioScheduleIds;
use App\Models\AccountModels\AccountModel;
use App\Helpers\DayPartingHelper;
use App\Models\ClientModels\ClientModel;
use App\User;

class DayParting extends Controller
{
    /**
     * dayParting constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getScheduleList(Request $request)
    {
        return PfCampaignSchedule::select('id', 'scheduleName', 'ccEmails', 'portfolioCampaignType', 'startDate', 'endDate',
            'emailReceiptStart', 'emailReceiptEnd', 'isActive', 'fkProfileId', 'isScheduleExpired', 'stopScheduleDate')
            ->where('isActive', 1)
            ->with('expiredCampaigns:id,name,fkScheduleId,portfolioId,enablingPausingStatus', 'expiredPortfolios:id,name,fkScheduleId,portfolioId,enablingPausingStatus', 'campaigns:id,name,fkScheduleId,portfolioId,enablingPausingStatus', 'portfolios:id,name,fkScheduleId,portfolioId,enablingPausingStatus', 'timeCampaigns:fkScheduleId,day,startTime,endTime')
            ->whereIn('fkProfileId', $this->getGBSProfiles())
            ->orderBy('id', 'Desc')
            ->get();
    }//end function

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showScheduleForm()
    {
        $data['pageTitle'] = 'Day Parting schedule';
        $data['pageHeading'] = 'Day Parting schedule';
        $data["brands"] = $this->getActiveBrands();
        return view('subpages.ams.dayparting.day_parting_scheduling')->with($data);
    }

    private function getActiveBrands()
    {
        $getGlobalBrandId = getBrandId();//fetch global brand
        return AccountModel::with("ams")
            ->with("brand_alias")
            ->where('fkAccountType', 1)
            ->where('fkBrandId', $getGlobalBrandId)
            ->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function storeScheduleForm(Request $request): \Illuminate\Http\JsonResponse
    {
        $hourGrid = $request->input('hoursGridSet');
        $hoursData = [];
        $monday = DayPartingHelper::hourSelection($hourGrid['Monday'], 'Monday');
        (!empty($monday)) ? array_push($hoursData, $monday) : '';
        $tuesday = DayPartingHelper::hourSelection($hourGrid['Tuesday'], 'Tuesday');
        (!empty($tuesday)) ? array_push($hoursData, $tuesday) : '';
        $wednesday = DayPartingHelper::hourSelection($hourGrid['Wednesday'], 'Wednesday');
        (!empty($wednesday)) ? array_push($hoursData, $wednesday) : '';
        $thursday = DayPartingHelper::hourSelection($hourGrid['Thursday'], 'Thursday');
        (!empty($thursday)) ? array_push($hoursData, $thursday) : '';
        $friday = DayPartingHelper::hourSelection($hourGrid['Friday'], 'Friday');
        (!empty($friday)) ? array_push($hoursData, $friday) : '';
        $saturday = DayPartingHelper::hourSelection($hourGrid['Saturday'], 'Saturday');
        (!empty($saturday)) ? array_push($hoursData, $saturday) : '';
        $sunday = DayPartingHelper::hourSelection($hourGrid['Sunday'], 'Sunday');
        (!empty($sunday)) ? array_push($hoursData, $sunday) : '';
        $responseData = [];
        $errorMessage = [];
        if (!empty($monday) || !empty($tuesday) || !empty($wednesday) || !empty($thursday) || !empty($friday) || !empty($saturday) || !empty($sunday)) {
            $messages = [
                'scheduleName.unique' => 'This schedule name is already exist.',
                'pfCampaigns.required' => 'Portfolios/Campaigns is required'
            ];
            // Validations
            $validator = Validator::make($request->all(), [
                'scheduleName' => 'required|max:50|unique:tbl_ams_day_parting_pf_campaign_schedules,scheduleName,NULL,NULL,isActive,1',
                // 'pfCampaigns.*' => 'required',
                'pfCampaigns' => 'required|array|min:1',
                //'startTime' => 'required', 'endTime' => 'required'
            ], $messages);

            if ($validator->passes()) {
                $isDatesOverlap = $this->isPfCampaignDateOverLap($request->all(), $hoursData);

                if ($isDatesOverlap['status'] != FALSE) {
                    // making array to store data in DB
                    $dbData = $this->scheduleData($request->all(), $hourGrid);
                    // portfolioCampaignType define schedule is Campaign Or Portfolio
                    $dbData['created_at'] = date('Y-m-d H:i:s');
                    $scheduleId = PfCampaignSchedule::insertPfCampaignSchedule($dbData);
                    $portfolioCampaignType = $request->input('portfolioCampaignType');
                    if (!empty($scheduleId)) {
                        $this->setMultipleScheduleTimings($hoursData, $scheduleId);

                        // Pf Campaign and Portfolio Ids insertion in relation table
                        switch ($portfolioCampaignType) {
                            case 'Campaign':
                            {
                                $pfCampaigns = $request->input('pfCampaigns');
                                $campaignStore = $this->makeCampaignArray($dbData, $pfCampaigns, $scheduleId);

                                DayPartingCampaignScheduleIds::insert($campaignStore);
                                break;
                            }
                            case 'Portfolio':
                            {
                                $allPfIds = [];
                                $pfPortfolio = $request->input('pfCampaigns');
                                foreach ($pfPortfolio as $singPfId) {
                                    $allPortfolios = Portfolios::select('portfolioId')
                                        ->where('id', intval($singPfId))
                                        ->first()->portfolioId;
                                    array_push($allPfIds, $allPortfolios);
                                }

                                $getAllPortfolioCampaign = PortfolioAllCampaignList::select('id', 'name', 'portfolioId')
                                    ->whereIn('portfolioId', $allPfIds)
                                    ->get()->toArray();
                                if (!empty($getAllPortfolioCampaign)) {
                                    $portfolioStore = $this->makePortfolioArray($dbData, $pfPortfolio, $scheduleId);
                                    DayPartingPortfolioScheduleIds::insert($portfolioStore);
                                    $campaignStore = $this->makeCampaignArray($dbData, $getAllPortfolioCampaign, $scheduleId, 'portfolioCampaign');
                                    DayPartingCampaignScheduleIds::insert($campaignStore);
                                } else {
                                    Log::info('Schedule Name = ' . $dbData['scheduleName'] . 'Campaigns Not found against Portfolios selected');
                                }

                                break;
                            }
                        }// Switch Case End
                    }
                    // Send Email
                    $managerEmailArray = $this->getEmailManagers($dbData['fkProfileId']);
                    if (!empty($managerEmailArray)) {
                        _sendEmailForScheduleCreated($dbData, $managerEmailArray);
                        $addNotification = new AmsAlertNotificationsController();
                        $addNotification->_daypartingScheduleCreationNotification($dbData);
                    }
                    unset($dbData);
                    $responseData = ['success' => 'Schedule has been added successfully!', 'ajax_status' => true];

                } else {

                    $finalTimeOverLapArray = [];
                    foreach ($isDatesOverlap as $index => $value) {
                        if (is_int($index)) {
                            $makeArrayForTimeOverLap = [];
                            $makeArrayForTimeOverLap['activatedScheduleName'] = $value['existCampaign']->scheduleName;
                            $makeArrayForTimeOverLap['activatedCampaignName'] = $value['existCampaign']->campaignName;
                            $makeArrayForTimeOverLap['fkScheduleId'] = $value['existCampaign']->fkScheduleId;
                            $makeArrayForTimeOverLap['existTimingsOfCampaign'] = $value['existTimingsOfCampaign'];
                            array_push($finalTimeOverLapArray, $makeArrayForTimeOverLap);
                        }
                    }
                    //array_push($errorMessage, $finalTimeOverLapArray);
                    $responseData = ['error' => $finalTimeOverLapArray, 'timeOverLap' => false, 'ajax_status' => false];
                } // End if else date overlap

            } else {

                $responseData = ['error' => $validator->errors()->all(), 'ajax_status' => false];
            }
        } else {
            array_push($errorMessage, 'Please select atleast one day of week!');
            $responseData = ['error' => $errorMessage, 'ajax_status' => false];
        }

        return response()->json($responseData);
    }//end function

    /**
     * @param $hoursData
     * @param $scheduleId
     */
    function setMultipleScheduleTimings($hoursData, $scheduleId)
    {
        DayPartingSchedulesTime::where('fkScheduleId', intval($scheduleId))->delete();
        $updateArrayHours = [];
        foreach ($hoursData as $singleData) {
            $tempArray = $singleData;
            $tempArray['fkScheduleId'] = intval($scheduleId);
            $tempArray['creationDate'] = date('Y-m-d');
            array_push($updateArrayHours, $tempArray);
        }

        return DayPartingSchedulesTime::insertScheduleTimings($updateArrayHours);
    }

    /**
     * @param $fkProfileId
     * @return array
     */
    function getEmailManagers($fkProfileId): array
    {
        $GetManagerId = AccountModel::where('fkId', $fkProfileId)->where('fkAccountType', 1)->first();
        $brandId = '';
        if (!empty($GetManagerId)) {
            $brandId = $GetManagerId->fkBrandId;
        }

        $managerEmailArray = [];
        if (!empty($brandId) || $brandId != 0) {
            $getBrandAssignedUsers = ClientModel::with("brandAssignedUsersEmails")->find($brandId);
            foreach ($getBrandAssignedUsers->brandAssignedUsersEmails as $getBrandAssignedUser) {
                $brandAssignedUserId = $getBrandAssignedUser->pivot->fkManagerId;
                $GetManagerEmail = User::where('id', $brandAssignedUserId)->first();
                $managerEmailArray[] = $GetManagerEmail->email;
            }
        }
        return $managerEmailArray;
    }

    /**
     * @param $dbData
     * @param $pfCampaigns
     * @param $scheduleId
     * @param null $pffData
     * @return array
     */
    private function makeCampaignArray($dbData, $pfCampaigns, $scheduleId, $pffData = NULL): array
    {

        $campaignArray = [];
        $campaignStore = [];
        foreach ($pfCampaigns as $key => $val) {
            if (is_null($pffData)) {
                $campaignDetail = explode("|", $val);
                $campaignId = $campaignDetail[0];
                $campaignName = $campaignDetail[1];
                $campaignArray['fkCampaignId'] = intval($campaignId);
                $campaignArray['campaignName'] = $campaignName;
            } else {
                $campaignArray['fkCampaignId'] = intval($val['id']);
                $campaignArray['campaignName'] = $val['name'];
            }
            $campaignArray['userSelection'] = 0;
            $campaignArray['enablingPausingTime'] = NULL;
            $campaignArray['enablingPausingStatus'] = NULL;
            $campaignArray['scheduleName'] = $dbData['scheduleName'];
            $campaignArray['fkScheduleId'] = intval($scheduleId);
            array_push($campaignStore, $campaignArray);
        }
        return $campaignStore;
    }//end function

    /**
     * @param $dbData
     * @param $pfCampaigns
     * @param $scheduleId
     * @return array
     */
    private function makePortfolioArray($dbData, $pfCampaigns, $scheduleId, $PfData = NULL): array
    {
        $portfolioArray = [];
        $portfolioStore = [];
        foreach ($pfCampaigns as $key => $val) {
            if (is_null($PfData)) {
                $portfolioDetail = explode("|", $val);
                $portfolioId = $portfolioDetail[0];
                $portfolioName = $portfolioDetail[1];
                $portfolioArray['fkPortfolioId'] = intval($portfolioId);
                $portfolioArray['portfolioName'] = $portfolioName;
            } else {
                $portfolioArray['fkPortfolioId'] = intval($val['id']);
                $portfolioArray['portfolioName'] = $val['name'];
            }
            $portfolioArray['userSelection'] = 0;
            $portfolioArray['enablingPausingTime'] = NULL;
            $portfolioArray['enablingPausingStatus'] = NULL;
            $portfolioArray['fkScheduleId'] = intval($scheduleId);
            $portfolioArray['scheduleName'] = $dbData['scheduleName'];
            array_push($portfolioStore, $portfolioArray);
        }

        return $portfolioStore;
    }//end function

    /**
     * @param $requestInput
     * @return array
     */
    private function scheduleData($requestInput, $hourGrid): array
    {
        $dbData['scheduleName'] = $requestInput['scheduleName'];
        $dbData['fkProfileId'] = $requestInput['fkProfileId'];
        $dbData['fkManagerId'] = Auth::user()->id;
        $dbData['managerEmail'] = Auth::user()->email;
        $dbData['emailReceiptStart'] = (isset($requestInput['emailReceiptStart'])) ? $requestInput['emailReceiptStart'] : 0;
        $dbData['emailReceiptEnd'] = (isset($requestInput['emailReceiptEnd'])) ? $requestInput['emailReceiptEnd'] : 0;
        $dbData['ccEmails'] = (isset($requestInput['ccEmails']) && !is_null($requestInput['ccEmails'])) ? implode(",", $requestInput['ccEmails']) : 'NA';
        $dbData['fkBrandId'] = getBrandId();
        $dbData['portfolioCampaignType'] = $portfolioCampaignType = $requestInput['portfolioCampaignType'];
        $dbData['startDate'] = date('Y-m-d', strtotime($requestInput['startDate']));
        $endDate = $requestInput['endDate'];
        $dbData['endDate'] = (!is_null($endDate)) ? date('Y-m-d', strtotime($endDate)) : null;
        $dbData['selectionHours'] = json_encode($hourGrid);
        $dbData['isActive'] = 1;
        return $dbData;
    }

    /**
     * @param $requestInput
     * @param $chkStartTime
     * @param $chkEndTime
     * @return array|mixed
     */
    private function isPfCampaignDateOverLap($requestInput, $hourGrid, $scheduleId = NULL)
    {
        $newArrayData = [];
        $newArrayData['status'] = TRUE;
        switch ($requestInput['portfolioCampaignType']) {
            case 'Campaign':
            {
                $allCamIds = [];
                $pfCampaigns = $requestInput['pfCampaigns'];
                foreach ($pfCampaigns as $singCampId) {
                    $allCampaigns = PortfolioAllCampaignList::select('id')
                        ->where('id', intval($singCampId))
                        ->first();
                    array_push($allCamIds, $allCampaigns->id);
                }

                if (is_null($scheduleId)) {
                    $existCampaignTime = DayPartingCampaignScheduleIds::select('campaignName', 'scheduleName', 'fkScheduleId')
                        ->whereIn('fkCampaignId', $allCamIds)
                        ->where('enablingPausingStatus', NULL)
                        ->get();
                } else {
                    $existCampaignTime = DayPartingCampaignScheduleIds::select('campaignName', 'scheduleName', 'fkScheduleId')
                        ->whereIn('fkCampaignId', $allCamIds)
                        ->where('fkScheduleId', '!=', intval($scheduleId))
                        ->where('enablingPausingStatus', NULL)
                        ->get();
                }

                if (!$existCampaignTime->isEmpty()) {
                    $newArrayData = [];
                    $isReturn = true;
                    foreach ($existCampaignTime as $index => $singleExistCampaign) {
                        $fkScheduleId = $singleExistCampaign->fkScheduleId;
                        $existTimingsOfCampaign = DayPartingSchedulesTime::where('fkScheduleId', $fkScheduleId)->get();
                        $dateCheckOverlapStatus = DayPartingHelper::checkIfCampaignTimeOverLap($existTimingsOfCampaign, $hourGrid);

                        $dateCheckOverlap['existCampaign'] = $singleExistCampaign;
                        $dateCheckOverlap['existTimingsOfCampaign'] = $existTimingsOfCampaign;
                        array_push($newArrayData, $dateCheckOverlap);
                        if ($isReturn === true && $dateCheckOverlapStatus['status'] === true) {
                            continue;
                        } else {
                            $isReturn = false;
                        }
                    }
                    $newArrayData['status'] = $isReturn;
                }

                break;
            }
            case 'Portfolio':
            {
                $allPfIds = [];
                $pfPortfolio = $requestInput['pfCampaigns'];
                foreach ($pfPortfolio as $singPfId) {
                    $allPortfolios = Portfolios::select('portfolioId')
                        ->where('id', intval($singPfId))
                        ->first();
                    array_push($allPfIds, $allPortfolios->portfolioId);
                }

                if (!empty($allPfIds)) {
                    $getAllPortfolioCampaign = PortfolioAllCampaignList::select('id')
                        ->whereIn('portfolioId', $allPfIds)
                        ->get()->toArray();
                    if (!empty($getAllPortfolioCampaign)) {
                        if (is_null($scheduleId)) {
                            $existCampaignTime = DayPartingCampaignScheduleIds::select('campaignName', 'scheduleName', 'fkScheduleId')
                                ->whereIn('fkCampaignId', $getAllPortfolioCampaign)
                                ->where('enablingPausingStatus', NULL)
                                ->get();
                        } else {
                            $existCampaignTime = DayPartingCampaignScheduleIds::select('campaignName', 'scheduleName', 'fkScheduleId')
                                ->whereIn('fkCampaignId', $getAllPortfolioCampaign)
                                ->where('fkScheduleId', '!=', intval($scheduleId))
                                ->where('enablingPausingStatus', NULL)
                                ->get();
                        }
                        if (!$existCampaignTime->isEmpty()) {
                            $newArrayData = [];
                            $isReturn = true;
                            foreach ($existCampaignTime as $singleExistCampaign) {
                                $fkScheduleId = $singleExistCampaign->fkScheduleId;
                                $existTimingsOfCampaign = DayPartingSchedulesTime::where('fkScheduleId', $fkScheduleId)->get();

                                $dateCheckOverlapStatus = DayPartingHelper::checkIfCampaignTimeOverLap($existTimingsOfCampaign, $hourGrid);

                                if ($isReturn === true && $dateCheckOverlapStatus['status'] === true) {
                                    continue;
                                } else {
                                    $isReturn = false;
                                    $dateCheckOverlap['existCampaign'] = $singleExistCampaign;
                                    $dateCheckOverlap['existTimingsOfCampaign'] = $existTimingsOfCampaign;
                                    array_push($newArrayData, $dateCheckOverlap);
                                }
                            }
                            $newArrayData['status'] = $isReturn;
                        }
                    }
                }
                break;
            }
        }// Switch Case End
        return $newArrayData;
    }//end function

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCampaignPortfolioData(Request $request): \Illuminate\Http\JsonResponse
    {
        $responseData = [];
        if ($request->ajax()) {
            if ($request->has('portfolioCampaignType')) {
                $portfolioCampaignType = $request->input('portfolioCampaignType');
                $profile = explode("|", $request->input('fkProfileId'));

                if (!empty($portfolioCampaignType)) {
                    switch ($portfolioCampaignType) {
                        case "Campaign":
                        {
                            $allCampaigns = PortfolioAllCampaignList::select('id', 'name')
                                ->where('state', '!=', 'archived')
                                //->where('created_at', 'like', '%' . date('Y-m-d') . '%')
                                ->where('fkProfileId', intval($profile[0]))
                                //->whereIn('fkProfileId', $this->getGBSProfiles())
                                ->get();
                            $responseData = ['text' => $allCampaigns, 'ajax_status' => true];
                            break;
                        }
                        case "Portfolio":
                        {
                            $allPortfolios = Portfolios::select('id', 'name', 'portfolioId')
                                ->whereHas('campaigns')
                                ->with('campaigns:id,name,portfolioId')
                                ->where('fkProfileId', intval($profile[0]))
//                                    ->whereIn('fkProfileId', $this->getGBSProfiles())
                                ->get();
                            $responseData = ['text' => $allPortfolios, 'ajax_status' => true];
                            break;
                        }
                        default:
                        {
                            $responseData = ['text' => '', 'ajax_status' => true];
                        }
                    }
                } else {
                    $responseData = ['ajax_status' => false];
                }
            };
            return response()->json($responseData);
        }

    }//end function

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showEditScheduleForm(Request $request)
    {
        if ($request->ajax()) {

            $scheduleId = intval($request->input('scheduleId'));
            $pfCampaignAllDetails = PfCampaignSchedule::where('id', $scheduleId)
                ->select('id', 'scheduleName', 'portfolioCampaignType', 'ccEmails', 'startDate', 'endDate',
                    'emailReceiptStart', 'emailReceiptEnd', 'reccuringSchedule', 'selectionHours', 'fkProfileId')
                ->with('campaigns:id,name', 'portfolios:id,name')
                ->first();
            $pfCampaignAllDetails->selectionHours = json_decode($pfCampaignAllDetails->selectionHours);
            if (!is_null($pfCampaignAllDetails)) {
                switch ($pfCampaignAllDetails->portfolioCampaignType) {
                    case "Campaign":
                    {
                        $allCampaigns = PortfolioAllCampaignList::select('id', 'name')
                            ->where('fkProfileId', $pfCampaignAllDetails->fkProfileId)
                            ->get();
                        break;
                    }
                    case "Portfolio":
                    {
                        $allPortfolios = Portfolios::select('id', 'name', 'portfolioId')
                            ->whereHas('campaigns')
                            ->with('campaigns:id,name,portfolioId')
                            ->where('fkProfileId', $pfCampaignAllDetails->fkProfileId)
                            ->get();
                        break;
                    }
                }
            }
            return [
                'allScheduleData' => $pfCampaignAllDetails,
                'allPortfolios' => (isset($allPortfolios) ? $allPortfolios : ''),
                'allCampaignListRecord' => (isset($allCampaigns) ? $allCampaigns : ''),
                'ajax_status' => true
            ];

        }

    }//end function

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editScheduleForm(Request $request): \Illuminate\Http\JsonResponse
    {

        $hourGrid = $request->input('hoursGridSet');
        $hoursData = [];
        $monday = DayPartingHelper::hourSelection($hourGrid['Monday'], 'Monday');
        (!empty($monday)) ? array_push($hoursData, $monday) : '';
        $tuesday = DayPartingHelper::hourSelection($hourGrid['Tuesday'], 'Tuesday');
        (!empty($tuesday)) ? array_push($hoursData, $tuesday) : '';
        $wednesday = DayPartingHelper::hourSelection($hourGrid['Wednesday'], 'Wednesday');
        (!empty($wednesday)) ? array_push($hoursData, $wednesday) : '';
        $thursday = DayPartingHelper::hourSelection($hourGrid['Thursday'], 'Thursday');
        (!empty($thursday)) ? array_push($hoursData, $thursday) : '';
        $friday = DayPartingHelper::hourSelection($hourGrid['Friday'], 'Friday');
        (!empty($friday)) ? array_push($hoursData, $friday) : '';
        $saturday = DayPartingHelper::hourSelection($hourGrid['Saturday'], 'Saturday');
        (!empty($saturday)) ? array_push($hoursData, $saturday) : '';
        $sunday = DayPartingHelper::hourSelection($hourGrid['Sunday'], 'Sunday');
        (!empty($sunday)) ? array_push($hoursData, $sunday) : '';
        $responseData = [];
        $errorMessage = [];
        if (!empty($monday) || !empty($tuesday) || !empty($wednesday) || !empty($thursday) || !empty($friday) || !empty($saturday) || !empty($sunday)) {
            $messages = [
                'scheduleName.unique' => 'This schedule name is already exist.',
                'pfCampaigns.required' => 'Portfolios/Campaigns is required'
            ];

            $scheduleId = $request->input('scheduleId');
            // Validations
            $validator = Validator::make($request->all(), [
                'scheduleName' => 'required|max:50|unique:tbl_ams_day_parting_pf_campaign_schedules,scheduleName,' . $scheduleId . ',id,isActive,1',
                'pfCampaigns' => 'required|array|min:1'
            ], $messages);
            if ($validator->passes()) {
                $isDatesOverlap = $this->isPfCampaignDateOverLap($request->all(), $hoursData, $scheduleId);

                if ($isDatesOverlap['status'] != FALSE) {
                    // making array to store data in DB
                    $dbData = $this->scheduleData($request->all(), $hourGrid);
                    $dbData['updated_at'] = date('Y-m-d H:i:s');
                    $portfolioCampaignType = $request->input('portfolioCampaignType');
                    if (PfCampaignSchedule::where('id', $scheduleId)->update($dbData)) {
                        $this->setMultipleScheduleTimings($hoursData, $scheduleId);
                        // Pf Campaign and Portfolio Ids insertion in relation table
                        switch ($portfolioCampaignType) {

                            case 'Campaign':
                            {
                                if ($request->has('campaignOptionSelected') && !is_null($request->input('campaignOptionSelected'))) {
                                    $userSelectionStatus = $request->input('campaignOptionSelected');
                                    $timeToPauseCampaign = $this->userSelectionFunction($request->input('campaignOptionSelected'));
                                }

                                // (1) if any campaign is removed from the list so we are updating following records instead,
                                if ($request->has('removeCampaigns') && !is_null($request->input('removeCampaigns'))) {
                                    $removeCampaign = explode(',', $request->input('removeCampaigns'));
                                    foreach ($removeCampaign as $key1 => $val1) {
                                        $campaignDetail = explode("|", $val1);
                                        $campaignId = $campaignDetail[0];
                                        DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
                                            ->where('fkCampaignId', $campaignId)
                                            ->update([
                                                'userSelection' => $userSelectionStatus,
                                                'enablingPausingTime' => $timeToPauseCampaign,
                                                'enablingPausingStatus' => 'deleted'
                                            ]);
                                    }
                                } // End If (1)

                                $pfCampaigns = $request->input('pfCampaigns');
                                $campaignStore = $this->makeCampaignArray($dbData, $pfCampaigns, $scheduleId);

                                if ($request->input('portfolioCampaignEditTypeOldValue') == 'Portfolio') {
                                    $this->EnablePausePreviousPortfolios($scheduleId, $userSelectionStatus, $timeToPauseCampaign);
                                    $this->EnablePausePreviousCampaign($scheduleId, $userSelectionStatus, $timeToPauseCampaign);
                                }
                                // delete previous data
                                foreach ($pfCampaigns as $key1 => $val1) {
                                    $campaignDetail = explode("|", $val1);
                                    $campaignId = $campaignDetail[0];
                                    DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
                                        ->where('fkCampaignId', $campaignId)
                                        ->delete();
                                }
                                // Insert New Record
                                DayPartingCampaignScheduleIds::insert($campaignStore);
                                break;
                            }
                            case 'Portfolio':
                            {
                                if ($request->has('campaignOptionSelected') && !is_null($request->input('campaignOptionSelected'))) {
                                    $userSelectionStatus = $request->input('campaignOptionSelected');
                                    $timeToPauseCampaign = $this->userSelectionFunction($request->input('campaignOptionSelected'));
                                }

                                // Delete Record if it was Campaigns
                                if ($request->input('portfolioCampaignEditTypeOldValue') == 'Campaign') {
                                    DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
                                        ->where('enablingPausingStatus', NULL)
                                        ->update([
                                            'userSelection' => $userSelectionStatus,
                                            'enablingPausingTime' => $timeToPauseCampaign,
                                            'enablingPausingStatus' => 'deleted'
                                        ]);
                                }

                                // (1) if any campaign is removed from the list so we are updating following records instead,
                                if ($request->has('removeCampaigns') && !is_null($request->input('removeCampaigns'))) {
                                    $removePortfolio = explode(',', $request->input('removeCampaigns'));

                                    foreach ($removePortfolio as $key1 => $val1) {
                                        $portfolioDetail = explode("|", $val1);
                                        $portfolioId = $portfolioDetail[0];
                                        DayPartingPortfolioScheduleIds::where('fkScheduleId', $scheduleId)
                                            ->where('fkPortfolioId', $portfolioId)
                                            ->update([
                                                'userSelection' => $userSelectionStatus,
                                                'enablingPausingTime' => $timeToPauseCampaign,
                                                'enablingPausingStatus' => 'deleted'
                                            ]);

                                        $allPortfoliosNeedToDelete = Portfolios::select('portfolioId')
                                            ->where('id', $portfolioId)
                                            ->first()->portfolioId;
                                        $getAllPortfolioCampaignNeedToUpdate = PortfolioAllCampaignList::select('id')
                                            ->where('portfolioId', $allPortfoliosNeedToDelete)
                                            ->get()->toArray();

                                        DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
                                            ->whereIn('fkCampaignId', $getAllPortfolioCampaignNeedToUpdate)
                                            ->update([
                                                'userSelection' => $userSelectionStatus,
                                                'enablingPausingTime' => $timeToPauseCampaign,
                                                'enablingPausingStatus' => 'deleted'
                                            ]);
                                    }


                                } // End If (1)

                                $allPfIds = [];
                                $pfPortfolio = $request->input('pfCampaigns');
                                foreach ($pfPortfolio as $singPfId) {
                                    $allPortfolios = Portfolios::select('portfolioId')
                                        ->where('id', intval($singPfId))
                                        ->first()->portfolioId;
                                    array_push($allPfIds, $allPortfolios);
                                }
                                $getAllPortfolioCampaign = PortfolioAllCampaignList::select('id', 'name', 'portfolioId')
                                    ->whereIn('portfolioId', $allPfIds)
                                    ->get()->toArray();

                                if (!empty($getAllPortfolioCampaign)) {
                                    $portfolioStore = $this->makePortfolioArray($dbData, $pfPortfolio, $scheduleId);

                                    foreach ($pfPortfolio as $key1 => $val1) {
                                        $portfolioDetail = explode("|", $val1);
                                        $portfolioId = $portfolioDetail[0];
                                        DayPartingPortfolioScheduleIds::where('fkScheduleId', $scheduleId)
                                            ->where('fkPortfolioId', $portfolioId)
                                            ->delete();
                                    }
                                    DayPartingPortfolioScheduleIds::insert($portfolioStore);

                                    $campaignStore = $this->makeCampaignArray($dbData, $getAllPortfolioCampaign, $scheduleId, 'portfolioCampaign');
                                    // Delete Previous Records
                                    foreach ($getAllPortfolioCampaign as $keyPfCampaign) {
                                        DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
                                            ->where('fkCampaignId', $keyPfCampaign['id'])
                                            ->delete();
                                    }
                                    DayPartingCampaignScheduleIds::insert($campaignStore);
                                } else {
                                    Log::info('Schedule Name = ' . $dbData['scheduleName'] . 'Campaigns Not found against Portfolios selected');
                                }
                                break;
                            }
                        }// Switch Case End
                    }
                    unset($dbData);
                    $responseData = ['success' => 'Schedule has been updated successfully!', 'ajax_status' => true];
                } else {
                    $finalTimeOverLapArray = [];
                    foreach ($isDatesOverlap as $index => $value) {
                        if (is_int($index)) {
                            $makeArrayForTimeOverLap = [];
                            $makeArrayForTimeOverLap['activatedScheduleName'] = $value['existCampaign']->scheduleName;
                            $makeArrayForTimeOverLap['activatedCampaignName'] = $value['existCampaign']->campaignName;
                            $makeArrayForTimeOverLap['fkScheduleId'] = $value['existCampaign']->fkScheduleId;
                            $makeArrayForTimeOverLap['existTimingsOfCampaign'] = $value['existTimingsOfCampaign'];
                            array_push($finalTimeOverLapArray, $makeArrayForTimeOverLap);
                        }
                    }

                    $responseData = ['error' => $finalTimeOverLapArray, 'timeOverLap' => false, 'ajax_status' => false];
                } // End if else date overlap
            } else {
                $responseData = ['error' => $validator->errors()->all(), 'ajax_status' => false];
            }
        } else {
            array_push($errorMessage, 'Please select atleast one day of week!');
            $responseData = ['error' => $errorMessage, 'ajax_status' => false];
        }

        return response()->json($responseData);
    }//end function

    private function EnablePausePreviousCampaign($scheduleId, $userSelectionStatus, $timeToPauseCampaign): void
    {

        DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
            ->update([
                'userSelection' => $userSelectionStatus,
                'enablingPausingTime' => $timeToPauseCampaign,
                'enablingPausingStatus' => 'deleted'
            ]);
    }

    private function EnablePausePreviousPortfolios($scheduleId, $userSelectionStatus, $timeToPauseCampaign): void
    {
        DayPartingPortfolioScheduleIds::where('fkScheduleId', $scheduleId)
            ->update([
                'userSelection' => $userSelectionStatus,
                'enablingPausingTime' => $timeToPauseCampaign,
                'enablingPausingStatus' => 'deleted'
            ]);
    }

    public function getProfileList(): array
    {
        $data["profiles"] = getAmsAllProfileList();
        return [$data, getBrandId()];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSchedule(Request $request)
    {
        if ($request->ajax()) {
            $scheduleId = $request->input('scheduleId');
            $campaignType = PfCampaignSchedule::select('portfolioCampaignType', 'scheduleName', 'fkProfileId')->where('id', $scheduleId)->first();

            if ($request->has('status')) {
                $userSelectionStatus = $request->input('status');
                $timeToPauseCampaign = $this->userSelectionFunction($userSelectionStatus);
            }
            if (!empty($campaignType)) {
                PfCampaignSchedule::where('id', $scheduleId)
                    ->update([
                        'isActive' => 0,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                switch ($campaignType->portfolioCampaignType) {
                    case 'Campaign':
                    {
                        DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
                            ->where('enablingPausingTime', NULL)
                            ->where('enablingPausingStatus', NULL)
                            ->update([
                                'userSelection' => $userSelectionStatus,
                                'enablingPausingTime' => $timeToPauseCampaign,
                                'enablingPausingStatus' => 'deleted'
                            ]);
                        break;
                    }
                    case 'Portfolio':
                    {
                        DayPartingPortfolioScheduleIds::where('fkScheduleId', $scheduleId)
                            ->where('enablingPausingTime', NULL)
                            ->where('enablingPausingStatus', NULL)
                            ->update([
                                'userSelection' => $userSelectionStatus,
                                'enablingPausingTime' => $timeToPauseCampaign,
                                'enablingPausingStatus' => 'deleted'
                            ]);

                        DayPartingCampaignScheduleIds::where('fkScheduleId', $scheduleId)
                            ->where('enablingPausingTime', NULL)
                            ->where('enablingPausingStatus', NULL)
                            ->update([
                                'userSelection' => $userSelectionStatus,
                                'enablingPausingTime' => $timeToPauseCampaign,
                                'enablingPausingStatus' => 'deleted'
                            ]);
                        break;
                    }
                }// Switch Case End
                //send alert notification
                $addNotification = new AmsAlertNotificationsController();
                $addNotification->_daypartingScheduleDeletionNotification($campaignType);
            }
            return response()->json([
                'status' => true,
                'message' => "Schedule has been Deleted Successfully"
            ]);
        }
    }//end function

    /**
     * @param $userSelectionStatus
     * @return string
     */
    private function userSelectionFunction($userSelectionStatus)
    {
        switch ($userSelectionStatus) {
            // Run today's schedule, then pause
            case '1':
            {
                $timeToPauseCampaign = '23:59:00';
                break;
            }
            // Pause campaigns immediately
            case '2':
            {
                $timeToPauseCampaign = strftime("%H:%M", strtotime(date('H:i') . '+3 minute')) . ':00';
                break;
            }
            // Enable campaigns immediately
            case '3':
            {
                $timeToPauseCampaign = strftime("%H:%M", strtotime(date('H:i') . '+3 minute')) . ':00';
                break;
            }

        }
        return $timeToPauseCampaign;
    }

    private function historyDataFunc($sch, $scheduleDay)
    {

        $pausedManager = ($sch->isActive == 0) ? '(deleted by manager)' : '';
        $description = $sch->scheduleName . ' On ' . date('g:i A', strtotime($sch->startTime)) . ' Off ' . date('g:i A', strtotime($sch->endTime)) . ' ' . $pausedManager;
        $fullDayDesc = $sch->scheduleName . ' full day ' . $pausedManager;
        $finalMessage = ($scheduleDay == 1) ? $description : $fullDayDesc;
        $errorMessage = $sch->cronMessage;
        $scheduleStartDate = date('Y-m-d', strtotime($sch->sDate));
        $scheduleEndDate = date('Y-m-d', strtotime($sch->sDate));
        $todayDate = date('Y-m-d');
        $schedulesArray = [];
        if ($todayDate == $scheduleStartDate && $sch->isCronEnd == 0) {
            return $schedulesArray;
        }
        $cronDay = $sch->schStatus;
        if ($cronDay == 1 || $cronDay == 2) {
            $schedulesArray['start'] = $scheduleStartDate;
            $schedulesArray['end'] = $scheduleEndDate;
            $dayMessage = ($cronDay == 2) ? ' Error' : '';
            $schedulesArray['title'] = $sch->scheduleName . $dayMessage;
            $schedulesArray['description'] = ($cronDay == 1) ? $finalMessage : $errorMessage;
//            if ($scheduleDay === 1) {
//                $schedulesArray['description'] = ($scheduleDay == 1) ? $description : $errorMessage;
//            } elseif ($scheduleDay === 0) {
//                $schedulesArray['description'] = ($scheduleDay == 0) ? $fullDayDesc : $errorMessage;
//            }
            if ($sch->isActive == 1) {
                // Green color
                $schedulesArray['color'] = '#2beba1';
            } elseif ($sch->isActive == 0 && $sch->isScheduleExpired == 1 || $sch->isScheduleExpired == 0) {
                // Brown color
                $schedulesArray['color'] = '#bab86c';
            }

            if ($sch->isScheduleExpired == 1 && $sch->isActive == 1) {
                // Orange color
                $schedulesArray['color'] = 'orange';
            }
            if ($cronDay == 2 && $sch->isActive == 1) {
                // Red color
                $schedulesArray['color'] = '#f6101094';
            }
        }
        return $schedulesArray;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showHistoryForm()
    {
        $data['pageHeading'] = 'Day Parting history';
        $data['pageTitle'] = 'Day Parting history';
        $data["brands"] = $this->getActiveBrands();
        return view('subpages.ams.dayparting.day_parting_history')->with($data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistoryScheduleData(Request $request)
    {
        $schedulesArrayStore = [];
        $schedule = DB::table('tbl_ams_day_parting_pf_campaign_schedules AS campSche')
            ->join('tbl_ams_day_parting_schedule_cron_statuses AS cronStatuses', 'cronStatuses.fkScheduleId', '=', 'campSche.id')
            ->select(array('campSche.fkManagerId', 'campSche.created_at', 'campSche.isCronError',
                'campSche.scheduleName', 'campSche.mon', 'campSche.tue', 'campSche.wed', 'campSche.thu', 'campSche.fri', 'campSche.sat', 'campSche.sun', 'campSche.startTime', 'campSche.endTime', 'campSche.isCronError',
                'campSche.isCronEnd', 'campSche.isActive', 'campSche.isScheduleExpired', 'campSche.created_at', 'campSche.fkBrandId',
                'cronStatuses.fkScheduleId AS fkScheduleId', 'cronStatuses.scheduleDate AS sDate', 'cronStatuses.scheduleStatus AS schStatus', 'cronStatuses.cronMessage'
            ))
            ->where('fkProfileId', $request->input('fkProfileId'))
            ->get();
        foreach ($schedule as $sch) {
            $schedulesArray = [];
            $scheduleStartDate = date('Y-m-d', strtotime($sch->sDate));
            $todayName = strtolower(date('l', strtotime($scheduleStartDate)));

            switch ($todayName) {
                case "monday":
                {
                    $schedulesArray = $this->historyDataFunc($sch, $sch->mon);
                    break;
                }
                case "tuesday":
                {
                    $schedulesArray = $this->historyDataFunc($sch, $sch->tue);
                    break;
                }
                case "wednesday":
                {
                    $schedulesArray = $this->historyDataFunc($sch, $sch->wed);
                    break;
                }
                case "thursday":
                {
                    $schedulesArray = $this->historyDataFunc($sch, $sch->thu);
                    break;
                }
                case "friday":
                {
                    $schedulesArray = $this->historyDataFunc($sch, $sch->fri);
                    break;
                }
                case "saturday":
                {
                    $schedulesArray = $this->historyDataFunc($sch, $sch->sat);
                    break;
                }
                case "sunday":
                {
                    $schedulesArray = $this->historyDataFunc($sch, $sch->sun);
                    break;
                }
            }
            if (!empty($schedulesArray)) {
                array_push($schedulesArrayStore, $schedulesArray);
            }
        }
        return $schedulesArrayStore;
//        return response()->json([
//            'status' => true,
//            'scheduleData' => $schedulesArrayStore
//        ]);
    }

    public function stopSchedule(Request $request)
    {
        if ($request->ajax()) {
            $scheduleId = $request->input('scheduleId');
            PfCampaignSchedule::where('id', $scheduleId)
                ->update([
                    'stopScheduleDate' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            return response()->json([
                'status' => true,
                'message' => "Schedule has been Stopped Successfully"
            ]);
        }
    }

    public function startSchedule(Request $request)
    {
        if ($request->ajax()) {
            $scheduleId = $request->input('scheduleId');
            PfCampaignSchedule::where('id', $scheduleId)
                ->update([
                    'stopScheduleDate' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            return response()->json([
                'status' => true,
                'message' => "Schedule has been Started Successfully"
            ]);
        }
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

}
