<?php

namespace App\Models\DayPartingModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DayPartingSchedulesTime extends Model
{
    public $table = 'tbl_ams_day_parting_schedules_time_campaigns';
    public $timestamps = false;

    public static function insertScheduleTimings($statusData)
    {
        foreach ($statusData as $data){
            $existData = DayPartingSchedulesTime::select('*')
                ->where('creationDate', $data['creationDate'])
                ->where('day', $data['day'])
                ->where('fkScheduleId', $data['fkScheduleId'])
                ->exists();

            if (!$existData) {
                try {
                    DayPartingSchedulesTime::insert($data);
                    Log::info('Schedule Cron Statuses Insertion');
                } catch (\Illuminate\Database\QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            } else {
                DayPartingSchedulesTime::
                where('fkScheduleId', $data['fkScheduleId'])
                    ->where('creationDate', $data['creationDate'])
                    ->where('day', $data['day'])
                    ->update($data);
            }
        }

    }
}
