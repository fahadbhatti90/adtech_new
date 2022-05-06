<?php

namespace App\Http\Controllers\ClientAuth;

use App\Libraries\DataTableHelpers\DataTableHelpers;
use App\Models\ams\campaign\CampaignList;
use App\Models\Inventory\InventoryBrandsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Models\AccountModels\AccountModel;
use App\Models\ClientModels\CampaignsIdModel;
use App\Models\ClientModels\CampaignTagsModel;
use App\Models\ClientModels\CampaignTagsAssignmentModel;
use App\Models\Vissuals\VissualsProfile;


class CompaignTaggingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.manager');
    } //end constructor

    /**
     * campainStrategyType
     *
     * @return void
     */
    public function compaignStrategyType()
    {
        return view("client.campaignTag")
            ->with("pageTitle", "Campaign Tagging");
    } //end function

    private function getTagsQuery($tagIds)
    {

        $tagTn = CampaignTagsAssignmentModel::getCompleteTableName();
        if($tagIds && count($tagIds) > 0){
            $tagIdsStr = implode(",", $tagIds);
            return \DB::raw("(SELECT GROUP_CONCAT(t1.fkTagId) AS fkTagIds, GROUP_CONCAT(t1.type) AS tagType,GROUP_CONCAT(t1.fkAccountId)AS fkAccountId, t1.campaignId, GROUP_CONCAT(t1.tag) AS tag FROM $tagTn t1 where t1.fkTagId IN ($tagIdsStr) GROUP BY campaignId) tagTb ");
        }
        return \DB::raw("(SELECT GROUP_CONCAT(t1.fkTagId) AS fkTagIds, GROUP_CONCAT(t1.type) AS tagType,GROUP_CONCAT(t1.fkAccountId)AS fkAccountId, t1.campaignId, GROUP_CONCAT(t1.tag) AS tag FROM $tagTn t1 GROUP BY campaignId) tagTb");
    }

    /**
     * compaignList
     *
     * @param Request $request
     * @return void
     */
    public function compaignList(Request $request)
    {
        $options = $request->input('options');
        $columnsToSearch = $request->input('columnsToSearch');
        $CampaignsTN = CampaignList::getTableName();
        $tagTn = CampaignTagsAssignmentModel::getCompleteTableName();
        $inventoryBrandModel = InventoryBrandsModel::getTableName();
        $accountModel = AccountModel::getTableName();
        $amsProfiles = VissualsProfile::getTableName();
        $searchDetails = $this->getSearchNSelectColumns($request->all(), $columnsToSearch,  $CampaignsTN, $inventoryBrandModel, $amsProfiles, $accountModel);

        $query = $this->campaignTaggingQuery($searchDetails["columnsToSelect"],$request->tag, $CampaignsTN, $inventoryBrandModel, $accountModel, $amsProfiles, $request);

        $query = $this->handleIfFilterApplied($query, $request, $CampaignsTN);

        $paginatedData = DataTableHelpers::GetProductTablePaginatedData(
            $query,
            $options,
            $searchDetails["columnsToSearch"],
            null,
            null,
            null
        );


        $paginatedData["status"] = true;
        return $paginatedData;

    } //end function

    private function handleIfFilterApplied($query, $request, $CampaignsTN){

        if($request->campaignName && count($request->campaignName) > 0){
            $campaignIds = array_map('intval', $request->campaignName);
            $query["data"]->whereIn("$CampaignsTN.id", $campaignIds);
            $query["count"]->whereIn("$CampaignsTN.id", $campaignIds);

        }
        if(($request->tag && count($request->tag) > 0)){
            $tagIds = array_map('intval', $request->tag);
            $query["data"]->WhereIn("tagTb.fkTagIds", $tagIds);
            $query["count"]->WhereIn("tagTb.fkTagIds", $tagIds);
            //$query["data"]->whereNotNull("tagTb.fkTagIds");
            //$query["count"]->whereNotNull("tagTb.fkTagIds");
        }

        return $query;
    }
    private function campaignTaggingQuery($columnsToSelect,  $tagIds, $CampaignsTN, $inventoryBrandModel, $accountModel, $amsProfiles, $request)
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

        if (isset($request['childBrand']) || isset($request['tag']) || isset($request['campaignName'])){

            $dataQuery = CampaignsIdModel::selectRaw(implode(",",$columnsToSelect))
                ->leftJoin($this->getTagsQuery($tagIds), "$CampaignsTN.campaignId", "=", DB::raw("tagTb.campaignId  COLLATE utf8mb4_unicode_ci"))
                ->leftJoin("$amsProfiles","$amsProfiles.id", "=", "$CampaignsTN.fkProfileId")
                ->leftJoin("$accountModel", "$accountModel.fkId", "=", "$amsProfiles.id")
                ->whereIn(DB::raw($CampaignsTN . ".fkProfileId"), $accounts);

            $countQuery = CampaignsIdModel::selectRaw("COUNT(*) OVER () AS TotalRecords")
                ->leftJoin($this->getTagsQuery($tagIds), "$CampaignsTN.campaignId", "=", DB::raw("tagTb.campaignId  COLLATE utf8mb4_unicode_ci"))
                ->leftJoin("$amsProfiles","$amsProfiles.id", "=", "$CampaignsTN.fkProfileId")
                ->leftJoin("$accountModel", "$accountModel.fkId", "=", "$amsProfiles.id")
                ->whereIn(DB::raw($CampaignsTN . ".fkProfileId"), $accounts);
        }else{

            $dataQuery = CampaignsIdModel::selectRaw(implode(",",$columnsToSelect))
                ->leftJoin($this->getTagsQuery($tagIds), "$CampaignsTN.campaignId", "=", DB::raw("tagTb.campaignId  COLLATE utf8mb4_unicode_ci"))
                ->leftJoin("$amsProfiles","$amsProfiles.id", "=", "$CampaignsTN.fkProfileId")
                ->leftJoin("$accountModel", "$accountModel.fkId", "=", "$amsProfiles.id")
                ->leftJoin("$inventoryBrandModel", "$inventoryBrandModel.fkAccountId", "=",  "$accountModel.id")
                ->whereIn(DB::raw($CampaignsTN . ".fkProfileId"), $accounts);

            $countQuery = CampaignsIdModel::selectRaw("COUNT(*) OVER () AS TotalRecords")
                ->leftJoin($this->getTagsQuery($tagIds), "$CampaignsTN.campaignId", "=", DB::raw("tagTb.campaignId  COLLATE utf8mb4_unicode_ci"))
                ->leftJoin("$amsProfiles","$amsProfiles.id", "=", "$CampaignsTN.fkProfileId")
                ->leftJoin("$accountModel", "$accountModel.fkId", "=", "$amsProfiles.id")
                ->leftJoin("$inventoryBrandModel", "$inventoryBrandModel.fkAccountId", "=",  "$accountModel.id")
                ->whereIn(DB::raw($CampaignsTN . ".fkProfileId"), $accounts);
        }


        return [
            "data" => $dataQuery,
            "count" => $countQuery
        ];
    }

    private function getSearchNSelectColumns($requestInput, $requestedColumnsToSearch, $CampaignsTN, $inventoryBrandModel, $amsProfiles, $accountModel) : array
    {



        $columnsToSearch = [];
        if (!isset($requestInput['childBrand']) || !isset($requestInput['tag']) || !isset($requestInput['campaignName'])){
            $columnsToSearch[]= "$inventoryBrandModel.overrideLabel";
            $columnsToSelect[]= "$inventoryBrandModel.overrideLabel";
        }

        $columnsToSearch[]= "$CampaignsTN.name";
        $columnsToSearch[]= "$amsProfiles.name";

        $columnsToSelect = [];
        $columnsToSelect[]= "$amsProfiles.name AS accounts";
        $columnsToSelect[]= "$accountModel.id AS fkAccountId";
        $columnsToSelect[]= "$CampaignsTN.strCampaignId AS campaignId";
        $columnsToSelect[]= "$CampaignsTN.name";
        $columnsToSelect[]= "$CampaignsTN.fkProfileId";
        foreach ($requestedColumnsToSearch as $key => $value) {
            if ($key == 0) continue;

            switch ($value) {
                case 'tag':
                    $columnsToSearch[] = "tagTb.".$value;
                    $columnsToSelect[] = "tagTb.".$value;
                    $columnsToSelect[] = "tagTb.fkTagIds";
                    $columnsToSelect[] = "tagTb.tagType";
                case 'accounts':
                case 'name':
                    # code...
                    //$columnsToSelect[] = "$value";
                    break;

                default:
                    # code...
                    $columnsToSearch[] = "$CampaignsTN." . $value;
                    $columnsToSelect[] = "$CampaignsTN." . $value;
                    break;
            }
        }
        return [
            "columnsToSearch" => $columnsToSearch,
            "columnsToSelect" => $columnsToSelect
        ];
    }
    /*****************************************Tags*************************************************/
    /**
     * addTag
     *
     * @param Request $request
     * @return void
     */
    public function addTag(Request $request)
    {
        $tag = $request->tag;
        $tag = CampaignTagsModel::firstOrCreate([
            "fkManagerId" => auth()->user()->id,
            "tag" => $tag,
        ]);
        return [
            "status" => true
        ];

    } //end function

    /**
     * editTag
     *
     * @param ProductTableTagsModel $tag
     * @param Request $request
     * @return void
     */
    public function editTag(CampaignTagsModel $tag, Request $request)
    {
        if (CampaignTagsModel::isDuplicateTag($request->tagId, $request->tagName)) {
            return [
                "status" => false,
                "message" => "Tag Already Exists",
            ];
        }
        $tag->tag = $request->tagName;
        if (count($tag->compaigns) > 0)
            $tag->compaigns()->where("fkTagId", $request->tagId)->update([
                "tag" => $request->tagName
            ]);
        if ($tag->save()) {
            return [
                "status" => true,
                "message" => "Tag updated successfully",
                "tags" => $this->getAllTags()["data"]
            ];
        }
        return [
            "status" => false,
            "message" => "Fail To Edit Tag",
        ];
    } //end function+

    public function getCampaignTaggingFilter() : array
    {
        $response = [
            "status" => true
        ];
        $account = AccountModel::with("ams:id,profileId,name,type")
            ->where("fkBrandId", getBrandId())
            ->where("fkAccountType", 1)
            ->select("id", "fkId")
            ->get();

        $response["childBrands"] = $account;
        $response["getAllTags"] = $this->getAllTags();

        return $response;
    }

    public function getCampaignNames(Request $request) : array
    {
        $response = [
            "status" => true
        ];
        $response["getCampaignNames"] = CampaignList::where('fkProfileId', $request->input('fkProfileId'))->get(['id','name']);

        return $response;
    }


    /**
     * getAllTags
     *
     * @return void
     */
    public function getAllTags()
    {
        Artisan::call('cache:clear');
        $tags = CampaignTagsModel::withCount("compaigns")->where("fkManagerId", auth()->user()->id)->get();
        return [
            "status" => true,
            "data" => $tags
        ];

    } //end function

    public function unAssignSingleTag(Request $request)
    {
        $campaignId = $request->campaignId;
        $accountId = $request->accountId;
        $tagType = $request->tagType;
        $tagId = $request->tagId;
        $status = CampaignTagsAssignmentModel::where("campaignId", intval($campaignId))
            ->where("fkAccountId", intval($accountId))
            ->where("fkTagId", intval($tagId))
            ->where("type", $tagType)
            ->delete();
        return [
            "status" => $status
        ];
    }//end function


    /**
     * getAllTagsToDelete
     *
     * @return void
     */
    public function getAllTagsToDelete(Request $request)
    {
        $asins = $request->asins;

        $asinArray = [];
        foreach ($asins as $key => $value) {
            $asinArray[] = $key;
        }
        $accounts = AccountModel::select("id")->get()->map(function ($item, $value) {
            return $item->id;
        });
        if (!CampaignTagsAssignmentModel::whereIn("campaignId", $asinArray)->whereIn("fkAccountId", $accounts)->exists())
            return [
                "status" => true
            ];
        $status = CampaignTagsAssignmentModel::whereIn("campaignId", $asinArray)->whereIn("fkAccountId", $accounts)->delete();

        return [
            "status" => $status
        ];

    } //end function

    /**
     * asignTag
     *
     * @param Request $request
     * @return void
     */
    public function asignTag(Request $request)
    {
        $data = [];
        $asinToUpdate = [];
        foreach ($request->asins as $key => $campaingData) {
            foreach ($request->tagsObj as $tagId => $tagName) {
                array_push($data, [
                    "campaignId" => $key,
                    'fkAccountId' => $campaingData["accountId"],
                    'fkTagId' => $tagId,
                    'tag' => $tagName,
                    'type' => $request->type,
                    "uniqueColumn" => $tagId . "|" . $key . "|" . $campaingData["accountId"] . "|" . $request->type,
                    "createdAt" => date('Y-m-d H:i:s'),
                    "updatedAt" => date('Y-m-d H:i:s'),
                ]);
            }
            array_push($asinToUpdate, $key);
        }
        CampaignTagsAssignmentModel::insertOrUpdate($data);
        return [
            "status" => true
        ];
    } //end function

    /**
     * deleteTag
     *
     * @param CampaignTagsModel $tag
     * @return void
     */
    public function deleteTag(CampaignTagsModel $tag)
    {
        DB::beginTransaction();
        try {
            CampaignTagsAssignmentModel::where("fkTagId", $tag->id)->delete();
            $tag->delete();
            DB::commit();
            return [
                "status" => true,
                "tags" => $this->getAllTags()["data"]
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                "status" => false,
            ];
        } //end catch
    } //end function

    /*****************************************Tags*************************************************/

}
