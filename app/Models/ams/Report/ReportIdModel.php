<?php

namespace App\Models\ams\Report;

use Illuminate\Database\Eloquent\Model;

class ReportIdModel extends Model
{
	protected $table = "tbl_ams_report_id";
	protected $primaryKey = 'id';
	protected $fillable = [
		'fkBatchId',
		'fkAccountId',
		'profileID',
		'fkConfigId',
		'reportId',
		'recordType',
		'reportType',
		'status',
		'statusDetails',
		'reportDate',
		'isDone',
		'creationDate'
	];
	public $timestamps = false;

	/**
	 * @param $getReportId
	 * @param $profileID
	 * @param $reportType
	 * @param $reportDateSingleDay
	 * @param $isDone
	 */
	public static function updateReportIdStatus($getReportId, $profileID, $reportType, $reportDateSingleDay, $isDone)
	{
		$reportId = ReportIdModel::find($getReportId);
		$reportId->profileID = $profileID;
		$reportId->reportType = $reportType;
		$reportId->reportDate = $reportDateSingleDay;
		$reportId->isDone = $isDone;
		$reportId->save();
	}
}
