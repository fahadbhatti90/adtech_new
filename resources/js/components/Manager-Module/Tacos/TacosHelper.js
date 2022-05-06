import DoneIcon from '@material-ui/icons/Done';
import moment from 'moment';
import React from 'react'
import ThemeSwitchBtn from '../../../general-components/ThemeSwitchBtn/ThemeSwitchBtn';
import ThemeTooltip from '../../../general-components/Tooltip/TooltipContainer';
import ActionBtns from './TacosHistory/ActionBtns';

export const SrCustomHead = (props) => {
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

export const getFilterColumnNames = (currentPage, prePage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick) => {
    return (getTableColumns(currentPage, prePage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick)).map(column => column.selector);
}
export const getScheduleFilterColumnNames = (currentPage, prePage, itemsToShow, onRowSelect, onIsActiveStatusChange) => {
    return (getScheduleTableColumns(currentPage, prePage, itemsToShow, onRowSelect, onIsActiveStatusChange))
        .map(column => column.selector)
        .filter(column => !["strCampaignId"].includes(column.selector));
}
export const getHistoryFilterColumnNames = (currentPage, prePage, itemsToShow) => {
    return (getHistoryTableColumns(currentPage, prePage, itemsToShow))
        .map(column => column.selector);
        // .filter(column => !["strCampaignId"].includes(column));
}
const getCategoryNameToolTip = (row) => {

    return <ThemeTooltip row={row}
                         tooltipContent={row.name ?? "NA"}
                         tooltipTarget={
                             <div
                                 fk-profile-id={row.fkProfileId}
                                 profile-id={row.profileId}
                                 campaign-id={row.strCampaignId}
                                 campaign-type={row.campaignType}
                                 className="RowTitle tooltipText"
                             >
                                 {row.name ?? "NA"}
                             </div>
                         }/>
}

const getSr = (currentPage, perPage, index, handleCheckBoxClick) => {
    return <>
        <div className="selectContainer" onClick={handleCheckBoxClick}>
            <div className="checkboxMiniContainer">
                <span><DoneIcon></DoneIcon></span>
            </div>
        </div>
        {(currentPage == 1 ? currentPage + (index) : ((currentPage - 1) * perPage) + (index + 1))}
    </>
}
export const getTableColumns = (currentPage, perPage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick) => {
    let cols = [
        {
            name: <SrCustomHead handleClick={handleSelectAllCheckBoxClick}/>,
            selector: 'Sr.#',
            sortable: false,
            cell: (row, index) => getSr(currentPage, perPage, index, handleCheckBoxClick),
            maxWidth: "100px"
        },
        {
            name: 'Campaign Name',
            selector: 'name',
            sortable: true,
            wrap: false,
            maxWidth: "300px",
            minWidth: "300px",
            cell: (row) => getCategoryNameToolTip(row)
        },
        {
            name: 'Campaign Type',
            selector: 'campaignType',
            sortable: true,
            maxWidth: "200px",
            minWidth: "200px"
        },
        {
            name: 'Status',
            selector: 'state',
            sortable: true,
            wrap: true,
        },

        {
            name: 'Category',
            selector: 'category',
            sortable: true,
            wrap: true,
        },
        {
            name: 'Strategy',
            selector: 'strategy',
            sortable: true,
            wrap: true,
        },
    ];
    let NewCols = [];
    NewCols.push(cols[0]);
    NewCols.push(cols[1]);
    NewCols.push(cols[2]);
    if (itemsToShow && itemsToShow.length > 0) {
        cols.map((item, index) => {
            if (itemsToShow.includes(index)) NewCols.push(item);
        })
    }
    return NewCols;
}

const getTaggedTooltipContent = (row, tags) => {
    const updatedTags = tags.map((tag, index) => {
        return <>
            <div key={tag} className="flex items-center">
                <span>{tag}</span>
            </div>
        </>
    });//end map funciton
    return <div className="ProductTableTooltip">
        {updatedTags}
    </div>
}
const getTaggedTooltipTarget = (tag) => {
    return <div className="tooltipText">
        {tag}
    </div>
}

const getTooltipTag = (row) => {
    if (row.tag && row.tag.length > 0) {
        let tagArray = row.tag.split(",").sort();
        if (tagArray.length > 0)
            return <ThemeTooltip
                row={row}
                tooltipContent={getTaggedTooltipContent(row, tagArray)}
                tooltipTarget={getTaggedTooltipTarget(row.tag)}
            />

        return tags;
    } else return "None";
}
export const getScheduleTableColumns = (currentPage, perPage, itemsToShow, onRowSelect, onIsActiveStatusChange) => {
    let cols = [
        {
            name: 'Sr.#',
            selector: 'Sr.#',
            sortable: false,
            cell: (row, index) => (currentPage == 1 ? currentPage + (index) : ((currentPage - 1) * perPage) + (index + 1)),
            maxWidth: "100px"
        },
        {
            name: 'Campaign Name',
            selector: 'name',
            sortable: true,
            wrap: false,
            maxWidth: "300px",
            minWidth: "300px",
            cell: (row) => getCategoryNameToolTip(row)
        },
        {
            name: 'ROAS-ACOS',
            selector: 'metric',
            sortable: true,
            wrap: true,
            maxWidth: "150px",
            minWidth: "150px"
        },
        {
            name: 'TACOS(%)',
            selector: 'tacos',
            sortable: true,
            maxWidth: "200px",
            minWidth: "200px"
        },
        {
            name: 'Min Bid',
            selector: 'min',
            sortable: true,
            wrap: true,
            cell: row => row.min === 0.00 ? "Empty" : row.min
        },
        {
            name: 'Max Bid',
            selector: 'max',
            sortable: true,
            wrap: true,
            cell: row => row.max === 0.00 ? "Empty" : row.max
        },
        {
            name: 'Campaign Type',
            selector: 'campaignType',
            sortable: true,
            wrap: true,
            maxWidth: "200px",
            minWidth: "200px"
        },
        {
            name: 'Tag',
            selector: 'tag',
            sortable: true,
            wrap: false,
            cell: getTooltipTag,
            maxWidth: "200px",
            minWidth: "200px"
        },
        {
            name: 'Strategy',
            selector: 'strategy',
            sortable: true,
            wrap: true,
        },
        {
            name: 'Category',
            selector: 'category',
            sortable: true,
            wrap: true,
        },
        {
            name: 'Start Date',
            selector: 'startDate',
            sortable: true,
            wrap: true,
            maxWidth: "200px",
            minWidth: "200px",
            cell: row => moment(row.startDate).format('YYYY-MM-DD')
        },
        {
            name: 'Active',
            selector: 'isActive',
            sortable: true,
            wrap: true,
            maxWidth: "150px",
            cell: row => <ThemeSwitchBtn
                checked={row.isActive}
                onChange={(e) => onIsActiveStatusChange(e, row)}
            />
        },
        {
            name: 'Action',
            selector: 'strCampaignId',
            sortable: true,
            maxWidth: "150px",
            cell: row => <ActionBtns
                row={row}
                deleteSchedule={() => onRowSelect("delete", row)}
                editSchedule={() => onRowSelect("edit", row)}
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
    NewCols.push(cols[5]);
    if (itemsToShow && itemsToShow.length > 0) {
        cols.map((item, index) => {
            if (itemsToShow.includes(index)) NewCols.push(item);
        })
    }
    NewCols.push(cols[cols.length - 2]);
    NewCols.push(cols[cols.length - 1]);
    return NewCols;
}
export const getHistoryColumnOptions=(itemsToShow)=>{
    const totalCols = getHistoryTableColumns(1, 10, itemsToShow);
    return totalCols.splice(9, totalCols.length - 9).map((item, index) => {
        return ({label: item.name, value: 9 + index})
    });
}
export const getHistoryTableColumns = (currentPage, perPage, itemsToShow, onListClick = () => {}) => {
    const historyTableDefaultColumns = [
        {
            name: 'Sr.#',
            selector: 'Sr.#',
            sortable: false,
            cell: (row, index) => (currentPage == 1 ? currentPage + (index) : ((currentPage - 1) * perPage) + (index + 1)),
            maxWidth: "100px"
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
            wrap: false,
            maxWidth:"170px",
            minWidth:"170px",
            cell: (row) => getCategoryNameToolTip(row)
        },
        {
            name: 'Include',
            selector: 'inlcude',
            sortable: true,
            wrap:true,
            cell: row => <div onClick={()=>onListClick(row)}>List</div>
        },
        {
            name: 'ROAS-ACOS',
            selector: 'metric',
            sortable: true,
            wrap: true,
            maxWidth: "150px",
            minWidth: "150px"
        },
        {
            name: 'TACOS(%)',
            selector: 'tacos',
            sortable: true,
            maxWidth: "200px",
            minWidth: "200px"
        },
        {
            name: 'Min Bid',
            selector: 'min',
            sortable: true,
            wrap: true,
            cell: row => row.min === 0.00 ? "Empty" : row.min
        },
        {
            name: 'Max Bid',
            selector: 'max',
            sortable: true,
            wrap: true,
            cell: row => row.max === 0.00 ? "Empty" : row.max
        },
        {
            name: 'Campaign Type',
            selector: 'campaignType',
            sortable: true,
            wrap: true,
            maxWidth: "200px",
            minWidth: "200px"
        },
        {
            name: 'Tag',
            selector: 'tag',
            sortable: true,
            wrap: false,
            cell: getTooltipTag,
            maxWidth: "200px",
            minWidth: "200px"
        },
        {
            name: 'Strategy',
            selector: 'strategy',
            sortable: true,
            wrap: true,
        },
        {
            name: 'Category',
            selector: 'category',
            sortable: true,
            wrap: true,
        },
        {
            name: 'Active',
            selector: 'isActive',
            sortable: true,
            wrap: true,
            maxWidth: "150px",
            cell: row => row.isActive ? "True" : "False"
        },
        {
            name: 'Log Date AND Time',
            selector: 'updatedAt',
            sortable: true,
            wrap: true,
            maxWidth:"200px",
            minWidth:"200px",
        }
    ];
    let NewCols = [];
    NewCols.push(historyTableDefaultColumns[0]);
    NewCols.push(historyTableDefaultColumns[1]);
    NewCols.push(historyTableDefaultColumns[2]);
    NewCols.push(historyTableDefaultColumns[3]);
    NewCols.push(historyTableDefaultColumns[4]);
    NewCols.push(historyTableDefaultColumns[5]);
    NewCols.push(historyTableDefaultColumns[6]);
    NewCols.push(historyTableDefaultColumns[7]);
    NewCols.push(historyTableDefaultColumns[8]);
    if (itemsToShow && itemsToShow.length > 0) {
        historyTableDefaultColumns.map((item, index) => {
            if (itemsToShow.includes(index)) NewCols.push(item);
        })
    }
    return NewCols;
}

export const countProducts = (campaignIds, productName) => {

    return Object.entries(campaignIds).map((obj, index) => {

        if (obj[1].campaignType == productName) {
            return obj[1].campaignType
        }
    }).filter(item => item !== undefined);
}