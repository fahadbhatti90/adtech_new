<?php

namespace App\Libraries\AmsAlertNotifications;

use App\Events\SendNotification;
use App\Mail\BuyBoxEmailAlertMarkdown;
use App\Models\AccountModels\AccountModel;
use App\Models\ClientModels\ClientModel;
use App\User;
use App\Models\amsAlerts\amsAlerts;
use App\Libraries\BroadCastNotification;
use Illuminate\Support\Facades\Log;

class AmsAlertNotificationsController
{
    /**
     * @param $scheduleData
     * Type Array
     * @return bool
     */
    public function addAlertNotification($scheduleData)
    {
        Log::info("filePath:app\Libraries\AmsAlertNotifications\AmsAlertNotifications.php. Add Notifications.");
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 0);
        $fkProfileId = $scheduleData['fkProfileId'];
        $moduleName = $scheduleData['moduleName'];
        $sendEmail = $scheduleData['sendEmail'];
        $getAccountBrand = $this->_getAccountBrand($fkProfileId);
        if (!empty($getAccountBrand)) {
            $accountBrand = $getAccountBrand->fkBrandId;
            $accountId = $getAccountBrand->id;
            $accountManagers = $this->_brandAssignedUsers($accountBrand);
            $alertCount = checkNotificationAlertExists($accountId, $moduleName);
            $alertDetails = $this->getAlertDetails($accountId);
            if ($alertCount && $alertCount > 0 && !empty($accountManagers) && !empty($alertDetails)) {
                Log::info("filePath:app\Libraries\AmsAlertNotifications\AmsAlertNotifications.php.Alert Name: " . $alertDetails->alertName . " and AccountId: " . $accountId);
                $scheduleData['alertName'] = $alertDetails->alertName;
                $addCC = [];
                $ccString = trim($alertDetails->addCC);
                if (!empty($ccString)) {
                    $addCC = explode(',', $ccString);
                }
                $notiDetails = $this->_getNotificationDetail($scheduleData);

                $managerIdsArray = $accountManagers['managerIds'];
                $managerEmailsArray = $accountManagers['managerEmails'];
                $notificationTitle = $scheduleData['notificationTitle'];
                $notificationMessage = $scheduleData['notificationMessage'];

                $DBnoti = array(
                    "type" => 3,
                    "title" => $notificationTitle,
                    "message" => $notificationMessage,
                    "details" => json_encode($notiDetails),
                    "fkAccountId" => $accountId,
                    "created_at" => date("Y-m-d H:i"),
                );
                $broadCaster = new BroadCastNotification();
                $addedNotification = $broadCaster->SaveNotificationInDatabase($DBnoti);

                if ($addedNotification != null) {
                    $DBnoti["id"] = $addedNotification->id;
                    $DBnoti["host"] = getHostForNoti();
                    $broadCaster->BroadcastNotificationsToUsers($DBnoti, $managerIdsArray, false);
                }
                if ($sendEmail == 1) {
                    $emailContent = $this->_notificationEmailContent($scheduleData);
                    $emailContent['email'] = $managerEmailsArray;
                    $emailContent['addCC'] = $addCC;
                    $sendEmail = $this->_sendNotificationEmailAlert($emailContent);
                }
            }
        }
        return true;
    }

    /**
     * @param $emailContent
     * Type Array
     * @return array
     */
    private function _sendNotificationEmailAlert($emailContent)
    {
        $usersEmail = $emailContent['email'];
        $addCC = $emailContent['addCC'];
        $emailSubject = $emailContent['emailSubject'];
        $emailBody = $emailContent['emailBody'];
        $bodyHTML = ((new BuyBoxEmailAlertMarkdown('', $emailBody))->render());
        $data = [];
        $data["toEmails"] = $usersEmail;
        if (!empty($addCC)) {
            $data["cc"] = $addCC;
        }
        $data["subject"] = $emailSubject;
        $data["bodyHTML"] = $bodyHTML;
        return SendMailViaPhpMailerLib($data);
    }//end function

    /**
     *   _notificationEmailContent
     * @param $scheduleData
     * @return $emailContent
     */
    private function _notificationEmailContent($data)
    {
        $errorType = $data['type'];
        $emailContent = [];
        $messages = [];
        switch ($errorType) {
            case "biddingRuleCronJobStarted":
                $subject = "Bidding Rule Cron Job Started.";
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>This email notification is to inform you that bidding rule cron job started.</p>";
                $messages[] = "<p>Bidding Rule Name: " . $data['biddingRuleName'] . "</p>";
                $messages[] = "<p>Start Time: " . $data['time'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "biddingRuleCronJobCompleted":
                $subject = "Bidding Rule Cron Job Completed.";
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>This email notification is to inform you that bidding rule cron job completed.</p>";
                $messages[] = "<p>Bidding Rule Name : " . $data['biddingRuleName'] . "</p>";
                $messages[] = "<p>End Time: " . $data['time'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "tacosCronJobStarted":
                $subject = "Tacos Cron Job Started.";
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>This email notification is to inform you that tacos cron job started.</p>";
                $messages[] = "<p>Tacos Id: " . $data['fkTacosId'] . "</p>";
                $messages[] = "<p>Start Time: " . $data['time'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "tacosCronJobCompleted":
                $subject = "Tacos Cron Job Completed.";
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>This email notification is to inform you that tacos cron job completed.</p>";
                $messages[] = "<p>Tacos Id: " . $data['fkTacosId'] . "</p>";
                $messages[] = "<p>End Time: " . $data['time'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "dayPartingScheduleDeletion":
                $subject = "Schedule Deleted Successfully.";
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>This email notification is to inform you that schedule is deleted successfully.</p>";
                $messages[] = "<p>Schedule Name: " . $data['scheduleName'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "budgetMultiplierCrudEmails":
                $subject = $data['budgetMultiplierCrudSubject'];
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>".$data['budgetMultiplierCrudBody']."</p>";
                $messages[] = "<p>Rule Name: " . $data['budgetRuleName'] . "</p>";
                $messages[] = "<p>Time: " . $data['time'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "budgetMultiplierError":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Budget Multiplier Rule : " . $data['budgetRuleName'] . "  triggered a " . ($data['errorType']) . " error.</p>";
                $messages[] = "<p>Rule Name : " . $data['budgetRuleName'] . ". </p>";
                $messages[] = "<p>Budget Rule : " . $data['budgetRuleFunction'] . ". </p>";
                if (isset($data['campaignId'])){
                $messages[] = "<p>Campaign Id : " . $data['campaignId'] . ". </p>";
                }
                if (isset($data['campaignName'])){
                $messages[] = "<p>Campaign Name : " . $data['campaignName'] . ". </p>";
                }
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "400":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign will be resumed once we have investigated further. Please wait for another email to confirm once this situation has been resolved</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "401":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign will be resumed once we have investigated further. Another email will be sent to once this situation has been resolved.</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "403":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign will be resumed once we have investigated further. Another email will be sent to once this situation has been resolved.</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "404":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign does not have access to [INSERT SPECIFICED RESOURCE]. Please adjust your campaign here [INSERT LINK HERE]. 
                Another email will be sent to once this situation has been resolved.</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "422":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign does not contain the correct parameters. 
                Please adjust your campaign here [INSERT LINK HERE]. Another email will be sent to once this situation has been resolved.</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "500":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign was limited due something went wrong on the server. Please wait a while and try again later. 
                If the error still persists, please report to us [INSERT LINKE OF WHERE TO REPORT] here.</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "429":
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " Error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign was limited due to too many requests. Please wait a while and try again later. 
                If the error still persists, please report to us [INSERT LINKE OF WHERE TO REPORT] here.</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "bidMultiplierCronJobStarted":
                $subject = "Bid Multiplier Cron Job Started.";
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>This email notification is to inform you that bid multiplier cron job started.</p>";
                $messages[] = "<p>Bid Multiplier Id: " . $data['fkMultiplierId'] . "</p>";
                $messages[] = "<p>Start Time: " . $data['time'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            case "bidMultiplierCronJobCompleted":
                $subject = "Bid Multiplier Cron Job Completed.";
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>This email notification is to inform you that bid multiplier cron job completed.</p>";
                $messages[] = "<p>Bid Multiplier Id: " . $data['fkMultiplierId'] . "</p>";
                $messages[] = "<p>Start Time: " . $data['time'] . "</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
                break;
            default:
                $subject = "[NO ACTION REQUIRED] " . ($data['errorType']) . " error in " . $data['moduleName'];
                $messages = array();
                $messages[] = "<p><b>Hello </b></p>";
                $messages[] = "<p>Your ad campaign " . ($data['campaignName']) . "  triggered a " . ($data['errorType']) . " error. 
                Your campaign will be resumed once we have investigated further. Please wait for another email to confirm once this situation has been resolved</p>";
                $messages[] = "<p>If you have any other questions or concerns, please contact us.</p>";
                $messages[] = "<p>Sincerely,</p>";
        }
        $emailContent['emailSubject'] = $subject;
        $emailContent['emailBody'] = $messages;
        return $emailContent;
    }

    /**
     *   _getAccountBrand
     * @param $fkProfileId
     * @return $amsAccounts
     */
    private function _getAccountBrand($fkProfileId)
    {
        $amsAccounts = AccountModel::select("id", "fkBrandId")
            ->where("fkId", $fkProfileId)->where("fkAccountType", 1)
            ->first();
        return $amsAccounts;
    }

    /**
     *   _brandAssignedUsers
     * @param $accountBrand
     * @return $data
     */
    private function _brandAssignedUsers($accountBrand)
    {
        $brandData = ClientModel::with("brandAssignedUsersEmails")->where('id', $accountBrand)->first();
        $assignedUsers = $brandData->brandAssignedUsersEmails;
        $assignedUsersArray = [];
        if (!empty($assignedUsers)) {
            foreach ($assignedUsers as $assignedUser) {
                $assignedUsersArray[] = $assignedUser->id;
            }
        }
        $users = User::whereIn('id', $assignedUsersArray)->where('deleted_at', NULL)->get();
        $usersEmailArray = [];
        foreach ($users as $values) {
            $userEmail = $values->email;
            array_push($usersEmailArray, $userEmail);
        }
        $data = [];
        if (in_array(2, $assignedUsersArray)) {
            //add null to broadcast notification to admin
            $assignedUsersArray[] = "null";
        }
        $data['managerIds'] = $assignedUsersArray;
        $data['managerEmails'] = $usersEmailArray;
        return $data;
    }

    /**
     *   _getNotificationDetail
     * @param $scheduleData
     * @return $notiDetails
     */
    private function _getNotificationDetail($scheduleData)
    {
        $notiDetails = array();
        if (isset($scheduleData['alertName'])) $notiDetails["Alert Name"] = $scheduleData['alertName'];
        if (isset($scheduleData['scheduleId'])) $notiDetails["Schedule Id"] = $scheduleData['scheduleId'];
        if (isset($scheduleData['scheduleName'])) $notiDetails["Schedule Name"] = $scheduleData['scheduleName'];
        if (isset($scheduleData['fkBudgetRuleId'])) $notiDetails["Budget Rule Id"] = $scheduleData['fkBudgetRuleId'];
        if (isset($scheduleData['budgetRuleName'])) $notiDetails["Budget Rule Name"] = $scheduleData['budgetRuleName'];
        if (isset($scheduleData['fkBiddingRuleId'])) $notiDetails["Bidding Rule Id"] = $scheduleData['fkBiddingRuleId'];
        if (isset($scheduleData['biddingRuleName'])) $notiDetails["Bidding Rule Name"] = $scheduleData['biddingRuleName'];
        if (isset($scheduleData['fkTacosId'])) $notiDetails["Tacos Id"] = $scheduleData['fkTacosId'];
        if (isset($scheduleData['fkMultiplierId']) && !empty(trim($scheduleData['fkMultiplierId']))) $notiDetails["Bid Multiplier Id"] = $scheduleData['fkMultiplierId'];
        if (isset($scheduleData['campaignId'])) $notiDetails["Campaign Id"] = $scheduleData['campaignId'];
        if (isset($scheduleData['campaignName'])) $notiDetails["Campaign Name"] = $scheduleData['campaignName'];
        if (isset($scheduleData['state'])) $notiDetails["State"] = $scheduleData['state'];
        if (isset($scheduleData['errorType'])) $notiDetails["Error Type"] = $scheduleData['errorType'];
        if (isset($scheduleData['errorMessage']) && !empty(trim($scheduleData['errorMessage']))) $notiDetails["Error Message"] = $scheduleData['errorMessage'];
        if (isset($scheduleData['time']) && !empty(trim($scheduleData['time']))) $notiDetails["Time"] = $scheduleData['time'];

        return $notiDetails;
    }

    /**
     *   getAccountsArray
     * @param $accountId
     * @return $accounts
     */
    private function getAccountsArray($accountId)
    {
        $accounts = [
            $accountId => []
        ];
        return $accounts;
    }

    /**
     *   getAlertDetails
     * @param $accountId
     * @return $alertDetails
     */
    private function getAlertDetails($accountId)
    {
        $alertDetails = amsAlerts::select('alertName', 'addCC')->where('fkAccountId', $accountId)->first();
        return $alertDetails;
    }

    /**
     * @param scheduleData
     * Type Array
     * @return bool
     */
    public function _daypartingScheduleCreationNotification($data)
    {
        $notificationData = [];
        $notificationData['type'] = "dayPartingScheduleCreation";
        $notificationData['moduleName'] = "day parting";
        $notificationData['notificationTitle'] = "Day Parting Schedule Created";
        $notificationData['notificationMessage'] = "Day Parting schedule name: " . $data['scheduleName'] . " created successfully";
        $notificationData['fkProfileId'] = $data['fkProfileId'];
        $notificationData['scheduleName'] = $data['scheduleName'];
        $notificationData['sendEmail'] = 0;
        $sendNotification = $this->addAlertNotification($notificationData);
        return $sendNotification;
    }

    /**
     * @param scheduleData
     * Type Array
     * @return bool
     */
    public function _daypartingScheduleDeletionNotification($data)
    {
        $notificationData = [];
        $notificationData['type'] = "dayPartingScheduleDeletion";
        $notificationData['moduleName'] = "day parting";
        $notificationData['notificationTitle'] = "Day Parting Schedule Deleted";
        $notificationData['notificationMessage'] = "Day Parting schedule name: " . $data->scheduleName . " deleted successfully";
        $notificationData['fkProfileId'] = $data->fkProfileId;
        $notificationData['scheduleName'] = $data->scheduleName;
        $notificationData['sendEmail'] = 1;
        $sendNotification = $this->addAlertNotification($notificationData);
        return $sendNotification;
    }
}