<?php

namespace App\Http\Controllers\amsAlertsModule;

use App\Models\amsAlerts\amsAlerts;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\AccountModels\AccountModel;
use App\Models\Vissuals\VissualsProfile;
use Illuminate\Support\Facades\Validator;

class amsAlertsModule extends Controller
{
    /********* Add alerts ********/
    /**
     * @param $request
     * @return array
     */
    public function viewAlerts(Request $request)
    {


        $data["alerts"] = amsAlerts::with("accounts:id,fkId")
            ->with("accounts.brand_alias:fkAccountId,overrideLabel")
            ->with("accounts.amsChildBrandData")->orderBy('id', 'desc')->get()
            ->map(function ($item, $index) {
                return [
                    "serial" => $index + 1,
                    "id" => $item->id,
                    "alertName" => $item->alertName,
                    "fkAccountId" => $item->fkAccountId,
                    "fkProfileId" => $item->fkProfileId,
                    "dayPartingAlertsStatus" => $item->dayPartingAlertsStatus,
                    "biddingRuleAlertsStatus" => $item->biddingRuleAlertsStatus,
                    "tacosAlertsStatus" => $item->tacosAlertsStatus,
                    "bidMultiplierAlertsStatus" => $item->bidMultiplierAlertsStatus,
                    "budgetMultiplierAlertsStatus" => $item->budgetMultiplierAlertsStatus,
                    "addCC" => $item->addCC,
                    "createdBy" => $item->createdBy,
                    "created_at" => $item->created_at,
                    "updated_at" => $item->updated_at,
                    "fkAccountId" => $item->accounts ? $item->accounts->id : null,
                    "accounts" => $item->accounts ?
                        count($item->accounts->brand_alias) > 0 &&
                        isset($item->accounts->brand_alias[0]->overrideLabel) != null ?
                            $item->accounts->brand_alias[0]->overrideLabel : (isset($item->accounts->amsChildBrandData[0]) ? $item->accounts->amsChildBrandData[0]->name : "No Profile Name Found") : "No Account Found",
                ];
            });
        return $data;
    }

    /********* Add alerts ********/
    /**
     * @param $request
     * @return array
     */
    public function addAlert(Request $request)
    {
        $messages = [
            'alertName.unique' => 'This alert name is already exist.'
        ];
        // Validations
        $validator = Validator::make($request->all(), [
            'alertName' => 'required|max:50|unique:tbl_ams_alerts,alertName',
        ], $messages);

        if ($validator->passes()) {

            $data = $this->alertData($request->all());
            $data['createdBy'] = auth()->user()->id;
            $data['created_at'] = date('Y-m-d h:i:s');
            $data['updated_at'] = date('Y-m-d h:i:s');

            amsAlerts::create($data);
            unset($data);
            $responseData = ['success' => 'Alert has been added successfully!', 'ajax_status' => true];
        }else{

            $responseData = ['error' => 'This alert name is already exist', 'ajax_status' => false];
        }

        return response()->json($responseData);
    }

    private function alertData($requestInput) : array{
        $dbData = [];
        $dbData['alertName'] = $requestInput['alertName'];
        $dbData['fkAccountId'] = $requestInput['fkAccountId'];
        $dbData['fkProfileId'] = $requestInput['fkProfileId'];
        $dbData['tacosAlertsStatus'] = (isset($requestInput['tacosAlertsStatus'])) ? $requestInput['tacosAlertsStatus'] : 0;
        $dbData['biddingRuleAlertsStatus'] = (isset($requestInput['biddingRuleAlertsStatus'])) ? $requestInput['biddingRuleAlertsStatus'] : 0;
        $dbData['dayPartingAlertsStatus'] = (isset($requestInput['dayPartingAlertsStatus'])) ? $requestInput['dayPartingAlertsStatus'] : 0;
        $dbData['bidMultiplierAlertsStatus'] = (isset($requestInput['bidMultiplierAlertsStatus'])) ? $requestInput['bidMultiplierAlertsStatus'] : 0;
        $dbData['budgetMultiplierAlertsStatus'] = (isset($requestInput['budgetMultiplierAlertsStatus'])) ? $requestInput['budgetMultiplierAlertsStatus'] : 0;
        $dbData['addCC'] = (isset($requestInput['addCC']) && !is_null($requestInput['addCC'])) ? implode(",", $requestInput['addCC']) : 'NA';

        return $dbData;

    }
    /********* Update alerts ********/
    /**
     * @param $request
     * @return array
     */
    public function updateAlert(Request $request)
    {
        $messages = [
            'alertName.unique' => 'This alert name is already exist.'
        ];

        $alertId = $request->input('id');
        // Validations
        $validator = Validator::make($request->all(), [
            'alertName' => 'required|max:50|unique:tbl_ams_alerts,alertName,' . $alertId . ',id',
        ], $messages);
        if ($validator->passes()) {
            $data = $this->alertData($request->all());

            $data['updated_at'] = date('Y-m-d h:i:s');

            amsAlerts::where('id', $alertId)->update($data);
            unset($data);
            $responseData = ['success' => 'Alert has been updated successfully!', 'ajax_status' => true];
        } else {

            $responseData = ['error' => 'This alert name is already exist', 'ajax_status' => false];
        }

        return response()->json($responseData);
    }


    /********* Delete alert ********/
    /**
     * @param $request
     * @return array
     */

    public function deleteAlert(Request $request)
    {
        $id = $request->input('id');
        return [
            "status" => amsAlerts::where('id', intval($id))
                ->delete()
        ];
    }

    /**
     * getAmsAdminProfileList
     * @return array
     */
    public function getAmsAdminProfileList(): array
    {
        $profileIds = $this->getAllAmsAdminAccountProfileIds();
        $data['accounts'] = VissualsProfile::with("accounts:id,fkId", "accounts.brand_alias:fkAccountId,overrideLabel")
            ->select("id", "profileId", "name")->whereIn("id", $profileIds)->get();
        return $data;

    }//end function

    /**
     *   getAllAmsAdminAccountProfileIds
     * @return array
     *
     */
    private function getAllAmsAdminAccountProfileIds()
    {
        $profileIds = AccountModel::select("id", "fkId")
            ->where("fkAccountType", 1)
            ->get()
            ->map(function ($item, $value) {
                return $item->fkId;
            });
        return $profileIds;
    }//end function
}
