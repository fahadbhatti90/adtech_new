import React from "react";
import moment from "moment";

export const profileInfo =(healthDate)=>[
    {
        name: 'Sr.',
        selector: 'serial',
        sortable: false,
        fixedHeader:true,
        wrap:true,
        minWidth: '30px',
        maxWidth: '60px'
    },
    {
        name: 'Profile Id',
        selector: 'profileId',
        sortable: false,
        wrap:true
    },

    {
        name: 'Profile Name',
        selector: 'name',
        sortable: false,
        wrap:true
    },
    {
        name: 'Marketplace',
        selector: 'countryCode',
        sortable: false,
        wrap:true
    },
    {
        name: 'Date',
        selector: 'creationDate',
        sortable: false,
        cell:row => <div>{row.creationDate.split(' ')[0]}</div>,
        wrap:true
    }
];

export const dataInformation =()=>[
    {
        name: 'Sr.',
        selector: 'serial',
        wrap:true,
        minWidth: '70px',
    },

    {
        name: 'Account ID',
        selector:'Account_Id',
        wrap:true,
        minWidth: '70px',
    },
    {
        name: 'Report Type Data',
        selector:'Report_Type_Data',
        wrap:true,
        minWidth: '178px',
    },
    {
        name: 'Repetitive Count',
        selector:'Repetitive_Count',
        wrap:true,
        minWidth: '50px',
    },
    {
        name: 'Date',
        cell:row => <div>{
            moment(row.reportDate).format("DD-MM-YYYY")
        }</div>,
        wrap:true,
        minWidth: '110px',
    }
];

export const reportIdError = () => [
    {
        name: 'Account ID',
        selector:'Account_Id',
        wrap:true,
        minWidth: '30px',
        maxWidth: '60px'
    },
    {
        name: 'Profile ID',
        selector:'Profile_id',
        wrap:true
    },
    {
        name: 'Report Type',
        selector:'Report_Type',
        wrap:true
    },
    {
        name: 'Date',
        selector:'Report_date',
        wrap:true,
        cell:row => <div>{
            moment(row.Report_date).format("DD-MM-YYYY")
        }</div>,
    },
]

export const reportLinkError = () => [
    {
        name: 'Account ID',
        selector:'Account_Id',
        wrap:true,
        minWidth: '30px',
        maxWidth: '60px'
    },
    {
        name: 'Profile ID',
        selector:'Profile_id',
        wrap:true,
        minWidth: '30px',
        maxWidth: '60px'
    },
    {
        name: 'Report Type',
        selector:'Report_Type',
        wrap:true
    },
    {
        name: 'Date',
        selector:'Report_date',
        cell:row => <div>{
            moment(row.Report_date).format("DD-MM-YYYY")
        }</div>,
        wrap:true
    },
]

export const informationLinks =()=>[
    {
        name: 'Sr.',
        selector: 'serial',
        wrap:true,
        minWidth: '30px',
        maxWidth: '60px'
    },
    {
        name: 'Report Type Link',
        selector: 'Report_Type_Link',
        wrap:true
    },
    {
        name: 'Status',
        selector: 'Status',
        wrap:true
    },
    {
        name: 'Repetitive Count',
        selector: 'Repetitive_Count',
        wrap:true
    },
    {
        name: 'Date',
        sortable: false,
        cell:row => <div>{
            moment(row.reportDate).format("DD-MM-YYYY")
        }</div>,
        wrap:true
    }
];

