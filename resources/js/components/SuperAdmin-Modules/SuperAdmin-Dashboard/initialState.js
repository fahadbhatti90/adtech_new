import React from 'react';
export const initialState = {
    totalReportIdCount:0,
    totalReportsCount:0,
    newProfileCount:0,
    activeProfilesCount:0,
    inactiveProfileCount:0,
    profileIncompatibleCount:0,
    agencyType:0,
    areaChartData:[],
    barChartData:[],
    reportTypeCategories:[],
    profileValidateData:[],
    profileValidateOriginalData:[],
    profileLoading: false,
    profileValidateTotalRows: 0,
    profileValidatePerPage: 10,
    linkDuplicationData:[],
    linkDuplicationOriginalData:[],
    linkDuplicationLoading: false,
    linkDuplicationTotalRows: 0,
    linkDuplicationPerPage: 5,
    dataDuplicationData:[],
    dataDuplicationOriginalData:[],
    dataDuplicationLoading: false,
    dataDuplicationTotalRows: 0,
    dataDuplicationPerPage: 5,
    reportIdErrorData:[],
    reportIdErrorOriginalData:[],
    reportIdErrorLoading: false,
    reportIdErrorTotalRows: 0,
    reportIdErrorPerPage: 10,
    reportLinkErrorData:[],
    reportLinkErrorOriginalData:[],
    reportLinkErrorLoading: false,
    reportLinkErrorTotalRows: 0,
    reportLinkErrorPerPage: 10,
    dateRangeObj: {
        startDate: new Date(),
        endDate: new Date(),
        key: 'selection',
    },
    showDRP: false,
}