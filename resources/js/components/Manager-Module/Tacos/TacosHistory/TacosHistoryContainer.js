import React, { useEffect, useState } from 'react';
import { withStyles } from '@material-ui/core/styles';
import "./Tacos.scss"
import {unAssignSingleTag} from './apiCalls';
import SvgLoader from "./../../../general-components/SvgLoader";
import productTableFilter from './../../../app-resources/svgs/manager/productTableFilter.svg';
import IconBtn from "./../../../general-components/IconBtn";
import Filter from './Filter';
import { Helmet } from 'react-helmet';
import { 
    manageAllRowSelection,
    handleSelectedCheckboxesStateChange,
} from './../ProductTable/Helpers/ProductTableHelper';
import { 
    getTableColumns,
    getFilterColumnNames,
 } from './TacosHelper';
import {
    TACOS_CAMPAIGN_FETCH_DATA_URL
} from './apiCalls';
import ServerSideDatatable from '../../../general-components/ServerSideDatatable/ServerSideDatatable';
import useFilter from './../../ProductTable/CustomHooks/useFilter';
import TMContainer from '../../../general-components/TagManager/TagManagerContainer/TMContainer';
import TacosPopupContainer from './TacosPopup/TacosPopupContainer';

const classStyles = theme => ({
    mainClass:{

    },
    productTable: {
        
    },
    ptTooltip:{
        color: "#000",
        backgroundColor: "rgb(255 255 255 / 90%)",
        boxShadow: "1px 1px 10px #0000003b",
        overflow: "hidden",
    },
    ptArrow:{
        color: "#fff"
    },
});

const TacosHistoryContainer = (props) => {
    const dataTableRef = React.useRef();
 
    const handleTagPopUp = ()=>{
        console.log("TagPopupClicled",selectedArray.length)
        if(selectedArray.length > 0 && state.showTagPopUp) return;
        setState((prevState)=>({
            ...prevState,
            showTagPopUp:selectedArray.length > 0
        }))
    }
    const {
        selectedArray,
        selectedObject,
        isAllSelected,
        handleCheckBoxClick,
        handleRowClickEvent,
        handleIfAllRowsSelected,
        resetSelectedCheckBoxState,
        handleSelectAllCheckBoxClick
    } = useMultiRowSelector({
        handleTagPopUp
    });

    const {
        filter,
        showFilter,
        applyFilterOnTable,
        helperLoadFilterAgain,
        handleApplyFilterButtonClick,
    } = useFilter(
            dataTableRef, 
            resetSelectedAsinsState, 
            { 
                filter: {
                    childBrand:null,
                    category:null,
                    strategy:null,
                    status:null,
                    itemsToShow:[6, 7, 8, 9],
                }
            }
        );

    const [state, setState] = useState({
        toggledClearRows: false,
        showTagPopUp:false,
        showSingleTagLoader:false,
        wasFilterOpen:false,
        isDataTableRowClick:false,
        currentPage:1,
        sortDirection:"",
        currentRowsPerPage:10,
    });
    
    const TableColumns = (itemsToShow) => {
        return getTableColumns(state.currentPage, state.currentRowsPerPage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick)
    }
    useEffect(() => {     
        if($(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr .selectContainer").length >= state.currentRowsPerPage)
        {
           $(".taggedDataTable .rdt_TableHeadRow .selectContainer").addClass("active")
        }
        else{
            $(".taggedDataTable .rdt_TableHeadRow .selectContainer").removeClass("active");
        }
    }, [isAllSelected, state.currentPage, state.sortDirection, state.currentRowsPerPage])
    const getTooltipTag = (row) => {
        if(row.tag && row.tag.length > 0)
        {
           let tagArray = row.tag.split(",").sort();
        //    if( tagArray.length > 0 )
        //         return <ProductTableTooltip row={row} tooltipContent={getTaggedTooltipContent(row,tagArray, handleSingleTagUnAssignment)} tooltipTarget={getTaggedTooltipTarget(tagArray)}/>

           return row.tag;
        }
        else return "None"; 
    }
    const handleSingleTagUnAssignment = (e) => {
        let singleTagAjaxData = {};
        if(state.showSingleTagLoader) return;

        setState((prevState)=>({
            ...prevState,
            showSingleTagLoader : !state.showSingleTagLoader
        }));
        let targetEl = typeof $(e.target).attr("asin") == "undefined" ? $(e.target).parents("svg") : e.target;
        $(targetEl).parents(".ProductTableTooltip").find(".singleTagLoader").show();
        singleTagAjaxData.asin = $(targetEl).attr("asin");
        singleTagAjaxData.accountId = $(targetEl).attr("account-id");
        singleTagAjaxData.tagId = $(targetEl).attr("tag-id");
        unAssignSingleTag(
            singleTagAjaxData,
            () =>{
                setState((prevState)=>({
                    ...prevState,
                    showSingleTagLoader : !state.showSingleTagLoader,
                }));
                showDataTableLoader();
                $(targetEl).parents(".ProductTableTooltip").find(".singleTagLoader").hide();
            },
            (error) =>{
                console.log(error);
                setState((prevState)=>({
                    ...prevState,
                    showSingleTagLoader : !state.showSingleTagLoader
                }));
                $(targetEl).parents(".ProductTableTooltip").find(".singleTagLoader").hide();
            }
        );
    }
    const handleOnSortDataTable = (column, sortDirection, event) => {
        setState((prevState)=>({
            ...prevState,
            sortDirection,
        }))
        handleSelectedCheckboxesStateChange({selectedArray}, handleIfAllRowsSelected, "campaign-id");
    }
    const onTacosPopupCloseButtonClicked = (close)=>{
        if(close) {
            resetSelectedAsinsState();
        }
    }
    const resetSelectedAsinsState = ()=>{
        setState((prevState)=>{
           return {
                ...prevState,
                showTagPopUp:false,
                showSingleTagLoader:false,
            }
        })
        resetSelectedCheckBoxState();
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
                handleSelectedCheckboxesStateChange({selectedArray}, handleIfAllRowsSelected, "campaign-id");
            }
            else
            {
                resetSelectedAsinsState();
            }
        });
    }
    const relaodDatatable = ()=>{
        resetSelectedAsinsState();
    }
    const handleOnDataTableSearch = () => {
        resetSelectedAsinsState();
    }
        return (
            <>
            <Helmet>
                <title>Pulse Advertising | TACOS Bidding Rule</title>
            </Helmet>
            <div className="datTableProduct">
                <ServerSideDatatable 
                    ref = {dataTableRef}
                    url = {TACOS_CAMPAIGN_FETCH_DATA_URL}
                    title="TACOS History"
                    customClass="productTable"
                    showButtons
                    columns={TableColumns(filter ? filter.itemsToShow : [6,7,8,9])}
                    handleRowClickEvent = {()=>{}}
                />
            </div>
            
            {
                showTagPopUp ?
                <TMContainer 
                    dots={selectedArray.length} 
                    selectedObject = {selectedObject}
                    onTacosPopupClose = {onTacosPopupCloseButtonClicked} 
                    showDataTableLoader = {showDataTableLoader} 
                    helperLoadFilterAgain = {helperLoadFilterAgain}
                    showFilter={showFilter}
                    type="1"
                    ChildComponent = {TacosPopupContainer}
                />
                :
                null
            } 
            </>
        )
};

export default withStyles(classStyles)(TacosHistoryContainer)




const useMultiRowSelector = ({
    handleTagPopUp
}) => {
    const [state, setState] = useState({
        isAllSelected:false,
        selectedArray:[],
        selectedObject:{},
    });
    
    useEffect(() => {
        if(state.selectedArray.length <=0 ){
           manageAllRowSelection(false);
           handleTagPopUp();
        }
    }, [state.selectedArray, state.isAllSelected])


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

        let fkProfileId = $(rowTitle).attr("fk-profile-id");
        let profileId = $(rowTitle).attr("profile-id");
        let campaignId = $(rowTitle).attr("campaign-id");
        
        $(tr).toggleClass("activeTr");

        if($(tr).hasClass("activeTr")){
            selectedArray.push(campaignId);
            setState((prevState)=>({
                ...prevState,
                selectedArray,
                selectedObject : {
                    ...prevState.selectedObject,
                    [campaignId]:{
                        fkProfileId,
                        profileId,
                        campaignId,
                    }
                  }
            }));
        }
        else{
            selectedArray.remove(campaignId);
            const {selectedObject} = state;
            delete selectedObject[campaignId];
            setState((prevState)=>({
                ...prevState,
                selectedArray,
                selectedObject
            }))
        }
        handleIfAllRowsSelected();
    }

    
    const handleIfAllRowsSelected = () => {
        // handleing if all checkbox selected.
        const headerCheckBox = $(".taggedDataTable .rdt_TableHeadRow .selectContainer");
        const trs = $(".taggedDataTable .rdt_TableBody .rdt_TableRow");
        console.log("AllRowSelected",trs.length,  $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length, trs.length == $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length)
        if(trs.length >= $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length){
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
    const resetSelectedCheckBoxState = ()=>{
        setState((prevState)=>{
           return {
                ...prevState,
                selectedArray: [],
                selectedObject: {},
                isAllSelected: false,
            }
        })
    }
    return {
        ...state,
        handleCheckBoxClick,
        handleIfAllRowsSelected,
        resetSelectedCheckBoxState,
        handleSelectAllCheckBoxClick
    }
}