<?php

namespace App\Http\Controllers\Tacos;

use Illuminate\Http\Request;
use App\Models\Tacos\TacosModel;
use App\Models\Tacos\keywordList;
use App\Http\Controllers\Controller;
use App\Models\Tacos\TacosBidTracker;
use App\Models\Tacos\TacosListActivityTrackerModel;

class TacosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [
            "status"=> true
        ];

        $tacos = TacosModel::
        with("campaign:campaignId,campaignName")
        ->select(
            "profileId",
            "campaignId as fkCampaignId",
            "metric",
            'tacos',
            'min',
            "max",
            "isActive",
            "createdAt"
        )->get();

        $response["data"] = $tacos;
        return $response;
    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $keywordTableName = keywordList::getTableName();
        $TacosTrackerTableName = TacosBidTracker::getTableName();
        return TacosBidTracker::where("$TacosTrackerTableName.fkTacosId", $id)
        ->where("$keywordTableName.fkMultiplierId", $id)
        ->leftJoin("$keywordTableName", "$TacosTrackerTableName.keywordId", "$keywordTableName.keywordId")
        ->select(
            "$keywordTableName.keywordId",
            "$keywordTableName.keywordText",
            \DB::raw("'NA' as bidOptimizationValue"),
            "$TacosTrackerTableName.oldBid",
            "$TacosTrackerTableName.bid",
            "$TacosTrackerTableName.creationDate"
        )
        ->orderBy("$TacosTrackerTableName.id", "desc")
        ->get();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = [];

        foreach ($request->campaignIds as $asin => $details) {
            $data[] = [
                "profileId"=>$details["profileId"],
                "campaignId"=>$details["campaignId"],
                "metric"=>$request["metric"],
                'tacos'=>$request["tacos"],
                'min'=>$request["min"],
                "max"=>$request["max"],
                "userID"=> auth()->user()->id,
                "createdAt"=>date('Y-m-d H:i:s'),
                "updatedAt"=> date('Y-m-d H:i:s')
            ];
        }
            $lastId = TacosModel::latest("id")->value("id");
            TacosModel::insert($data);
            $newlyAddedRecord = [];
            if(is_null($lastId)){
                $newlyAddedRecord = TacosModel::get();
                
            }else{
                $newlyAddedRecord = TacosModel::where("id",">=",$lastId)->get();
            }
            $logData = [];
            foreach ($newlyAddedRecord as $details) {
                $logData[] = [
                    "fkTacosId" => $details["id"],
                    "profileId" => $details["profileId"],
                    "campaignId" => $details["campaignId"],
                    'metric' => $details["metric"],
                    "tacos" => $details["tacos"],
                    "min" => $details["min"],
                    "max" => $details["max"],
                    "userID" =>  auth()->user()->id,
                    "updatedAt" => date('Y-m-d H:i:s')
                ];
            }
            TacosListActivityTrackerModel::insert($logData);
        
        return [
            "status" => true,
        ];
    }//end function

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tacos\TacosModel  $tacosModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TacosModel $taco)
    {
        
        TacosListActivityTrackerModel::insert([
            "fkTacosId" => $taco["id"],
            "profileId" => $taco["profileId"],
            "campaignId" => $taco["campaignId"],
            'metric' => $request->has("metric") ? $request->input("metric") : $taco["metric"],
            "tacos" => $request->has("tacos") ? $request->input("tacos") : $taco["tacos"],
            "min" => $request->has("min") ? $request->input("min") : $taco["min"],
            "max" => $request->has("max") ? $request->input("max") : $taco["max"],
            "userID" =>  auth()->user()->id,
            "isActive" => $request->has("isActive") ? $request->input("isActive") : $taco["isActive"],
            "updatedAt" => date('Y-m-d H:i:s')
        ]);
        return [
            "status" => true,
            "data" => $taco->update($request->all()),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tacos\TacosModel  $tacosModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(TacosModel $taco)
    {
        return [
            "status" =>$taco->delete(),
        ];
    }
}
