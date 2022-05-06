<?php

namespace App\Http\Controllers\SuperAdmin\HealthDashboard;

use App\Models\HealthDashboard\AmsDataDuplication;
use App\Models\HealthDashboard\AmsLinkDuplication;
use App\Models\HealthDashboard\AmsProfilesValidate;
use App\Models\HealthDashboard\AmsScoreBoard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HealthDashboard extends Controller
{
    /**
     * HealthDashboard constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getHealthDashboardData(Request $request)
    {
        $healthDate = $request->input('healthDate');
        $formatDate = date('Ymd', strtotime($healthDate));
        $data = [];
        $data['getPopulateLink'] = [];
        $data['getReportId'] = [];
        $scoreData = DB::connection("mysql")->select("CALL spAMSPopulateScoreCard (?)",[$formatDate]);
        $data['getReportIdMandatory'] = DB::select("CALL spAMSPopulateReportIdMandatory (?)",[$formatDate]);
        $data['getReportId'] = DB::select("CALL spAMSPopulateReportId (?)",[$formatDate]);
        $data['getPopulateLink'] = DB::select("CALL spAMSPopulateLink (?)",[$formatDate]);
        $data['profile_info'] = DB::select("CALL spAMSPopulateProfile (?)",[$formatDate]);
        $data['link_duplication'] = DB::connection("mysql")->select("CALL spAMSPopulateLinkDuplication (?)",[$formatDate]);
        $data['data_duplication'] = DB::connection("mysql")->select("CALL spAMSPopulateDataDuplication (?)",[$formatDate]);
        $data['report_id_error'] = DB::connection("mysql")->select("CALL spAMSPopulateReportIdError (?)",[$formatDate]);
        $data['report_link_error'] = DB::connection("mysql")->select("CALL spAMSPopulateLinkError (?)",[$formatDate]);
        if(!empty($scoreData)){
            $data['score_card'] = $scoreData[0];
        }

        return $data;
    }
}
