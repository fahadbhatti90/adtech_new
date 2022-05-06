import axios from 'axios';
import React from 'react';

export const HEALTH_DASHBOARD_URL = window.baseUrl + "/health-dashboard-data";

export function getHealthDashboard(params,callback, errorcallback) {
    // Get All Profiles Associated with master brand
    axios.post(HEALTH_DASHBOARD_URL + '?_=' + new Date().getTime(), params)
        .then(res => {


            if (callback != null) {

                let mandatoryReportId = res.data.getReportIdMandatory.map(function(obj){

                    return +obj.total_report_id;
                });
                let mandatoryReportTypeId = res.data.getReportIdMandatory.map(function(obj){

                    return obj.report_type_id;
                });

                let getReportId = res.data.getReportId.map(function(obj){
                    return +obj.total_report_id;
                });
                let getReportTypeId = res.data.getReportId.map(function(params){

                    return params.report_type_id;
                });

                let ReportType = mandatoryReportTypeId.filter(x => getReportTypeId.includes(x));
                callback(res.data);
            }
        })
        .catch(err => {
            if (errorcallback != null) {
                errorcallback(err);
            }
        })
}