<?php


namespace App\Helpers;


use App\Models\AccountModels\AccountModel;
use App\Models\AMSModel;
use App\Models\ClientModels\ClientModel;
use App\Models\DayPartingModels\PortfolioAllCampaignList;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DayPartingHelper
{

    public static function hourSelection($hourGrid, $dayName): array
    {
        $dayName = strtolower($dayName);
        $isDayChecked = $hourGrid['isChecked'];
        $data['startTime'] = date('g:i A', strtotime('00:00:00'));
        $data['endTime'] = date('g:i A', strtotime('23:59:59'));
        if ($isDayChecked != FALSE) {
            //$data[$dayName.'StartTime'] = $dayName;
            $data['day'] = $dayName;
            return $data;
        }
        $data = [];
        $hours = $hourGrid['hours'];
        // get selected hours start
        $getHoursSelected = '';
        $unSelectedHours = '';
        for ($i = 0; $i <= count($hours) - 1; $i++) {
            if ($hours[$i] === TRUE) {
                $getHoursSelected .= $i . ',';
            } else {
                $unSelectedHours .= $i . ',';
            }
        }

        if ($getHoursSelected != '') {
            $missingHours = explode(",", $unSelectedHours);
            $missingHours = array_filter($missingHours, function ($value) {
                return ($value !== '');
            });

            // now we need to get selected
            $orderHours = '';
            for ($r = 0; $r <= count($hours) - 1; $r++) {
                $minutes = ($r < 10) ? '0' . $r . ':00:00,' : $r . ':00:00,';
                $previousNumber = $r - 1;
                if (in_array($r, $missingHours)) {
                    if (in_array($previousNumber, $missingHours)) {
                        continue;
                    }
                    $orderHours .= '|';
                    continue;
                }
                $orderHours .= $minutes;
            }

            $finalHours = array_filter(explode("|", $orderHours));
            $minIndexFinalHours = min(array_keys($finalHours));
            $maxIndexFinalHours = max(array_keys($finalHours));

            $startTime = '';
            $endTime = '';

            for ($f = $minIndexFinalHours; $f <= $maxIndexFinalHours; $f++) {
                if (isset($f)) {
                    // Remove Empty value index from Array
                    $explodeHours = array_filter(explode(",", $finalHours[$f]));
                    // get Min Index Of Array
                    $minIndexArray = min(array_keys($explodeHours));
                    // get Max Index Of Array
                    $maxIndexArray = max(array_keys($explodeHours));

                    $startTime .= date('g:i A', strtotime($explodeHours[$minIndexArray])) . ',';
                    $forFirstSelection = date('g:i A', strtotime('00:59:59')) . ',';
                    $addSecondToString = date('g:i A', strtotime(str_replace("00", "59", $explodeHours[$maxIndexArray]))) . ',';
                    $newVariable = ($explodeHours[$maxIndexArray] == '00:00:00') ? $forFirstSelection : $addSecondToString;

                    $endTime .= $newVariable;
                }

            }
            $data['startTime'] = removeCommaFromLast($startTime);
            $data['endTime'] = removeCommaFromLast($endTime);
            //$data[$dayName.'StartTime'] = $dayName;
            $data['day'] = $dayName;
        }
        return $data;
    }

    public static function getEmailManagers($fkProfileId): array
    {
        $GetManagerId = AccountModel::where('fkId', $fkProfileId)->where('fkAccountType', 1)->first();
        $brandId = '';
        if (!empty($GetManagerId)) {
            $brandId = $GetManagerId->fkBrandId;
        }

        $managerEmailArray = [];
        if (!empty($brandId) || $brandId != 0) {
            $getBrandAssignedUsers = ClientModel::with("brandAssignedUsers")->find($brandId);
            foreach ($getBrandAssignedUsers->brandAssignedUsers as $getBrandAssignedUser) {
                $brandAssignedUserId = $getBrandAssignedUser->pivot->fkManagerId;
                $GetManagerEmail = User::where('id', $brandAssignedUserId)->first();
                $managerEmailArray[] = $GetManagerEmail->email;
            }
        }
        return $managerEmailArray;
    }

    public static function checkTimingForEachDay($startActualTime, $endActualTime, $newStartActualTime, $newEndActualTime): array
    {
        $startTime = $startActualTime;
        $endTime = $endActualTime;
        $chkStartTime = $newStartActualTime;
        $chkEndTime = $newEndActualTime;
        $dateCheckOverlapStatus['status'] = TRUE;
        //$errorMessageToShowOnScreen = $campaignName . ' already has an active schedule ' . $scheduleName . ' is active at ' . date('g:i A', strtotime($startTime)) . ' AND ' . date('g:i A', strtotime($endTime)) . ' on ' . $dayName . '. Please adjust your new schedule and try again.';
        if ($chkStartTime > $startTime && $chkEndTime < $endTime) {
            #-> Check time is in between start and end time
            $dateCheckOverlapStatus['status'] = FALSE;
            // $dateCheckOverlapStatus['message'] = $errorMessageToShowOnScreen;
        } elseif (($chkStartTime > $startTime && $chkStartTime < $endTime) || ($chkEndTime > $startTime && $chkEndTime < $endTime)) {
            #-> Check start or end time is in between start and end time
            $dateCheckOverlapStatus['status'] = FALSE;
            //$dateCheckOverlapStatus['message'] = $errorMessageToShowOnScreen;
        } elseif ($chkStartTime == $startTime || $chkEndTime == $endTime) {
            #-> Check start or end time is at the border of start and end time
            $dateCheckOverlapStatus['status'] = FALSE;
            // $dateCheckOverlapStatus['message'] = $errorMessageToShowOnScreen;
        } elseif ($startTime > $chkStartTime && $endTime < $chkEndTime) {
            #-> start and end time is in between  the check start and end time.
            $dateCheckOverlapStatus['status'] = FALSE;
            //$dateCheckOverlapStatus['message'] = $errorMessageToShowOnScreen;
        } else {
            $dateCheckOverlapStatus['status'] = TRUE;
            //$dateCheckOverlapStatus['message'] = 'success';
        }

        return $dateCheckOverlapStatus;
    }

    public static function checkIfCampaignTimeOverLap($existCampaigns, $newRequestedCampaigns): array
    {

        $result = [];
        $result['status'] = true;
        $isReturn = true;
        foreach ($existCampaigns as $checkCampaign) {
            $dayName = $checkCampaign->day;

            foreach ($newRequestedCampaigns as $newR) {
                if ($dayName === $newR['day']) {
                    $existStartTime = $checkCampaign->startTime;
                    $existEndTime = $checkCampaign->endTime;

                    if (strlen($existStartTime) > 8 && strlen($existEndTime) > 8) {

                        $explodeStartTiming = explode(',', $existStartTime);
                        $explodeEndTiming = explode(',', $existEndTime);
                        $count = count($explodeStartTiming);
                        for ($i = 0; $i < $count; $i++) {

                            $startActualTime = date("H:i:s", strtotime($explodeStartTiming[$i]));
                            $endActualTime = date("H:i:s", strtotime($explodeEndTiming[$i]));
                            $newStartTime = $newR['startTime'];
                            $newEndTime = $newR['endTime'];
                            if (strlen($newStartTime) > 8 && strlen($newEndTime) > 8) {
                                $newExplodeStartTiming = explode(',', $newStartTime);
                                $newExplodeEndTiming = explode(',', $newEndTime);
                                $newCount = count($newExplodeStartTiming);
                                for ($j = 0; $j < $newCount; $j++) {

                                    $newStartActualTime = date("H:i:s", strtotime($newExplodeStartTiming[$j]));
                                    $newEndActualTime = date("H:i:s", strtotime($newExplodeEndTiming[$j]));

                                    $result = DayPartingHelper::checkTimingForEachDay($startActualTime, $endActualTime, $newStartActualTime, $newEndActualTime);

                                    if ($result['status'] === true) {
                                        continue;
                                    } else {
                                        $isReturn = false;
                                        break;
                                    }
                                }
                            } else {
                                $newStartActualTime = date("H:i:s", strtotime($newStartTime));
                                $newEndActualTime = date("H:i:s", strtotime($newEndTime));
                                $result = DayPartingHelper::checkTimingForEachDay($startActualTime, $endActualTime, $newStartActualTime, $newEndActualTime);

                                if ($result['status'] === true) {
                                    continue;
                                } else {
                                    $isReturn = false;
                                    break;
                                }
                            }
                        }

                    } else {
                        $startActualTime = date("H:i:s", strtotime($existStartTime));
                        $endActualTime = date("H:i:s", strtotime($existEndTime));
                        $newStartTime = $newR['startTime'];
                        $newEndTime = $newR['endTime'];
                        if (strlen($newStartTime) > 8 && strlen($newEndTime) > 8) {
                            $newExplodeStartTiming = explode(',', $newStartTime);
                            $newExplodeEndTiming = explode(',', $newEndTime);
                            $newCount = count($newExplodeStartTiming);
                            for ($i = 0; $i < $newCount; $i++) {
                                $newStartActualTime = date("H:i:s", strtotime($newExplodeStartTiming[$i]));
                                $newEndActualTime = date("H:i:s", strtotime($newExplodeEndTiming[$i]));
                                $result = DayPartingHelper::checkTimingForEachDay($startActualTime, $endActualTime, $newStartActualTime, $newEndActualTime);

                                if ($result['status'] === true) {
                                    continue;
                                } else {
                                    $isReturn = false;
                                    break;
                                }
                            }
                        } else {

                            $newStartActualTime = date("H:i:s", strtotime($newStartTime));
                            $newEndActualTime = date("H:i:s", strtotime($newEndTime));
                            $result = DayPartingHelper::checkTimingForEachDay($startActualTime, $endActualTime, $newStartActualTime, $newEndActualTime);

                            if ($result['status'] === true) {
                                continue;
                            } else {
                                $isReturn = false;
                                break;
                            }
                        }
                    }
                }
            }
        }
        $result['status'] = $isReturn;
        return $result;

    }
}