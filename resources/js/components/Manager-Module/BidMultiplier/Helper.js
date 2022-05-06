import React from "react";
import moment from 'moment';
import DoneIcon from "@material-ui/icons/Done";
import ThemeTooltip from "../../../general-components/Tooltip/TooltipContainer";
import ThemeSwitchBtn from "../../../general-components/ThemeSwitchBtn/ThemeSwitchBtn";
import ActionBtn from "../Tacos/TacosHistory/ActionBtns";

export const SrCustomHead = (props)=>{
    return (
        <>
            <div className={`selectContainer`} onClick={props.handleClick}>
                <div className="checkboxMiniContainer justify-center items-center">
                    <span className="flex justify-center items-center"><DoneIcon></DoneIcon></span>
                </div>
            </div>
            Sr.#
        </>
    )
}
export const getFilterColumnNames = (currentPage, prePage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick)=>{
    return (getTableColumns(currentPage, prePage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick)).map(column => column.selector);
}
const getSr = (currentPage, perPage, index, handleCheckBoxClick) => {
    return <>
        <div className="selectContainer" onClick={handleCheckBoxClick}>
            <div className="checkboxMiniContainer">
                <span><DoneIcon></DoneIcon></span>
            </div>
        </div>{ (currentPage == 1 ? currentPage + (index) : ((currentPage-1) * perPage)  + (index + 1))}
    </>
}
const getCategoryNameToolTip = (row) => {
    return <ThemeTooltip row={row}
                         tooltipContent={row.name ?? "NA"}
                         tooltipTarget={
                             <div
                                 fk-profile-id={row.fkProfileId}
                                 profile-id={row.profileId}
                                 campaign-id={row.strCampaignId}
                                 className="RowTitle tooltipText"
                             >
                                 {row.name ?? "NA"}
                             </div>
                         }/>
}
export const getTableColumns = (currentPage, perPage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick)=>{
    let cols = [
        {
            name: <SrCustomHead handleClick = {handleSelectAllCheckBoxClick}/>,
            selector: 'Sr.#',
            sortable: false,
            cell:(row , index)=> getSr(currentPage, perPage , index, handleCheckBoxClick) ,
            maxWidth:"100px"
        },
        {
            name: 'Campaign Name',
            selector: 'name',
            sortable: true,
            wrap:false,
            maxWidth:"300px",
            minWidth:"300px",
            cell:(row)=>getCategoryNameToolTip(row)
        },
        {
            name: 'Campaign Type',
            selector: 'campaignType',
            sortable: true,
            maxWidth:"200px",
            minWidth:"200px"
        },
        {
            name: 'Status',
            selector: 'state',
            sortable: true,
            wrap:true,
        },

        {
            name: 'Category',
            selector: 'category',
            sortable: true,
            wrap:true,
        },
        {
            name: 'Strategy',
            selector: 'strategy',
            sortable: true,
            wrap:true,
        },
    ];
    let NewCols = [];
    NewCols.push(cols[0]);
    NewCols.push(cols[1]);
    NewCols.push(cols[2]);
    if(itemsToShow && itemsToShow.length > 0){
        cols.map((item,index)=>{
            if(itemsToShow.includes(index)) NewCols.push(item);
        })
    }
    return NewCols;
}

// bid multiplier Schedule


export const getScheduleFilterColumnNames = (currentPage, prePage, itemsToShow, onRowSelect, onIsActiveStatusChange)=>{
    return (getScheduleTableColumns(currentPage, prePage, itemsToShow, onRowSelect, onIsActiveStatusChange))
        .map(column => column.selector)
        .filter(column => !["strCampaignId","isActive"].includes(column.selector));
}

export const getScheduleTableColumns = (currentPage, perPage, itemsToShow, onRowSelect, onIsActiveStatusChange) => {
    let cols = [
        {
            name: 'Sr.#',
            selector: 'Sr.#',
            sortable: false,
            cell:(row , index)=> (currentPage == 1 ? currentPage + (index) : ((currentPage-1) * perPage)  + (index + 1)),
            maxWidth:"100px"
        },
        {
            name: 'Campaign Name',
            selector: 'name',
            sortable: true,
            wrap:false,
            maxWidth:"300px",
            minWidth:"300px",
            cell:(row)=>getCategoryNameToolTip(row)
        },
        {
            name: 'Bid',
            selector: 'bid',
            sortable: true,
            wrap:true,
            cell: row => row.bid
        },
        {
            name: 'Start Date',
            selector: 'startDate',
            sortable: true,
            wrap:true,
            maxWidth:"200px",
            minWidth:"200px",
            cell: row => moment(row.startDate).format('MM-DD-YYYY')
        },
        {
            name: 'End Date',
            selector: 'endDate',
            sortable: true,
            wrap:true,
            maxWidth:"200px",
            minWidth:"200px",
            cell: row => moment(row.endDate).format('MM-DD-YYYY')
        },
        {
            name: 'Status',
            selector: 'state',
            sortable: true,
            wrap:true,
        },
        {
            name: 'Strategy',
            selector: 'strategy',
            sortable: true,
            wrap:true,
        },
        {
            name: 'Category',
            selector: 'category',
            sortable: true,
            wrap:true,
        },
        {
            name: 'Active',
            selector: 'isActive',
            sortable: true,
            wrap:true,
            maxWidth:"150px",
            cell: row => {
                let EndDate = moment(row.endDate).format('l');
                let currentDate = moment(new Date()).format('l');

                return (
                    <ThemeSwitchBtn
                        checked = {(row.isActive == 1)}
                        onChange={(e)=> onIsActiveStatusChange(e, row)}
                        disabled={new Date(EndDate) <  new Date(currentDate)}
                    />
                )
            }
        },
        {
            name: 'Action',
            selector: 'strCampaignId',
            sortable: true,
            maxWidth:"150px",
            cell: row => <ActionBtn
                row={row}
                deleteSchedule={()=> onRowSelect("delete", row)}
                editSchedule = {()=> onRowSelect("edit", row)}
            />,
            ignoreRowClick: true,
            allowOverflow: true,
            button: true,
        },
    ];

    let NewCols = [];
    NewCols.push(cols[0]);
    NewCols.push(cols[1]);
    NewCols.push(cols[2]);
    NewCols.push(cols[3]);
    NewCols.push(cols[4]);
    if(itemsToShow && itemsToShow.length > 0){
        cols.map((item,index)=>{
            if(itemsToShow.includes(index)) NewCols.push(item);
        })
    }
    NewCols.push(cols[cols.length - 2]);
    NewCols.push(cols[cols.length - 1]);
    return NewCols;
}
// bid multiplier history


export const getHistoryFilterColumnNames = (currentPage, prePage)=>{
    return (getHistoryTableColumns(currentPage, prePage))
        .map(column => column.selector)
        .filter(column => !["inlcude"].includes(column));
}

export const getHistoryTableColumns = (currentPage, perPage, onListClick = ()=>{}) => {
    let cols = [
        {
            name: 'Sr.#',
            selector: 'Sr.#',
            sortable: false,
            cell:(row , index)=> (currentPage == 1 ? currentPage + (index) : ((currentPage-1) * perPage)  + (index + 1)),
            maxWidth:"100px"
        },
        
        {
            name: 'Name',
            selector: 'userName',
            sortable: true,
            wrap:true,
        },
        {
            name: 'Email',
            selector: 'email',
            sortable: true,
            wrap:true,
            maxWidth:"170px",
            minWidth:"170px",
            cell:row => <ThemeTooltip row={row}
            tooltipContent={row.email ?? "NA"}
            tooltipTarget={
                <div
                    className="tooltipText"
                >
                    {row.email ?? "NA"}
                </div>
            }/>
        },
        {
            name: 'Campaign Name',
            selector: 'name',
            sortable: true,
            wrap:false,
            maxWidth:"170px",
            minWidth:"170px",
            className:['campaignHeaderClass'],
            cell:(row)=>getCategoryNameToolTip(row)

        },
        {
            name: 'Include',
            selector: 'inlcude',
            sortable: true,
            wrap:true,
            cell: row => <div onClick={()=>onListClick(row)}>List</div>
        },
        {
            name: 'Bid',
            selector: 'bid',
            sortable: true,
            wrap:true,
            cell: row => row.bid
        },
        {
            name: 'Start Date',
            selector: 'startDate',
            sortable: true,
            wrap:true,
            maxWidth:"120px",
            minWidth:"120px",
            cell: row => moment(row.startDate).format('MM-DD-YYYY')
        },
        {
            name: 'End Date',
            selector: 'endDate',
            sortable: true,
            wrap:true,
            maxWidth:"120px",
            minWidth:"120px",
            cell: row => moment(row.endDate).format('MM-DD-YYYY')
        },
        {
            name: 'Status',
            selector: 'isActive',
            sortable: true,
            wrap:true,
            cell: row => row.isActive ? "True" : "False"
        },
        {
            name: 'Log Date AND Time',
            selector: 'updatedAt',
            maxWidth:"200px",
            minWidth:"200px",
            sortable: true,
            wrap:true,
        }
    ];

    return cols;
}

export const getNumericValFromString = (props) => {
    let value =  props.replace ( /[^\d]/g, '' );
    let parseValue = parseInt(value, 10);
    let anotherValue = '' + parseValue + '';
    return anotherValue;
}