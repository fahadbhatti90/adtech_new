<?php

namespace App\Http\Controllers\ClientAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Models\AccountModels\AccountModel;
use App\Models\ClientModels\AsinTagsModel;
use App\Models\ProductSegments\AsinSegments;
use App\Models\Inventory\InventoryProductModel;
use App\Models\ProductSegments\ProductSegments;
use App\Models\ProductPreviewModels\EventsModel;
use App\Models\TempProductModels\TempProductModel;
use App\Libraries\DataTableHelpers\DataTableHelpers;
use App\Models\ProductPreviewModels\UserActionsModel;
use App\Models\TempProductModels\TempFilterAssignModel;
use App\models\ProductSegments\ProductSegmentGroupsModel;
use App\Models\ProductPreviewModels\GraphDataModels\AsinWeekModels;
use App\Models\ProductPreviewModels\GraphDataModels\AsinDailyModels;
use App\Models\ProductPreviewModels\GraphDataModels\AsinMonthModels;
use App\Models\ProductPreviewModels\GraphDataModels\ProductTableGraphModel;
use App\Models\ProductPreviewModels\GraphDataModels\ViewProductSegmentModel;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.manager');
    } //end constructor

    public function dashboard()
    {

        $data = [];
        $data["availableYears"] = $this->getAvailableYears();
        $data["defaultMonth"] = count($data["availableYears"]) > 0 ? $this->getDefaultMonths($data["availableYears"][0]->availableYear):"";

        return $data;
        
        return view("client.newDashboard");
    } //end function

    public function productList(Request $request)
    {
        $options = $request->options;

        $masterTN = AsinDailyModels::getCompleteTableName();
        $ItMTN = InventoryProductModel::getTableName();
        $VpsmTN = ViewProductSegmentModel::getCompleteTableName();

        $searchDetails = $this->getSearchNSelectColumns($request->columnsToSearch, $masterTN, $ItMTN, $VpsmTN);
        
        $query = $this->getProductTableQuery(
            $searchDetails["columnsToSelect"], 
            $request->segmentsIds, 
            $request->tagIds, 
            $masterTN, 
            $ItMTN, 
            $VpsmTN
        );

        $query = $this->handleIfTagOrSegmentFilterApplied($query, $request, $VpsmTN);
        
        $paginatedData = DataTableHelpers::GetProductTablePaginatedData(
            $query, 
            $options, 
            $searchDetails["columnsToSearch"],
            null,
            null,
            "$masterTN.ASIN"
        );

        $paginatedData["status"] = true;
        return $paginatedData ;
      
    } //end function
    private function handleIfTagOrSegmentFilterApplied($query, $request, $VpsmTN){
        if(($request->tagIds && count($request->tagIds) > 0)){
            $query["data"]->whereNotNull("tagTb.fkTagIds");
            $query["count"]->whereNotNull("tagTb.fkTagIds");
            if($request->segmentsIds && count($request->segmentsIds) > 0){
                $query["data"]->orWhereNotNull("$VpsmTN.segmentId");
                $query["count"]->orWhereNotNull("$VpsmTN.segmentId");
            }
        }
        else if($request->segmentsIds && count($request->segmentsIds) > 0){
            $query["data"]->whereNotNull("$VpsmTN.segmentId");
            $query["count"]->whereNotNull("$VpsmTN.segmentId");
        }
        return $query;
    }
    private function getSearchNSelectColumns($requestedColumnsToSearch, $masterTN, $ItMTN, $VpsmTN){
        $columnsToSearch = [];
        $columnsToSelect = [];
        $columnsToSearch[]= "$ItMTN.overrideLabel";
        $columnsToSelect[]= "fk_account_id";
        $columnsToSelect[]= "$ItMTN.overrideLabel";
        foreach ($requestedColumnsToSearch as $key => $value) {
            if($key == 0) continue;
            $activeColumn = "";
            switch ($value) {
                case 'tag':
                    # code...
                    $columnsToSearch[] = "tagTb.".$value;
                    $columnsToSelect[] = "tagTb.".$value;
                    $columnsToSelect[] = "tagTb.fkTagIds";
                    break;
                
                case 'segmentName':
                case 'groupName':
                    # code...
                    $columnsToSearch[] = "$VpsmTN.".$value;
                    $columnsToSelect[] = "$VpsmTN.segmentId";
                    $columnsToSelect[] = "$VpsmTN.".$value;
                break;
                default:
                    # code...
                    $columnsToSearch[] = "$masterTN.".$value;
                    $columnsToSelect[] = "$masterTN.".$value;
                    break;
            }
        }
        return ["columnsToSearch"=>$columnsToSearch, "columnsToSelect" => $columnsToSelect];
    }
    private function getProductTableQuery($columnsToSelect, $segmentsIds, $tagIds,  $masterTN, $ItMTN, $VpsmTN){
        
        $accounts = getActiveBrandAccountIds();
        $dataQuery = AsinDailyModels::selectRaw(implode(",",$columnsToSelect))
        ->leftJoin("$VpsmTN", "$masterTN.ASIN", "=", DB::raw("$VpsmTN.segmentASIN  COLLATE utf8mb4_unicode_ci"))
        ->leftJoin($this->getTagsQuery($tagIds), "$masterTN.ASIN", "=", DB::raw("tagTb.asin  COLLATE utf8mb4_unicode_ci"))
        ->leftJoin("$ItMTN", "$masterTN.ASIN", "=", DB::raw("$ItMTN.asin  COLLATE utf8mb4_unicode_ci"))
        ->whereIn("$masterTN.fk_account_id", $accounts)
        ->groupBy("$masterTN.ASIN");
        $countQuery =  AsinDailyModels::selectRaw("COUNT(*) OVER () AS TotalRecords")
        ->leftJoin("$VpsmTN", "$masterTN.ASIN", "=", DB::raw("$VpsmTN.segmentASIN  COLLATE utf8mb4_unicode_ci"))
        ->leftJoin($this->getTagsQuery($tagIds), "$masterTN.ASIN", "=", DB::raw("tagTb.asin  COLLATE utf8mb4_unicode_ci"))
        ->leftJoin("$ItMTN", "$masterTN.ASIN", "=", DB::raw("$ItMTN.asin  COLLATE utf8mb4_unicode_ci"))
        ->whereIn("$masterTN.fk_account_id", $accounts)
        ->groupBy("$masterTN.ASIN");
        return [
            "data"=>$dataQuery,
            "count"=>$countQuery
        ];
    }
    private function getTagsQuery ($tagIds){
        $tagTn = AsinTagsModel::getCompleteTableName();
        if($tagIds && count($tagIds) > 0){
            $tagIdsStr = implode(",", $tagIds);
           return \DB::raw("(SELECT GROUP_CONCAT(t1.fkTagId) AS fkTagIds, t1.asin, GROUP_CONCAT(t1.tag) AS tag FROM $tagTn t1 where t1.fkTagId IN ($tagIdsStr) GROUP BY ASIN) tagTb ");
        }
        return \DB::raw("(SELECT GROUP_CONCAT(t1.fkTagId) AS fkTagIds, t1.asin, GROUP_CONCAT(t1.tag) AS tag FROM $tagTn t1 GROUP BY ASIN) tagTb");
    }
    private function getSegmentsQuery ($segmentsIds) {
        $segmentsTN = AsinSegments::getCompleteTableName();
        $productSegmentsTN = ProductSegments::getCompleteTableName();
        $productSegmentGroupsTN = ProductSegmentGroupsModel::getCompleteTableName();
        if($segmentsIds && count($segmentsIds) > 0){
            $segIds = implode(",", $segmentsIds);
           return \DB::raw("(
                    SELECT tas.ASIN AS segmentASIN, 
                        tas.fkAccountId, 
                        GROUP_CONCAT(ps.id) AS segmentId, 
                        GROUP_CONCAT(ps.segmentName) AS segmentName, 
                        GROUP_CONCAT(tsg.id) AS groupId, 
                        GROUP_CONCAT(tsg.groupName) AS groupName 
                    FROM $segmentsTN tas
                    LEFT JOIN $productSegmentsTN ps
                        ON tas.fkSegmentId = ps.id
                    LEFT JOIN $productSegmentGroupsTN tsg
                        ON tas.fkGroupId = tsg.id
                    where tas.fkSegmentId IN ($segIds)    
                    GROUP BY tas.ASIN
                ) tblAsinSegments");
        }
        return \DB::raw("(
            SELECT tas.ASIN AS segmentASIN, 
                tas.fkAccountId, 
                GROUP_CONCAT(ps.id) AS segmentId, 
                GROUP_CONCAT(ps.segmentName) AS segmentName, 
                GROUP_CONCAT(tsg.id) AS groupId, 
                GROUP_CONCAT(tsg.groupName) AS groupName 
            FROM $segmentsTN tas
            LEFT JOIN $productSegmentsTN ps
                ON tas.fkSegmentId = ps.id
            LEFT JOIN $productSegmentGroupsTN tsg
                ON tas.fkGroupId = tsg.id
            GROUP BY tas.ASIN
        ) tblAsinSegments");
    }
    public function getCategories()
    {
        $accounts = AccountModel::where("fkBrandId",getBrandId())
        ->select("id")
        ->get()
        ->map(function($item,$value){
            return $item->id;
        });
        return ProductTableGraphModel::with("category_alias:fkCategoryId,overrideLabel")
            ->select("category_id", "category_name")
            ->whereNotNull("category_id")
            ->where("category_id", ">=", 0)
            ->whereIn("fk_account_id", $accounts)
            ->distinct()
            ->get();
    } //end function
    public function getAvailableMonths(Request $request)
    {
        return ProductTableGraphModel::select(DB::raw("distinct month(DATE) as availableMonth"))
            ->whereRaw(("year(DATE) = " . $request->year))
            ->orderBy(DB::raw("month(DATE)"))
            ->get();
            
    }
    public function getDefaultMonths($year)
    {
        return ProductTableGraphModel::select(DB::raw("distinct month(DATE) as defaultMonth"))
            ->whereRaw(("year(DATE) = " . $year))
            ->orderBy(DB::raw("month(DATE)", "desc"))
            ->first()->defaultMonth;
    }
    public function getAvailableYears()
    {
        return ProductTableGraphModel::select(DB::raw("distinct year(DATE) as availableYear"))
            ->orderBy(DB::raw("year(DATE)"), "desc")
            ->get();
    }
    public function getAvailableDates($asin, $accounts){
       return ProductTableGraphModel::select(\DB::raw("DATE_FORMAT(DATE,'%b %Y') AS fullDate, YEAR(DATE) AS 'year', MONTH(DATE) AS 'month',DATE_FORMAT(DATE,'%b, %Y') AS fullMonthYear"))
        ->where("ASIN",$asin)
        ->whereIn("fk_account_id",$accounts)
        ->groupBy(\DB::raw("MONTH(DATE),YEAR(DATE)"))
        ->orderBy(\DB::raw("DATE"), "desc")
        ->get();
    }
    /******************************************Private functions************************************/

    private function getDailyFilteredData( $asin, $year, $month, $attribute)
    {
        $accounts = AccountModel::where("fkBrandId",getBrandId())
        ->select("id")
        ->get()
        ->map(function($item,$value){
            return $item->id;
        });
        $data = ProductTableGraphModel::select("$attribute as attribute", DB::raw("day(DATE(DATE_FORMAT(DATE,'%Y-%m-%d'))) as capture_day"), DB::raw("DATE(DATE_FORMAT(DATE,'%Y-%m-%d')) as capture_date"))
            ->where("$attribute", ">=", 0)
            ->whereRaw("month(DATE) = $month")
            ->whereRaw('year(DATE) = ' . $year)
            ->where("ASIN", $asin)
            ->whereIn("fk_account_id", $accounts)
            ->orderBy(DB::raw('day(DATE)'))
            ->get();
        
        $newData = [];
        if(count($data) > 0){
            $monthEnd = intval(date("t", strtotime($data[0]->capture_date)));
            for ($i=1; $i <= $monthEnd; $i++) { 
                $newData[$i] = null;
            }
            foreach ($data as $key => $value) {
                $newData[$value->capture_day] = $value->attribute;
            }
        }
        return $newData;
    } //end funtion

    public function getDailyEvents(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $asin = $request->asin;
        $attribute = $request->attribute;
        $accounts = AccountModel::where("fkBrandId",getBrandId())
        ->select("id")
        ->get()
        ->map(function($item,$value){
            return $item->id;
        });
        
        $availableYears = $this->getAvailableDates($asin, $accounts);
        if(count($availableYears) <= 0)
        {    
            return ["events"=>[], "graph"=>[], "availbleDates"=>[], "eventNotes" => []];
        }
        if($year == "NA"){
            $year = $availableYears[0]->year;
            $month = $availableYears[0]->month;
        }
        $event = EventsModel::select("id", "eventName", "eventColor")->with(["prodcutPreview" => function ($query) use ($year, $month, $asin, $accounts) {
            $query->select(DB::raw("fkEventId,occurrenceDate,notes"));
            $query->whereIn('fkAccountId', $accounts );
            $query->whereRaw('year(occurrenceDate) = ' . $year);
            $query->whereRaw('month(occurrenceDate) = ' . $month);
            $query->where("asin", $asin);
        }])->get();
        // return $event;
        $events = [];
        $eventsNotes = [];
        $start = 5;
        $graphData = $this->getDailyFilteredData($asin, $year, $month, $attribute);
        $graphDataKeys = array_keys($graphData);
        if(count($graphDataKeys) <= 0)
        {    
            return ["events"=>[], "graph"=>[], "availbleDates"=>[], "eventNotes" => []];
        }
        $min = intval($graphDataKeys[0]);
        $max = intval($graphDataKeys[count($graphDataKeys)-1]);
        
        $topMargin = -5;  
        $totalDays = [];
       for ($i = 1; $i <= 31; $i++) {
           array_push($totalDays, $i);
       }
       $totalDays = collect($totalDays);
        foreach ($event as $key => $value) {
            $logs = $value->prodcutPreview;
            if(count($logs) > 0){
                //Single Event Occurance Array in booleon Format
                $eventsOccurance = $totalDays->map(function ($day) use ($logs) {
                    foreach ($logs as $key => $log) {
                        if ($day == date("d", strtotime($log->occurrenceDate))) {
                            return true;
                        } //endif
                    } //end foreach
                    return false;
                }); //end map
                
                $first = $last = 0;
                $tempHoldEvent = [];
                $isEventClosed = false;
                $CurrentActualEvent = 0;
                $CurrentEvent = 0;
                $isCurrentEventSet = false;
                foreach ($eventsOccurance as $keyboolEve => $boolEve) {
                    if($boolEve) {
                        if($first == 0)
                            $first = $keyboolEve+1;

                        $last = $keyboolEve+1;
                        $isEventClosed = false;
                        // if($logs[$keyboolEve])
                        $isCurrentEventSet = true;
                        $eventsNotes[$value->id][$CurrentEvent][] =  $logs[$CurrentActualEvent]; 
                        $CurrentActualEvent++;
                    }
                    else {
                        if($first != 0){
                            $isEventClosed = true;
                            if($first < $min){
                                $first = $min;
                            }
                            if($last > $max){
                                $last = $max;
                            }
                            $events[] = [
                                $value->id, 
                                $value->eventName,
                                intval($first),
                                intval($last),
                                $topMargin,
                            ]; 
                            $first = $last = 0;
                        }
                        if($isCurrentEventSet){
                            $isCurrentEventSet = false;
                            $CurrentEvent++;
                        }
                    }
                }//end foreach
                if(!$isEventClosed){
                    if($first < $min){
                        $first = $min;
                    }
                    if($last > $max){
                        $last = $max;
                    }
                    $events[] = [
                        $value->id, 
                        $value->eventName,
                        intval($first),
                        intval($last),
                        $topMargin,
                    ];     
                    // $eventsNotes[] = $value;   
                }
                 
                $topMargin = $topMargin + 15;
            }
        }
        $finalEventNotes = [];
        foreach ($eventsNotes as $key => $subEventNotesArray) {
            foreach ($subEventNotesArray as $key1 => $value) {
                # code...
                $finalEventNotes[] = $value;
            }
        }
        return [
            "events"=>$events,
            "eventNotes"=>$finalEventNotes,
            "graph"=>$graphData,
            "availbleDates"=>$availableYears, 
            $year,
            $month,
            "eventsData"=>EventsModel::select("id","eventColor")->get()
        ];
      
    } //end function
    /******************************************Private functions************************************/

} //end class
