import React, {useEffect, useState} from "react";
import {makeStyles, withStyles} from '@material-ui/core/styles';
import "./CampaignTaggingTable.scss";
import ServerSideDatatable from "../../../general-components/ServerSideDatatable/ServerSideDatatable";
import {CAMPAIGN_FETCH_DATA_URL, unAssignSingleTag} from "./apiCalls";
import useFilter from "../ProductTable/CustomHooks/useFilter";
import {handleSelectedCheckboxesStateChange, manageAllRowSelection} from "../ProductTable/Helpers/ProductTableHelper";
import {getCampaignTagColumnNames, getCampaignTagHeaderColumnNames} from "./CampaingTagHelper";
import TagManager from "../../../general-components/TagManager/TagManager";
import IconBtn from "../../../general-components/IconBtn";
import SvgLoader from "../../../general-components/SvgLoader";
import productTableFilter from "../../../app-resources/svgs/manager/productTableFilter.svg";
import {Filter} from "./Filter/Filter";


const useStyles = makeStyles(theme => ({
    root: {
        width: '100%',
        '& > * + *': {
            marginTop: theme.spacing(2),
        },
    },
}));
const classStyles = theme => ({
    mainClass: {},
    campaignTaggingTable: {},
    ctTooltip: {
        color: "#000",
        backgroundColor: "rgb(255 255 255 / 90%)",
        boxShadow: "1px 1px 10px #0000003b",
        // overflow: "hidden",
    },
    ctArrow: {
        color: "#fff"
    },
});

function CampaignTaggingDataTable(props) {

    const dataTableRef = React.useRef();
    const [showSingleTagLoader, setShowSingleTagLoader] = useState(false);
    const {
        filter,
        showFilter,
        applyFilterOnTable,
        helperLoadFilterAgain,
        handleApplyFilterButtonClick
    } = useFilter(
        dataTableRef,
        resetSelectedAsinsState,
        {
            filter: {
                childBrand: null
            }
        })
    const [state, setState] = useState({
        showTagPopUp: false,
        isDataTableRowClick: false,
        currentPage: 1,
        soreDirection: "",
        currentRowPerPage: 10,
        showHistoryLoader: false,
        isAllSelected:false,
        selectedArray:[],
        selectedObject:{},
        totalSelectedRows:0,
    })

    const reloadDatatable = () => {
        resetSelectedAsinsState();
    }
    const TableColumns = (itemsToShow) => {
        return getCampaignTagHeaderColumnNames(state.currentPage, state.currentRowPerPage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick, handleSingleTagUnAssignment)
    }
    useEffect(() => {

        if ($(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr .selectContainer").length >= state.currentRowPerPage) {
            $(".taggedDataTable .rdt_TableHeadRow .selectContainer").addClass("active")
        } else {
            $(".taggedDataTable .rdt_TableHeadRow .selectContainer").removeClass("active");

        }
    }, [state.isAllSelected, state.currentPage, state.sortDirection, state.currentRowPerPage])

    const handleIfAllRowsSelected = () => {
        // handleing if all checkbox selected.
        const headerCheckBox = $(".taggedDataTable .rdt_TableHeadRow .selectContainer");
        const trs = $(".taggedDataTable .rdt_TableBody .rdt_TableRow");
        if(trs.length == $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length){
            setState((prevState)=>{
                $(headerCheckBox).addClass("active");
                return {
                    ...prevState,
                    isAllSelected:true
                }
            })
        }
        else{
            setState((prevState)=>{
                $(headerCheckBox).removeClass("active");
                return {
                    ...prevState,
                    isAllSelected:false
                }
            })
        }
        handleTagPopUp();
    }
    const handleTagPopUp = ()=>{
        if(state.selectedArray.length > 0 && state.showTagPopUp) return;
        setState((prevState)=>({
            ...prevState,
            showTagPopUp:state.selectedArray.length > 0
        }))
    }
    const handleOnChangePage = (currentPage, totalRows) => {
        setState((prevState)=>({
            ...prevState, currentPage
        }));
        handleSelectedCheckboxesStateChange( state, handleIfAllRowsSelected, 'campaign-id');
    }

    const handleOnSortDataTable = (column, sortDirection, event) => {
        setState((prevState)=>({
            ...prevState,
            sortDirection,
        }))
        handleSelectedCheckboxesStateChange(state, handleIfAllRowsSelected, 'campaign-id');
    }

    const handleOnDataTableSearch = () => {
        resetSelectedAsinsState();
    }

    useEffect(() => {
        if(state.selectedArray.length <=0 ){
            manageAllRowSelection(false);
            handleTagPopUp();
        }
    }, [state.selectedArray, state.isAllSelected])

    const resetSelectedAsinsState = ()=>{
        setState((prevState)=>{
            return {
                ...prevState,
                selectedArray: [],
                selectedObject: {},
                isAllSelected: false,
                showTagPopUp:false
            }
        })
    }

    const handleSelectAllCheckBoxClick = (e) => {
        e.stopPropagation();
        e.preventDefault();
        manageAllRowSelection(!state.isAllSelected);

        setState((prevState)=>({
            ...prevState,
            isAllSelected:!state.isAllSelected
        }))
    }

    const handleCheckBoxClick = (e) => {
        const checkBox = e.target;
        const tr = $(checkBox).parents(".rdt_TableRow");
        const rowTitle = $(tr).find(".RowTitle");
        const {selectedArray} = state;

        let fkAccountId = $(rowTitle).attr("fk-account-id");
        let campaignId = $(rowTitle).attr("campaign-id");

        $(tr).toggleClass("activeTr");

        if ($(tr).hasClass("activeTr")) {
            selectedArray.push(campaignId);
            setState((prevState) => ({
                ...prevState,
                selectedArray,
                selectedObject: {
                    ...prevState.selectedObject,
                    [campaignId]: {
                        accountId: fkAccountId
                    }
                }
            }));
        } else {
            selectedArray.remove(campaignId);
            const {selectedObject} = state;
            delete selectedObject[campaignId];
            setState((prevState) => ({
                ...prevState,
                selectedArray,
                selectedObject
            }))
        }
        handleIfAllRowsSelected();
    }

    const showDataTableLoader = (shouldReset = true) => {
        dataTableRef.current.helperReloadDataTable(()=>{

            if(!shouldReset){
                setState((prevState)=>{
                    return {
                        ...prevState,
                        isAllSelected: false,
                    }
                })
                handleSelectedCheckboxesStateChange(state, handleIfAllRowsSelected);
            }
            else
            {
                resetSelectedAsinsState();
            }
        });
    }

    const handleSingleTagUnAssignment = (e) => {
        let singleTagAjaxData = {};

        //if (showSingleTagLoader) return;
        setShowSingleTagLoader(!showSingleTagLoader)

        let targetEl = typeof $(e.target).attr("campaign-id") == "undefined" ? $(e.target).parents("svg") : e.target;
        $(targetEl).parents(".CampaignTaggingTooltip").find(".singleTagLoader").show();
        singleTagAjaxData.campaignId = $(targetEl).attr("campaign-id");
        singleTagAjaxData.accountId = $(targetEl).attr("account-id");
        singleTagAjaxData.tagType = $(targetEl).attr("tag-type");
        singleTagAjaxData.tagId = $(targetEl).attr("tag-id");

        unAssignSingleTag(
            singleTagAjaxData,
            (response) => {
                setState((prevState) => ({
                    ...prevState
                }))
                setShowSingleTagLoader(!showSingleTagLoader)
                showDataTableLoader();
                $(targetEl).parents(".CampaignTaggingTooltip").find(".singleTagLoader").hide();
            },
            (error) => {
                setState((prevState) => ({
                    ...prevState
                }))
                setShowSingleTagLoader(!showSingleTagLoader)

                $(targetEl).parents(".CampaignTaggingTooltip").find(".singleTagLoader").hide();
            }
        );
    }

    const onTagPopupCloseButtonClicked = (close)=>{
        if(close) {
            resetSelectedAsinsState();
        }
    }

    const handleApplyHistoryFilterButtonClick = () =>{

    }
    const { showTagPopUp } = state;
    return (
        <>
            <div style={{display: 'table', tableLayout: 'fixed', width: '100%'}} className="campaignTaggingTable ">
                <ServerSideDatatable
                    ref={dataTableRef}
                    url={CAMPAIGN_FETCH_DATA_URL}
                    dataForAjax={
                        {
                            columnsToSearch: getCampaignTagColumnNames(state.currentPage, state.currentRowPerPage, filter ? filter : "", handleSelectAllCheckBoxClick, handleCheckBoxClick, handleSingleTagUnAssignment),
                            childBrand: filter.childBrand && filter.childBrand.value,
                            tag: filter.tag && filter.tag,
                            campaignName: filter.campaignName && filter.campaignName
                        }
                    }
                    title="Campaign Tagging"
                    showButtons
                    buttons={
                        <>
                            <IconBtn
                                BtnLabel={"Filter"}
                                variant={"contained"}
                                icon={<SvgLoader src={productTableFilter}/>}
                                onClick={handleApplyFilterButtonClick}
                            />
                        </>
                    }
                    otherSection={
                        <>
                            {
                                showFilter ?
                                    <Filter
                                        filter={filter}
                                        applyFilterOnTable={applyFilterOnTable}
                                        reloadDataTableCampaignTag={reloadDatatable}
                                    />
                                    : null
                            }
                        </>
                    }
                    columns={TableColumns(filter ? filter.itemsToShow : [3, 4, 5, 6])}
                    handleRowClickEvent={() => {
                    }}
                    callBackOnChangePage={handleOnChangePage}
                    callBackOnSortDataTable={handleOnSortDataTable}
                    onDataTableSearch={handleOnDataTableSearch}
                    callBackOnChangeRowsPerPage={
                        (currentRowsPerPage, currentPage) => {
                            setState((prevState) => ({
                                ...prevState, currentRowsPerPage
                            }));
                            handleSelectedCheckboxesStateChange(state, handleIfAllRowsSelected, "campaign-id")
                        }
                    }
                />
            </div>

            {showTagPopUp ?
                <TagManager
                    dots={state.selectedArray.length}
                    selectedObject = {state.selectedObject}
                    onTagPopupClose = {onTagPopupCloseButtonClicked}
                    showDataTableLoader = {showDataTableLoader}
                    helperLoadFilterAgain = {helperLoadFilterAgain}
                    showFilter={showFilter}
                    orignalData={state.selectedObject}
                    type="2"
                />
                :null}
        </>
    )
}

export default withStyles(classStyles)(CampaignTaggingDataTable)