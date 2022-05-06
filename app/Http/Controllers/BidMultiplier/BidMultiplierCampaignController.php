<?php

namespace App\Http\Controllers\BidMultiplier;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ams\campaign\CampaignList;
use App\Models\AccountModels\AccountModel;
use App\Libraries\DataTableHelpers\DataTableHelpers;
use App\Models\BidMultiplierModels\BidMultiplierTracker;
use App\Models\BidMultiplierModels\BidMultiplierListModel;
use App\Models\BidMultiplierModels\BidMulitplierListActivityTrackerModel;

class BidMultiplierCampaignController extends Controller
{
    public function index(Request $request): array
    {
        $options = $request->input('options');
        $columnsToSearch = $request->input('columnsToSearch');

        $bidCampaignTN = CampaignList::getTableName();
        $bidTN = BidMultiplierListModel::getTableName();

        $searchDetails = $this->getSearchNSelectColumns($columnsToSearch, $bidCampaignTN, $bidTN);

        $query = $this->getCampaignTableQuery($searchDetails["columnsToSelect"], $request, $bidCampaignTN, $bidTN);

        $query = $this->handleIfFilterApplied($query, $request, $bidCampaignTN, $bidTN);

        $paginatedData = DataTableHelpers::GetProductTablePaginatedData($query, $options, $searchDetails["columnsToSearch"], null, null);

        $paginatedData["status"] = true;

        return $paginatedData;
    }

    private function handleIfFilterApplied($query, $request, $bidCampaignTN): array
    {

        if (isset($request->status)) {
            $query["data"]->where(function ($query) use ($bidCampaignTN, $request) {
                $query->orWhere("$bidCampaignTN.state", $request->status);
            });
            $query["count"]->where(function ($query) use ($bidCampaignTN, $request) {
                $query->orWhere("$bidCampaignTN.state", $request->status);
            });
        }

        return $query;
    }

    private function getCampaignTableQuery($columnsToSelect, $request, $bidCampaignTN, $bidTN): array
    {

        $accounts = AccountModel::where("fkBrandId", getBrandId())
            ->where("fkAccountType", 1)
            ->select("fkId")
            ->get()
            ->map(function ($item, $value) use ($request) {
                if (isset($request->childBrand)) {
                    if ($request->childBrand == $item->fkId)
                        return $item->fkId;
                } else {
                    return $item->fkId;
                }
            });


        $dataQuery = CampaignList::selectRaw(implode(",", $columnsToSelect))
            // ->leftJoin("$bidTN", "$bidCampaignTN.campaignId", "=", "$bidTN.campaignId")
            // ->whereRaw("$bidTN.campaignId IS NULL")
            ->whereIn("$bidCampaignTN.fkProfileId", $accounts)
            ->whereRaw("$bidCampaignTN.campaignType != 'sponsoredDisplay'");
        //        dd($dataQuery->toSql()); // Show results of log
        $countQuery = CampaignList::selectRaw("COUNT(*) OVER () AS TotalRecords")
            // ->leftJoin("$bidTN", "$bidCampaignTN.campaignId", "=", "$bidTN.campaignId")
            // ->whereRaw("$bidTN.campaignId IS NULL")
            ->whereIn("$bidCampaignTN.fkProfileId", $accounts)
            ->whereRaw("$bidCampaignTN.campaignType != 'sponsoredDisplay'");

        return [
            "data" => $dataQuery,
            "count" => $countQuery
        ];
    }

    private function getSearchNSelectColumns($requestedColumnsToSearch, $bidCampaignTN, $bidTN): array
    {
        $columnsToSearch = [];
        $columnsToSelect = [];
        $columnsToSelect[] = "$bidCampaignTN.strCampaignId";
        $columnsToSelect[] = "$bidCampaignTN.fkProfileId";
        $columnsToSelect[] = "$bidCampaignTN.profileId";
        $columnsToSelect[] = "'NA' as category";
        $columnsToSelect[] = "$bidCampaignTN.strategy";
        foreach ($requestedColumnsToSearch as $key => $value) {
            if ($key == 0) continue;

            switch ($value) {
                case 'category':
                case 'strategy':
                    # code...
                    //$columnsToSelect[] = "$value";
                    break;

                default:
                    # code...
                    $columnsToSearch[] = "$bidCampaignTN." . $value;
                    $columnsToSelect[] = "$bidCampaignTN." . $value;
                    break;
            }
        }
        return [
            "columnsToSearch" => $columnsToSearch,
            "columnsToSelect" => $columnsToSelect
        ];
    }

    public function childBrands(Request $request): array
    {
        $response = [
            "status" => true
        ];
        $account = AccountModel::with("ams:id,profileId,name,type")
            ->where("fkBrandId", getBrandId())
            ->where("fkAccountType", 1)
            ->select("id", "fkId")
            ->get();
        $accountIds = $account->map(function ($item, $value) use ($request) {
            return $item->id;
        });

        $response["childBrands"] = $account;

        return $response;
    }

    public function CampaignSchedule(Request $request): array
    {

        $options = $request->input('options');
        $columnsToSearch = $request->input('columnsToSearch');

        $bidCampaignTN = CampaignList::getTableName();
        $bidTN = BidMultiplierListModel::getTableName();

        $searchDetails = $this->getScheduleSearchNSelectColumns($columnsToSearch, $bidCampaignTN, $bidTN);
        $query = $this->getCampaignScheduleTableQuery(
            $searchDetails["columnsToSelect"],
            $request,
            $bidCampaignTN,
            $bidTN
        );

        $query = $this->handleIfScheduleFilterApplied($query, $request, $bidCampaignTN, $bidTN);

        $paginatedData = DataTableHelpers::GetProductTablePaginatedData(
            $query,
            $options,
            $searchDetails["columnsToSearch"],
            "id",
            "desc"
        );

        $paginatedData["status"] = true;

        return $paginatedData;

    }
    public function CampaignHistory(Request $request): array
    {

        $options = $request->input('options');
        $columnsToSearch = $request->input('columnsToSearch');

        $bidCampaignTN = CampaignList::getTableName();
        $bidActivityTrackerTN = BidMultiplierTracker::getTableName();
        $bidListActivityTrackerTN = BidMulitplierListActivityTrackerModel::getTableName();
        $userTN = User::getTableName();
        $searchDetails = $this->getHistorySearchNSelectColumns(
            $columnsToSearch, 
            $bidCampaignTN, 
            $bidListActivityTrackerTN, 
            $bidActivityTrackerTN,
            $userTN
        );
        $query = $this->getCampaignHistoryTableQuery(
            $searchDetails["columnsToSelect"],
            $request,
            $bidCampaignTN, 
            $bidListActivityTrackerTN, 
            $bidActivityTrackerTN,
            $userTN
        );

        $query = $this->handleIfHistoryFilterApplied($query, $request, $bidListActivityTrackerTN, $userTN);

        $paginatedData = DataTableHelpers::GetProductTablePaginatedData(
            $query,
            $options,
            $searchDetails["columnsToSearch"],
            "id",
            "desc"
        );

        $paginatedData["status"] = true;

        return $paginatedData;

    }

    private function getScheduleSearchNSelectColumns($requestedColumnsToSearch, $bidCampaignTN, $bidTN): array
    {

        $columnsToSelect = [];
        $columnsToSelect[] = "$bidCampaignTN.strCampaignId";
        $columnsToSelect[] = "$bidCampaignTN.fkProfileId";
        $columnsToSelect[] = "$bidCampaignTN.profileId";
        $columnsToSelect[] = "'NA' as category";
        $columnsToSelect[] = "$bidCampaignTN.strategy";
        //$columnsToSelect[] = "'NA' as strategy";
        $columnsToSelect[] = "$bidCampaignTN.name";
        $columnsToSelect[] = "$bidTN.id";

        $columnsToSearch = [];
        $columnsToSearch[] = "$bidCampaignTN.name";

        foreach ($requestedColumnsToSearch as $key => $value) {
            if ($key == 0) continue;

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
                    $columnsToSearch[] = "$bidTN." . $value;
                    $columnsToSelect[] = "$bidTN." . $value;
                    break;
                case 'endDate':
                    $columnsToSearch[] = "$bidTN." . $value;
                    $columnsToSelect[] = "$bidTN." . $value;
                    break;
                case 'state':
                    ($value == 'state') ? 'done' : 'else';
                    $columnsToSearch[] = "$bidCampaignTN." . $value;
                    $columnsToSelect[] = "$bidCampaignTN." . $value;
                    break;

                default:
                    # code...
                    ($value == 'state') ? 'done default' : 'default else';
                    $columnsToSearch[] = "$bidTN." . $value;
                    $columnsToSelect[] = "$bidTN." . $value;
                    break;
            }
        }

        return ["columnsToSearch" => $columnsToSearch, "columnsToSelect" => $columnsToSelect];
    }
    private function getHistorySearchNSelectColumns($requestedColumnsToSearch, $bidCampaignTN, $bidListActivityTrackerTN, $bidActivityTrackerTN, $userTN): array
    {

        $columnsToSelect = [];
        $columnsToSelect[] = "$bidCampaignTN.strCampaignId";
        $columnsToSelect[] = "$bidCampaignTN.profileId";
        $columnsToSelect[] = "$bidListActivityTrackerTN.fkMultiplierListId";
        $columnsToSelect[] = "'NA' as include";
        // $columnsToSelect[] = "0 as optimizationValue";
        $columnsToSelect[] = "$bidCampaignTN.name";
        $columnsToSelect[] = "$bidListActivityTrackerTN.id";

        $columnsToSearch = [];
        $columnsToSearch[] = "$bidCampaignTN.name";

        foreach ($requestedColumnsToSearch as $key => $value) {
            if ($key == 0) continue;

            switch ($value) {
                case 'email':
                    $columnsToSearch[] = "$userTN." . $value;
                    $columnsToSelect[] = "$userTN." . $value;
                    break;
                case 'userName':
                    $columnsToSearch[] = "$userTN.name" ;
                    $columnsToSelect[] = "$userTN.name as userName";
                    break;
                case 'strCampaignId':
                case 'name':
                case 'state':
                    # code...
                    // $columnsToSelect[] = "$value";
                    $columnsToSearch[] = "$bidCampaignTN." . $value;
                    $columnsToSelect[] = "$bidCampaignTN." . $value;
                    break;

                default:
                    # code...
                    $columnsToSearch[] = "$bidListActivityTrackerTN." . $value;
                    $columnsToSelect[] = "$bidListActivityTrackerTN." . $value;
                    break;
            }
        }

        return ["columnsToSearch" => $columnsToSearch, "columnsToSelect" => $columnsToSelect];
    }

    private function getCampaignScheduleTableQuery($columnsToSelect, $request, $bidCampaignTN, $bidTN): array
    {
        $accounts = AccountModel::where("fkBrandId", getBrandId())
            ->where("fkAccountType", 1)
            ->select("fkId")
            ->get()
            ->map(function ($item, $value) use ($request) {
                if (isset($request->childBrand)) {
                    if ($request->childBrand == $item->fkId)
                        return $item->fkId;
                } else {
                    return $item->fkId;
                }
            });


        $dataQuery = CampaignList::selectRaw(implode(",", $columnsToSelect))
            ->leftJoin("$bidTN", "$bidCampaignTN.campaignId", "=", "$bidTN.campaignId")
            ->whereRaw("$bidTN.campaignId IS NOT NULL")
            ->whereIn("$bidCampaignTN.fkProfileId", $accounts)
            ->whereRaw("$bidCampaignTN.campaignType != 'sponsoredDisplay'");


        $countQuery = CampaignList::selectRaw("COUNT(*) OVER () AS TotalRecords")
            ->leftJoin("$bidTN", "$bidCampaignTN.campaignId", "=", "$bidTN.campaignId")
            ->whereRaw("$bidTN.campaignId IS NOT NULL")
            ->whereIn("$bidCampaignTN.fkProfileId", $accounts);

        return [
            "data" => $dataQuery,
            "count" => $countQuery
        ];
    }
    private function getCampaignHistoryTableQuery($columnsToSelect, $request, $bidCampaignTN, $bidListActivityTrackerTN, $bidActivityTrackerTN, $userTN): array
    {
        $accounts = AccountModel::where("fkBrandId", getBrandId())
            ->where("fkAccountType", 1)
            ->select("fkId")
            ->get()
            ->map(function ($item, $value) use ($request) {
                if (isset($request->childBrand)) {
                    if ($request->childBrand == $item->fkId)
                        return $item->fkId;
                } else {
                    return $item->fkId;
                }
            });


        $dataQuery = BidMulitplierListActivityTrackerModel::selectRaw(implode(",", $columnsToSelect))
            ->leftJoin("$bidCampaignTN", "$bidListActivityTrackerTN.campaignId", "=", "$bidCampaignTN.campaignId")
            ->leftJoin("$userTN", "$userTN.id", "=", "$bidListActivityTrackerTN.userID")
            ->whereRaw("$bidListActivityTrackerTN.campaignId IS NOT NULL")
            ->whereIn("$bidCampaignTN.fkProfileId", $accounts)
            ->whereRaw("$bidCampaignTN.campaignType != 'sponsoredDisplay'");


        $countQuery = BidMulitplierListActivityTrackerModel::selectRaw("COUNT(*) OVER () AS TotalRecords")
            ->leftJoin("$bidCampaignTN", "$bidListActivityTrackerTN.campaignId", "=", "$bidCampaignTN.campaignId")
            ->leftJoin("$userTN", "$userTN.id", "=", "$bidListActivityTrackerTN.userID")
            ->whereRaw("$bidListActivityTrackerTN.campaignId IS NOT NULL")
            ->whereIn("$bidCampaignTN.fkProfileId", $accounts)
            ->whereRaw("$bidCampaignTN.campaignType != 'sponsoredDisplay'");

        return [
            "data" => $dataQuery,
            "count" => $countQuery
        ];
    }

    private function handleIfScheduleFilterApplied($query, $request, $bidCampaignTN, $bidTN): array
    {

        if (isset($request->status)) {
            $query["data"]->where(function ($query) use ($bidCampaignTN, $request) {
                $query->orWhere("$bidCampaignTN.state", $request->status);
            });
            $query["count"]->where(function ($query) use ($bidCampaignTN, $request) {
                $query->orWhere("$bidCampaignTN.state", $request->status);
            });
        }

        if (isset($request->startDate) && !is_null($request->startDate)) {
            $query["data"]->where(function ($query) use ($bidTN, $request) {
                $query->orWhere("$bidTN.startDate", ">=", $request->startDate);
            });
            $query["count"]->where(function ($query) use ($bidTN, $request) {
                $query->orWhere("$bidTN.startDate", ">=", $request->startDate);
            });
        }

        if (isset($request->endDate) && !is_null($request->endDate)) {
            $query["data"]->where(function ($query) use ($bidTN, $request) {
                $query->orWhere("$bidTN.endDate", "<=", $request->endDate);
            });
            $query["count"]->where(function ($query) use ($bidTN, $request) {
                $query->orWhere("$bidTN.endDate", "<=", $request->endDate);
            });
        }

        return $query;
    }
    private function handleIfHistoryFilterApplied($query, $request, $bidListActivityTrackerTN): array
    {
        if (isset($request->startDate) && !is_null($request->startDate)) {
            $query["data"]->where(function ($query) use ($bidListActivityTrackerTN, $request) {
                $query->orWhere("$bidListActivityTrackerTN.startDate", ">=", $request->startDate);
            });
            $query["count"]->where(function ($query) use ($bidListActivityTrackerTN, $request) {
                $query->orWhere("$bidListActivityTrackerTN.startDate", ">=", $request->startDate);
            });
        }

        if (isset($request->endDate) && !is_null($request->endDate)) {
            $query["data"]->where(function ($query) use ($bidListActivityTrackerTN, $request) {
                $query->orWhere("$bidListActivityTrackerTN.endDate", "<=", $request->endDate);
            });
            $query["count"]->where(function ($query) use ($bidListActivityTrackerTN, $request) {
                $query->orWhere("$bidListActivityTrackerTN.endDate", "<=", $request->endDate);
            });
        }

        return $query;
    }
}
