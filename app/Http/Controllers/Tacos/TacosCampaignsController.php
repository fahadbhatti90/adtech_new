<?php

namespace App\Http\Controllers\Tacos;

use App\User;
use Illuminate\Http\Request;
use App\Models\Tacos\TacosModel;
use App\Http\Controllers\Controller;
use App\Models\Tacos\TacosCampaignModel;
use App\Models\AccountModels\AccountModel;
use App\Models\ClientModels\CampaignTagsModel;
use App\Models\Tacos\TacosListActivityTrackerModel;
use App\Libraries\DataTableHelpers\DataTableHelpers;
use App\Models\ClientModels\CampaignTagsAssignmentModel;

class TacosCampaignsController extends Controller
{
    public function index(Request $request)
    {
        
        $options = $request->options;

        $tacosCampaginTN = TacosCampaignModel::getCompleteTableName();
        $tacosTN = TacosModel::getCompleteTableName();

        $searchDetails = $this->getSearchNSelectColumns($request->columnsToSearch, $tacosCampaginTN, $tacosTN);
        
        $query = $this->getCampaignTableQuery(
            $searchDetails["columnsToSelect"], 
            $request, 
            $tacosCampaginTN, 
            $tacosTN
        );

        $query = $this->handleIfFilterApplied($query, $request, $tacosCampaginTN, $tacosTN);
        
        $paginatedData = DataTableHelpers::GetProductTablePaginatedData(
            $query, 
            $options, 
            $searchDetails["columnsToSearch"],
            null,
            null
        );

        $paginatedData["status"] = true;
        return $paginatedData ;
      
    } //end function
    
    public function CampaignSchedule(Request $request)
    {
        
        $options = $request->options;

        $tacosCampaginTN = TacosCampaignModel::getCompleteTableName();
        $tacosTN = TacosModel::getCompleteTableName();
        $campaignTagsTN = CampaignTagsAssignmentModel::getCompleteTableName();
        $tagsTN = CampaignTagsModel::getCompleteTableName();

        $searchDetails = $this->getScheduleSearchNSelectColumns($request->columnsToSearch, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagsTN);
        
        $query = $this->getCampaignScheduleTableQuery(
            $searchDetails["columnsToSelect"], 
            $request, 
            $tacosCampaginTN, 
            $tacosTN,
            $campaignTagsTN,
            isset($request->tag) ? $request->tag : ""
        );

        $query = $this->handleIfScheduleFilterApplied($query, $request, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagsTN);
        
        $paginatedData = DataTableHelpers::GetProductTablePaginatedData(
            $query, 
            $options, 
            $searchDetails["columnsToSearch"],
            "id",
            "desc"
        );

        $paginatedData["status"] = true;
        return $paginatedData ;
      
    } //end function
    public function CampaignHistory(Request $request)
    {
        $options = $request->options;
        $tacosCampaginTN = TacosCampaignModel::getCompleteTableName();
        $tacosTN = TacosListActivityTrackerModel::getTableName();
        $campaignTagsTN = CampaignTagsAssignmentModel::getCompleteTableName();
        $tagsTN = CampaignTagsModel::getCompleteTableName();
        $userTN = User::getTableName();
        $searchDetails = $this->getHistorySearchNSelectColumns(
            $request->columnsToSearch, 
            $tacosCampaginTN, 
            $tacosTN, 
            $campaignTagsTN, 
            $tagsTN,
            $userTN
        );
        $query = $this->getCampaignHistoryTableQuery(
            $searchDetails["columnsToSelect"], 
            $request, 
            $tacosCampaginTN, 
            $tacosTN,
            $campaignTagsTN,
            isset($request->tag) ? $request->tag : "",
            $userTN
        );

        $query = $this->handleIfHistoryFilterApplied($query, $request, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagsTN, $userTN);
        
        $paginatedData = DataTableHelpers::GetProductTablePaginatedData(
            $query, 
            $options, 
            $searchDetails["columnsToSearch"],
            "id",
            "desc"
        );

        $paginatedData["status"] = true;
        return $paginatedData ;
      
    } //end function

    private function getSearchNSelectColumns($requestedColumnsToSearch, $tacosCampaginTN, $tacosTN){
        $columnsToSearch = [];
        $columnsToSelect = [];
        $columnsToSelect[]= "$tacosCampaginTN.strCampaignId";
        $columnsToSelect[]= "$tacosCampaginTN.fkProfileId";
        $columnsToSelect[]= "$tacosCampaginTN.profileId";
        $columnsToSelect[]= "'NA' as category";
        $columnsToSelect[]= "'NA' as strategy";
        foreach ($requestedColumnsToSearch as $key => $value) {
            if($key == 0) continue;

            $activeColumn = "";
            switch ($value) {
                case 'category':
                case 'strategy':
                    # code...
                    // $columnsToSelect[] = "$value";
                    break;
                
                default:
                    # code...
                    $columnsToSearch[] = "$tacosCampaginTN.".$value;
                    $columnsToSelect[] = "$tacosCampaginTN.".$value;
                    break;
            }
        }
        return ["columnsToSearch"=>$columnsToSearch, "columnsToSelect" => $columnsToSelect];
    }
    
    private function getScheduleSearchNSelectColumns($requestedColumnsToSearch, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagsTN){
        $columnsToSearch = [];
        $columnsToSelect = [];
        $columnsToSelect[]= "$tacosCampaginTN.strCampaignId";
        $columnsToSelect[]= "$tacosCampaginTN.fkProfileId";
        $columnsToSelect[]= "$tacosCampaginTN.profileId";
        $columnsToSelect[]= "'NA' as category";
        $columnsToSelect[]= "'NA' as strategy";
        $columnsToSelect[] = "$tacosCampaginTN.name";
        $columnsToSearch[] = "$tacosCampaginTN.name";
        $columnsToSelect[] = "$tacosTN.id";
        foreach ($requestedColumnsToSearch as $key => $value) {
            if($key == 0) continue;

            $activeColumn = "";
            switch ($value) {
                case 'category':
                case 'strategy':
                case 'strCampaignId':
                case 'name':
                    # code...
                    // $columnsToSelect[] = "$value";
                break;
                case 'campaignType':
                case 'startDate':
                    $columnsToSearch[] = "$tacosCampaginTN.".$value;
                    $columnsToSelect[] = "$tacosCampaginTN.".$value;
                    break;
                case 'tag':
                    $columnsToSearch[] = "tagTb.tag";
                    $columnsToSelect[] = "tagTb.fkTagIds";
                    $columnsToSelect[] = "tagTb.tag";
                    break;
                default:
                    # code...
                    $columnsToSearch[] = "$tacosTN.".$value;
                    $columnsToSelect[] = "$tacosTN.".$value;
                    break;
            }
        }
        // $columnsToSearch[] = "$tacosCampaginTN.name";
        // $columnsToSearch[] = "$tacosTN.tacos";
        // $columnsToSearch[] = "$tacosTN.min";
        // $columnsToSelect[] = "$tacosTN.metric";
        // $columnsToSearch[] = "$tacosTN.max";
        // $columnsToSearch[] = "$tacosTN.isActive";
        return ["columnsToSearch"=>$columnsToSearch, "columnsToSelect" => $columnsToSelect];
    }
    private function getHistorySearchNSelectColumns($requestedColumnsToSearch, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagsTN, $userTN){
        $columnsToSearch = [];
        $columnsToSelect = [];
        $columnsToSelect[]= "$tacosCampaginTN.strCampaignId";
        $columnsToSelect[]= "$tacosCampaginTN.fkProfileId";
        $columnsToSelect[]= "$tacosCampaginTN.profileId";
        $columnsToSelect[]= "'NA' as category";
        $columnsToSelect[]= "'List' as include";
        $columnsToSelect[]= "'NA' as strategy";
        $columnsToSelect[] = "$tacosCampaginTN.name";
        $columnsToSearch[] = "$tacosCampaginTN.name";
        $columnsToSelect[] = "$tacosTN.id";
        $columnsToSelect[] = "$tacosTN.fkTacosId";
        foreach ($requestedColumnsToSearch as $key => $value) {
            if($key == 0) continue;

            $activeColumn = "";
            switch ($value) {
                case 'category':
                case 'strategy':
                case 'strCampaignId':
                case 'inlcude':
                case 'name':
                    # code...
                    // $columnsToSelect[] = "$value";
                break;
                case 'email':
                    $columnsToSearch[] = "$userTN." . $value;
                    $columnsToSelect[] = "$userTN." . $value;
                    break;
                case 'userName':
                    $columnsToSearch[] = "$userTN.name" ;
                    $columnsToSelect[] = "$userTN.name as userName";
                    break;
                case 'campaignType':
                case 'startDate':
                    $columnsToSearch[] = "$tacosCampaginTN.".$value;
                    $columnsToSelect[] = "$tacosCampaginTN.".$value;
                    break;
                case 'tag':
                    $columnsToSearch[] = "tagTb.tag";
                    $columnsToSelect[] = "tagTb.fkTagIds";
                    $columnsToSelect[] = "tagTb.tag";
                    break;
                default:
                    # code...
                    $columnsToSearch[] = "$tacosTN.".$value;
                    $columnsToSelect[] = "$tacosTN.".$value;
                    break;
            }
        }
        // $columnsToSearch[] = "$tacosCampaginTN.name";
        // $columnsToSearch[] = "$tacosTN.tacos";
        // $columnsToSearch[] = "$tacosTN.min";
        // $columnsToSelect[] = "$tacosTN.metric";
        // $columnsToSearch[] = "$tacosTN.max";
        // $columnsToSearch[] = "$tacosTN.isActive";
        return ["columnsToSearch"=>$columnsToSearch, "columnsToSelect" => $columnsToSelect];
    }
    
    private function handleIfFilterApplied($query, $request, $tacosCampaginTN){
        if(isset($request->status)){
            $query["data"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.state", $request->status);
            });
            $query["count"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.state", $request->status);
            });
           
        }
        // if(isset($request->category)){
        //     $query["data"]->orWhereNotNull("$VpsmTN.category");
        //     $query["count"]->orWhereNotNull("$VpsmTN.category");
        // }
        return $query;
    }
    
    private function handleIfScheduleFilterApplied($query, $request, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagsTN){
        if(isset($request->adType)){
            $query["data"]->where(function($query) use ($tacosTN,$request){
                $query->orWhere("$tacosTN.metric", $request->adType);
            });
            $query["count"]->where(function($query) use ($tacosTN,$request){
                $query->orWhere("$tacosTN.metric", $request->adType);
            });
           
        }
        if(isset($request->status)){
            $query["data"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.state", $request->status);
            });
            $query["count"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.state", $request->status);
            });
           
        }
        if(isset($request->startDate)){
            $query["data"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.startDate", date('Ymd', strtotime($request->startDate)));
            });
            $query["count"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.startDate", date('Ymd', strtotime($request->startDate)));
            });
           
        }
        // if(isset($request->category)){
        //     $query["data"]->orWhereNotNull("$VpsmTN.category");
        //     $query["count"]->orWhereNotNull("$VpsmTN.category");
        // }
        return $query;
    }
    
    private function handleIfHistoryFilterApplied($query, $request, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagsTN){
        if(isset($request->adType)){
            $query["data"]->where(function($query) use ($tacosTN,$request){
                $query->orWhere("$tacosTN.metric", $request->adType);
            });
            $query["count"]->where(function($query) use ($tacosTN,$request){
                $query->orWhere("$tacosTN.metric", $request->adType);
            });
           
        }
        if(isset($request->status)){
            $query["data"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.state", $request->status);
            });
            $query["count"]->where(function($query) use ($tacosCampaginTN,$request){
                $query->orWhere("$tacosCampaginTN.state", $request->status);
            });
           
        }
        if (isset($request->startDate) && !is_null($request->startDate)) {
            $query["data"]->where(function ($query) use ($tacosTN, $request) {
                $query->orWhere(\DB::raw("DATE($tacosTN.updatedAt)"), ">=", $request->startDate);
            });
            $query["count"]->where(function ($query) use ($tacosTN, $request) {
                $query->orWhere(\DB::raw("DATE($tacosTN.updatedAt)"), ">=", $request->startDate);
            });
        }

        if (isset($request->endDate) && !is_null($request->endDate)) {
            $query["data"]->where(function ($query) use ($tacosTN, $request) {
                $query->orWhere(\DB::raw("DATE($tacosTN.updatedAt)"), "<=", $request->endDate);
            });
            $query["count"]->where(function ($query) use ($tacosTN, $request) {
                $query->orWhere(\DB::raw("DATE($tacosTN.updatedAt)"), "<=", $request->endDate);
            });
        }
        // if(isset($request->category)){
        //     $query["data"]->orWhereNotNull("$VpsmTN.category");
        //     $query["count"]->orWhereNotNull("$VpsmTN.category");
        // }
        return $query;
    }
    
    private function getCampaignScheduleTableQuery($columnsToSelect, $request, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagIds){
        
        $accounts = AccountModel::where("fkBrandId",getBrandId())
        ->where("fkAccountType",1)
        ->select("fkId")
        ->get()
        ->map(function($item,$value) use ($request) {
            if(isset($request->childBrand))
            {
                if($request->childBrand == $item->fkId)
                return $item->fkId;
            }
            else{
                return $item->fkId;
            }
        });

        $dataQuery = TacosCampaignModel::selectRaw(implode(",",$columnsToSelect))
        ->leftJoin("$tacosTN", "$tacosCampaginTN.campaignId", "=", "$tacosTN.campaignId")
        ->leftJoin($this->getTagsQuery($tagIds), "$tacosTN.campaignId", "=",\DB::raw("tagTb.campaignId"))
        ->whereRaw("$tacosTN.campaignId IS NOT NULL")
        ->whereIn("$tacosCampaginTN.fkProfileId", $accounts);


        $countQuery =  TacosCampaignModel::selectRaw("COUNT(*) OVER () AS TotalRecords")
        ->leftJoin("$tacosTN", "$tacosCampaginTN.campaignId", "=", "$tacosTN.campaignId")
        // ->leftJoin("$campaignTagsTN", "$tacosCampaginTN.campaignId", "=", "$campaignTagsTN.campaignId")
        ->leftJoin($this->getTagsQuery($tagIds), "$tacosTN.campaignId", "=",\DB::raw("tagTb.campaignId"))
        ->whereRaw("$tacosTN.campaignId IS NOT NULL")
        ->whereIn("$tacosCampaginTN.fkProfileId", $accounts);
        
        return [
            "data"=>$dataQuery,
            "count"=>$countQuery
        ];
    }
    
    private function getCampaignHistoryTableQuery($columnsToSelect, $request, $tacosCampaginTN, $tacosTN, $campaignTagsTN, $tagIds, $userTN){
        
        $accounts = AccountModel::where("fkBrandId",getBrandId())
        ->where("fkAccountType",1)
        ->select("fkId")
        ->get()
        ->map(function($item,$value) use ($request) {
            if(isset($request->childBrand))
            {
                if($request->childBrand == $item->fkId)
                return $item->fkId;
            }
            else{
                return $item->fkId;
            }
        });

        $dataQuery = TacosCampaignModel::selectRaw(implode(",",$columnsToSelect))
        ->leftJoin("$tacosTN", "$tacosCampaginTN.campaignId", "=", "$tacosTN.campaignId")
        ->leftJoin($this->getHistoryTagsQuery($tagIds), "$tacosTN.campaignId", "=",\DB::raw("tagTb.campaignId"))
        ->leftJoin("$userTN", "$userTN.id", "=", "$tacosTN.userID")
        ->whereRaw("$tacosTN.campaignId IS NOT NULL")
        ->whereIn("$tacosCampaginTN.fkProfileId", $accounts);


        $countQuery =  TacosCampaignModel::selectRaw("COUNT(*) OVER () AS TotalRecords")
        ->leftJoin("$tacosTN", "$tacosCampaginTN.campaignId", "=", "$tacosTN.campaignId")
        // ->leftJoin("$campaignTagsTN", "$tacosCampaginTN.campaignId", "=", "$campaignTagsTN.campaignId")
        ->leftJoin($this->getHistoryTagsQuery($tagIds), "$tacosTN.campaignId", "=",\DB::raw("tagTb.campaignId"))
        ->leftJoin("$userTN", "$userTN.id", "=", "$tacosTN.userID")
        ->whereRaw("$tacosTN.campaignId IS NOT NULL")
        ->whereIn("$tacosCampaginTN.fkProfileId", $accounts);
        if(!empty($tagIds) && !is_null($tagIds)){
            $dataQuery->whereRaw("tagTb.fkTagIds IS NOT NULL");
            $countQuery->whereRaw("tagTb.fkTagIds IS NOT NULL");
        }
        return [
            "data"=>$dataQuery,
            "count"=>$countQuery
        ];
    }

    private function getTagsQuery ($tagIds){
        $campaignTagsTN = CampaignTagsAssignmentModel::getCompleteTableName();
        $tagTn = CampaignTagsModel::getCompleteTableName();
        if($tagIds && count($tagIds) > 0){
            $tagIdsStr = implode(",", $tagIds);
           return \DB::raw("(
                SELECT 
                GROUP_CONCAT(t1.fkTagId) AS fkTagIds, 
                t1.campaignId, 
                GROUP_CONCAT(t2.tag) AS tag 
                FROM $campaignTagsTN t1 
                LEFT JOIN $tagTn as t2
                ON t1.fkTagId = t2.id
                where t1.fkTagId IN ($tagIdsStr) 
                GROUP BY t1.campaignId
           ) tagTb ");
        }
        return \DB::raw("(
            SELECT 
            GROUP_CONCAT(t1.fkTagId) AS fkTagIds, 
            t1.campaignId, 
            GROUP_CONCAT(t2.tag) AS tag 
            FROM $campaignTagsTN t1 
            LEFT JOIN $tagTn as t2
            ON t1.fkTagId = t2.id
            GROUP BY t1.campaignId
        ) tagTb");
    }
    private function getHistoryTagsQuery ($tagIds){
        $campaignTagsTN = CampaignTagsAssignmentModel::getCompleteTableName();
        $tagTn = CampaignTagsModel::getCompleteTableName();
        if($tagIds && !empty($tagIds)){
            $tagIdsStr = $tagIds;
           return \DB::raw("(
                SELECT 
                GROUP_CONCAT(t1.fkTagId) AS fkTagIds, 
                t1.campaignId, 
                GROUP_CONCAT(t2.tag) AS tag 
                FROM $campaignTagsTN t1 
                LEFT JOIN $tagTn as t2
                ON t1.fkTagId = t2.id
                where t1.fkTagId IN ($tagIdsStr) 
                GROUP BY t1.campaignId
           ) tagTb ");
        }
        return \DB::raw("(
            SELECT 
            GROUP_CONCAT(t1.fkTagId) AS fkTagIds, 
            t1.campaignId, 
            GROUP_CONCAT(t2.tag) AS tag 
            FROM $campaignTagsTN t1 
            LEFT JOIN $tagTn as t2
            ON t1.fkTagId = t2.id
            GROUP BY t1.campaignId
        ) tagTb");
    }
    private function getCampaignTableQuery($columnsToSelect, $request, $tacosCampaginTN, $tacosTN){
        
        $accounts = AccountModel::where("fkBrandId",getBrandId())
        ->where("fkAccountType",1)
        ->select("fkId")
        ->get()
        ->map(function($item,$value) use ($request) {
            if(isset($request->childBrand))
            {
                if($request->childBrand == $item->fkId)
                return $item->fkId;
            }
            else{
                return $item->fkId;
            }
        });
        $dataQuery = TacosCampaignModel::selectRaw(implode(",",$columnsToSelect))
        ->leftJoin("$tacosTN", "$tacosCampaginTN.campaignId", "=", "$tacosTN.campaignId")
        ->whereRaw("$tacosTN.campaignId IS NULL")
        ->whereIn("$tacosCampaginTN.fkProfileId", $accounts);
        $countQuery =  TacosCampaignModel::selectRaw("COUNT(*) OVER () AS TotalRecords")
        ->leftJoin("$tacosTN", "$tacosCampaginTN.campaignId", "=", "$tacosTN.campaignId")
        ->whereRaw("$tacosTN.campaignId IS NULL")
        ->whereIn("$tacosCampaginTN.fkProfileId", $accounts);
        return [
            "data"=>$dataQuery,
            "count"=>$countQuery
        ];
    }
    public function childBrands(Request $request) {
        $response = [
            "status"=>true
        ];
        $account = AccountModel::with("ams:id,profileId,name,type")
        ->where("fkBrandId", getBrandId())
        ->where("fkAccountType", 1)
        ->select("id", "fkId")
        ->get();
        $accountIds = $account->map(function($item, $value) use ($request) {
            return $item->id;
        });
        $campaignTagsTN = CampaignTagsAssignmentModel::getCompleteTableName();
        $tagsTN = CampaignTagsModel::getCompleteTableName();

        $tags = CampaignTagsAssignmentModel::select("$tagsTN.id", "$tagsTN.tag")
        ->leftJoin("$tagsTN", "$campaignTagsTN.fkTagId", "=", "$tagsTN.id")
        ->whereIn("$campaignTagsTN.fkAccountId", $accountIds)
        ->groupBy("$campaignTagsTN.fkTagId")
        ->get();
        $response["childBrands"] = $account;
        $response["tags"] = $tags;
        return $response;
    }
}
