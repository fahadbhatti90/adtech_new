import DoneIcon from "@material-ui/icons/Done";
import React from "react";
import ThemeTooltip from "../../../general-components/Tooltip/TooltipContainer";
import CloseIcon from "@material-ui/icons/Close";
import LinearProgress from '@material-ui/core/LinearProgress';
import {unAssignSingleTag} from "./apiCalls";

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

const getCampaignNameTooltip = (row) => {

    let campaignName = row.name && row.name.length > 0
        ? row.name.length > 70
            ? row.name.slice(0, 67) + "..."
            : row.name
        : "NA"

    let campaignNameFull = row.name && row.name.length > 0 ? row.name : "NA"
    return <ThemeTooltip row={row}
                         tooltipContent={campaignNameFull}
                         tooltipTarget={
                             <div
                                 fk-account-id={row.fkAccountId}
                                 campaign-id={row.campaignId}
                                 className="RowTitle tooltipText"
                             >
                                 {campaignName}
                             </div>
                         }/>

}

export function getAccountColumnValue(row) {

    return <ThemeTooltip row={row}
                         tooltipContent={row.overrideLabel ? row.overrideLabel : row.accounts}
                         tooltipTarget={
                             <div
                                 fk-account-id={row.fkAccountId}
                                 campaign-id={row.campaignId}
                                 className="RowTitle tooltipText"
                             >
                                 {row.overrideLabel ? row.overrideLabel : row.accounts}
                             </div>
                         }/>
}

export function getCampaignTagColumnNames(currentPage, perPage, itemToShow, handleAllCheckBox, handleCheckBoxClick, handleSingleTagUnAssignment) {
    return (getCampaignTagHeaderColumnNames(currentPage, perPage, itemToShow, handleAllCheckBox, handleCheckBoxClick, handleSingleTagUnAssignment)).map(column => column.selector)
}


export function getTooltipTag(row, handleSingleTagUnAssignment) {

    if (row.tag != null) {
        let tagType = row.tagType.split(",")[0];
        return <ThemeTooltip
            row={row}
            tooltipContent={getCampTagTooltipContent(row, handleSingleTagUnAssignment)}
            tooltipTarget={
                <div className="RowTitle tooltipText">
                              <span>
                                  {row.tag.split(",")[0]} {getTagTypeName(tagType)}...


                            </span>
                </div>
            }/>

    } else {
        return "None"
    }
}

export function getTagTypeName(type) {

    switch (type) {
        case "1":
            return "(Product Type)"
            break;
        case "2":
            return "(Strategy Type)"
            break;
        default:
            return "(Custom Type)";
            break;
    }
}

export function getCampTagTooltipContent(row, handleSingleTagUnAssignment) {

    if (row.tag != null) {

        let tagName = row.tag.split(",");
        let fkTagIds = row.fkTagIds.split(",");
        let tagType = row.tagType.split(",");

        const tags = tagName.map((tag, index) => {

            return <div key={(tag + index + tagName[index] + tagName[index])} className="flex items-center singleTagDelete">
                <span>{tag} {getTagTypeName(tagType[index])} </span>

                <CloseIcon
                    campaign-id={row.campaignId}
                    account-id={row.fkAccountId}
                    tag-id={fkTagIds[index]}
                    tag-type={tagType[index]}
                    className="cursor-pointer"
                    onClick={handleSingleTagUnAssignment}
                />
            </div>
        });//end map function
        return <div className="CampaignTaggingTooltip">
            <div className="singleTagLoader absolute left-0 top-0 w-full hidden">
                <LinearProgress/>
            </div>
            {tags}
        </div>
    }


}


export function getCampaignTagHeaderColumnNames(currentPage, perPage, itemToShow, handleAllCheckBox, handleCheckBoxClick, handleSingleTagUnAssignment) {

    return [
        {
            name: <SrCustomHead handleClick={handleAllCheckBox}/>,
            selector: 'Sr.#',
            sortable: false,
            cell: (row, index) => {
                return getSr(currentPage, perPage, index, handleCheckBoxClick)
            },
            maxWidth: "100px"
        },
        {
            name: 'Campaign Name',
            selector: 'name',
            sortable: true,
            wrap: false,
            maxWidth: "300px",
            minWidth: "300px",
            cell: (row) => getCampaignNameTooltip(row)
        },
        {
            name: 'Child Brand',
            selector: 'accounts',
            sortable: true,
            wrap: true,
            cell: (row) => getAccountColumnValue(row)
        },
        {
            name: 'Tags',
            selector: 'tag',
            sortable: true,
            wrap: true,
            cell: (row) => getTooltipTag(row, handleSingleTagUnAssignment),
        },
    ]

}