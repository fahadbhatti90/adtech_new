<?php

namespace App\Http\Controllers\BidMultiplier;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Models\ams\campaign\CampaignList;
use App\Models\BidMultiplierModels\KeywordBidValue;
use App\Models\BidMultiplierModels\BidMultiplierTracker;
use App\Models\BidMultiplierModels\BidMultiplierListModel;
use App\Models\BidMultiplierModels\Cron as bidMultiplierCronModel;
use App\Models\BidMultiplierModels\BidMulitplierListActivityTrackerModel;

class BidMultiplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $campaignIds = [];
        $data = [];
        $campaignSelected = $request->input('campaignIds');

        $startDate = date('Y-m-d', strtotime($request->input('startDate')));
        $endDate = date('Y-m-d', strtotime($request->input('endDate')));
        foreach ($campaignSelected as $details) {
            $campaignIds[] = $details["campaignId"];
            $data[] = [
                "profileId" => $details["profileId"],
                "campaignId" => $details["campaignId"],
                'bid' => $request->input('bid'),
                "startDate" => $startDate,
                "endDate" => $endDate,
                "userID" => auth()->user()->id,
                "createdAt" => date('Y-m-d H:i:s'),
                "updatedAt" => date('Y-m-d H:i:s')
            ];
        }
        $status = true;

        $getData = $this->checkCampaignOverlap($startDate, $endDate, $campaignIds);

        if(!$getData->isEmpty())
        {
            $status = false;
            unset($data);
            unset($campaignIds);
        }else{
            $lastId = BidMultiplierListModel::latest("id")->value("id");
            BidMultiplierListModel::insert($data);
            $newlyAddedRecord = [];
            if(is_null($lastId)){
                $newlyAddedRecord = BidMultiplierListModel::get();
                
            }else{
                $newlyAddedRecord = BidMultiplierListModel::where("id",">=",$lastId)->get();
            }
            $logData = [];
            foreach ($newlyAddedRecord as $details) {
                $logData[] = [
                    "fkMultiplierListId" => $details["id"],
                    "profileId" => $details["profileId"],
                    "campaignId" => $details["campaignId"],
                    'bid' => $details["bid"],
                    "startDate" => $details["startDate"],
                    "endDate" => $details["endDate"],
                    "userID" =>  auth()->user()->id,
                    "updatedAt" => date('Y-m-d H:i:s')
                ];
            }
            BidMulitplierListActivityTrackerModel::insert($logData);
        }
            
        return [
            "status" => $status,
            "overlapCampaigns" => $getData
        ];

    }

    private function checkCampaignOverlap($startDate, $endDate, $campaignIds, $id = null)
    {
        $bidCampaignTN = CampaignList::getTableName();
        $bidTN = BidMultiplierListModel::getTableName();

        if (is_null($id)) {
            return CampaignList::leftJoin("$bidTN", "$bidCampaignTN.campaignId", "=", "$bidTN.campaignId")
                ->whereRaw("$bidTN.campaignId IS NOT NULL")
                ->whereIn("$bidTN.campaignId", $campaignIds)
                ->where("$bidTN.endDate", '>=', $startDate)
                ->where("$bidTN.startDate", '<=', $endDate)
                ->get(['name', "$bidTN.startDate", "$bidTN.endDate"]);
        } else {
            return CampaignList::leftJoin("$bidTN", "$bidCampaignTN.campaignId", "=", "$bidTN.campaignId")
                ->where("$bidTN.campaignId", $campaignIds)
                ->where("$bidTN.endDate", '>=', $startDate)
                ->where("$bidTN.startDate", '<=', $endDate)
                ->where("$bidTN.id", "!=", $id)
                ->get(['name', "$bidTN.startDate", "$bidTN.endDate"]);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $keywordTableName = KeywordBidValue::getTableName();
        $BidTrackerTableName = BidMultiplierTracker::getTableName();
        return BidMultiplierTracker::where("$BidTrackerTableName.fkMultiplierId", $id)
        ->where("$keywordTableName.fkMultiplierId", $id)
        ->leftJoin("$keywordTableName", "$BidTrackerTableName.keywordId", "$keywordTableName.keywordId")
        ->select(
            "$BidTrackerTableName.keywordId",
            "$keywordTableName.keywordText",
            "$BidTrackerTableName.bidOptimizationValue",
            "$BidTrackerTableName.oldBid",
            "$BidTrackerTableName.bid",
            "$BidTrackerTableName.creationDate"
        )
        ->orderBy("$BidTrackerTableName.id", "desc")
        ->get();
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

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BidMultiplierListModel $bidMultiplier)
    {
        $status = true;
        if ($request->has('id')) {
            $startDate = date('Y-m-d', strtotime($request->input('startDate')));
            $endDate = date('Y-m-d', strtotime($request->input('endDate')));
            $bid = $request->input('bid');
            $id = $request->input('id');
            $campaignId = $request->input('campaignId');
            
            $oldata = $bidMultiplier::find($id);
            $updateState = false;
            $getData = $this->checkCampaignOverlap($startDate, $endDate, $campaignId, $id);
            if(!$getData->isEmpty()){
                $status = false;
            }else{
                $updateState = $bidMultiplier->update($request->all());
                if($updateState){
                    $this->logScheduleActivity($bidMultiplier);
                }
            } 
            if ($bidMultiplier->isActive == 1) {
                /**
                 * Previously Only Start and End date edit was allowed
                 * Now if currentDate is not greator than endDate edit is allowed
                 */
                $runStatus = $this->shouldSendPutCall($bidMultiplier);

                if($bid != $oldata->bid){
                    $singleData = bidMultiplierCronModel::with('getTokenDetail')
                        ->where('fkMultiplierId', $bidMultiplier->id)
                        ->first(); //get one record from DB
                    if ($singleData && $runStatus == true) {
                        $data['data'] = $singleData;
                        $data['data']['frontEnd'] = 1;
                        Artisan::call('update:bidMultiplier', $data);
                    }
                }
            }
            return [
                "status" => $status,
                "overlapCampaigns" => $getData
            ];

        } else {
            $runStatus = $this->shouldSendPutCall($bidMultiplier);

            $singleData = bidMultiplierCronModel::with('getTokenDetail')
                ->where('fkMultiplierId', $bidMultiplier->id)
                ->first(); //get one record from DB
            if ($singleData && $runStatus == true) {
                $data['data'] = $singleData;
                if ($request->input('isActive') == true) {
                    $data['data']['frontEnd'] = 1;
                    Artisan::call('update:bidMultiplier', $data);
                } else {
                    Artisan::call('delete:bidMultiplier', $data);
                }
            }
            $bidMultiplierUpdateStatus = $bidMultiplier->update($request->all());
            if($bidMultiplierUpdateStatus){
                $this->logScheduleActivity($bidMultiplier);
            }

            return [
                "status" => true,
                "data" => $bidMultiplierUpdateStatus,
            ];
        }
    }
    private function logScheduleActivity($bidMultiplier){
        BidMulitplierListActivityTrackerModel::create([
            "fkMultiplierListId" => $bidMultiplier["id"],
            "profileId" => $bidMultiplier["profileId"],
            "campaignId" => $bidMultiplier["campaignId"],
            'bid' => $bidMultiplier["bid"],
            "startDate" => $bidMultiplier["startDate"],
            "endDate" => $bidMultiplier["endDate"],
            "isActive" => $bidMultiplier["isActive"],
            "userID" =>  auth()->user()->id,
            "updatedAt" => date('Y-m-d H:i:s')
        ]);
    }
    private function shouldSendPutCall($bidMultiplier){
        $shouldSend = false;
        if ($bidMultiplier->endDate >= $bidMultiplier->startDate && date('Y-m-d') <= $bidMultiplier->endDate && date('Y-m-d') >= $bidMultiplier->startDate ) {
            $shouldSend = true;
        } elseif ($bidMultiplier->startDate == $bidMultiplier->endDate) {
            $finishTime = date('Y-m-d H:i:s', strtotime($bidMultiplier->endDate . "11:59 p.m."));
            if ($finishTime >= date('Y-m-d H:i:s') && $bidMultiplier->endDate == date('Y-m-d')) {
                $shouldSend = true;
            }
        }
        return $shouldSend;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bidMultiplier = BidMultiplierListModel::where('id', $id)
            ->first(); //get one record from DB
        $runStatus = false;
        if ($bidMultiplier->endDate > $bidMultiplier->startDate && $bidMultiplier->startDate == date('Y-m-d')) {
            $runStatus = true;
        } elseif ($bidMultiplier->startDate == $bidMultiplier->endDate) {
            $finishTime = date('Y-m-d H:i:s', strtotime($bidMultiplier->endDate . "11:59 p.m."));
            if ($finishTime >= date('Y-m-d H:i:s') && $bidMultiplier->endDate == date('Y-m-d')) {
                $runStatus = true;
            }
        }
        $singleData = bidMultiplierCronModel::with('getTokenDetail')
            ->where('fkMultiplierId', intval($id))
            ->first(); //get one record from DB
        if ($singleData && $runStatus == true) {
            $data['data'] = $singleData;
            Artisan::call('delete:bidMultiplier', $data);
            bidMultiplierCronModel::where('fkMultiplierId', intval($id))->delete();
        }
        return [
            "status" => BidMultiplierListModel::where('id', intval($id))
                ->delete()
        ];
    }
}
