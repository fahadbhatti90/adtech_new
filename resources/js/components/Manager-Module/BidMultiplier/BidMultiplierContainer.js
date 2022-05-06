import React, {useState, useEffect} from 'react';
import {Helmet} from "react-helmet";
import ServerSideDatatable from "../../../general-components/ServerSideDatatable/ServerSideDatatable";
import {BID_MULTIPLIER_CAMPAIGN_FETCH_DATA_URL, BID_MULTIPLIER_CAMPAIGN_SCHEDULE_FETCH_DATA_URL, BID_MULTIPLIER_CAMPAIGN_HISTORY_FETCH_DATA_URL} from "./apiCalls";
import useFilter from "../ProductTable/CustomHooks/useFilter";
import productTableFilter from '../../../app-resources/svgs/manager/productTableFilter.svg';
import SvgLoader from "../../../general-components/SvgLoader";
import {Filter} from "../BidMultiplier/Filter/Filter";
import BMHistoryFilter from "./History/BMHistoryFilter";
import IconBtn from "../../../general-components/IconBtn";
import {
    getFilterColumnNames,
    getTableColumns,
    getScheduleTableColumns,
    getScheduleFilterColumnNames,
    getHistoryTableColumns,
    getHistoryFilterColumnNames
} from '../BidMultiplier/Helper';
import {manageAllRowSelection, handleSelectedCheckboxesStateChange} from "../ProductTable/Helpers/ProductTableHelper";
import ModalDialog from './../../../general-components/ModalDialog';
import {PopUpContainer} from "./PopUp/PopUpContainer";
import {BMContainer} from "./PopUp/BMContainer";
import {ShowFailureMsg} from "../../../general-components/failureDailog/actions";
import {put} from '../../../../js/service/service';
import DeleteBidMultiplier from "./History/Delete/DeleteBidMultiplier";
import BMScheduleFilter from './Schedule/BMScheduleFilter';
import KeywordsTable from '../../../general-components/Keywords/KeywordsTable';

const BidMultiplierContainer = (props) => {

    const dataTableRef = React.useRef();
    const dataTableScheduleRef = React.useRef();
    const dataTableHistoryRef = React.useRef();

    const [bidMultiplierTabOne, setBidMultiplierOne] = useState(1);
    const [selectedRow, setSelectedRow] = useState(null);
    const [shouldModelOpen, setShouldModelOpen] = useState(false);
    const toggleTabButton = (status) => {

        setSelectedRow(null);
        setBidMultiplierOne(status)
        resetSelectedCheckBoxState();
        setState((prevState) => {
            switch (bidMultiplierTabOne) {
                case 1:
                    return ({
                        ...prevState,
                        showTagPopUp: false,
                        currentPage: 1,
                        sortDirection: "",
                        currentRowsPerPage: 10,
                    })
                case 2:
                    return ({
                        ...prevState,
                        showTagPopUp: false,
                        sortDirection: "",
                        historyCurrentPage: 1,
                        historyCurrentRowsPerPage: 10,
                    })
                default:
                    return ({
                        ...prevState,
                        showTagPopUp: false,
                        scheduleCurrentPage: 1,
                        scheduleCurrentRowsPerPage: 10,
                    })
            }
            
        })
    }

    function resetSelectedAsinsState() {
        setState((prevState) => {
            return {
                ...prevState,
                showTagPopUp: false,
                isEditing: false,
            }
        })
        resetSelectedCheckBoxState();
    }

    const handleModelClose = () => {
        setShouldModelOpen(false);
    }
    useEffect(() => {
        if(!shouldModelOpen){
            setTimeout(() => {
                setKeywordState(prevState =>({
                    ...prevState,
                    showKeywordPopup:false
                }))
            }, 100);
        }
        
    }, [shouldModelOpen])

    const onRowSelect = (action, row) => {

        if (action === "delete") {
            setShouldModelOpen(true);
            setState((prevState) => ({
                ...prevState,
                showTagPopUp: false,
                isEditing: false,
            }))
            setSelectedRow(row);
        } else {
            setSelectedRow({
                selectedArray: [row.strCampaignId],
                selectedObject: {
                    [row.strCampaignId]: {
                        fkProfileId: row.fkProfileId,
                        profileId: row.profileId,
                        campaignId: row.strCampaignId,
                    }
                },
                row
            });
            setState((prevState) => ({
                ...prevState,
                showTagPopUp: true,
                isEditing: true,
            }))
        }
    }

    const handleTagPopUp = () => {
        if (selectedArray.length > 0 && state.showTagPopUp) return;
        setState((prevState) => ({
            ...prevState,
            showTagPopUp: selectedArray.length > 0
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
                childBrand: null,
                category: null,
                strategy: null,
                status: null,
                itemsToShow: [3, 4, 5, 6],
            }
        }
    );
    const resetScheduleSelectedAsins = () => {

    }
    const resetHistorySelectedAsins = () => {

    }
    const {
        filter: scheduleFilter,
        showFilter: showScheduleFilter,
        applyFilterOnTable: applyScheduleFilterOnTable,
        handleApplyFilterButtonClick: handleApplyScheduleFilterButtonClick,
    } = useFilter(
        dataTableScheduleRef,
        resetScheduleSelectedAsins,
        {
            filter: {
                childBrand: null,
                category: null,
                strategy: null,
                status: null,
                itemsToShow: [5, 6, 7],
            }
        }
    );
    const {
        filter: historyFilter,
        showFilter: showHistoryFilter,
        applyFilterOnTable: applyHistoryFilterOnTable,
        handleApplyFilterButtonClick: handleApplyHistoryFilterButtonClick,
    } = useFilter(
        dataTableHistoryRef,
        resetHistorySelectedAsins,
        {
            filter: {
                childBrand: null,
            }
        }
    );
    const [keywordState, setKeywordState] = useState({
        selectedRow:null,
        showKeywordPopup:false,
        title:"Bid Multiplier Keywords"
    })
    const [state, setState] = useState({
        toggledClearRows: false,
        showTagPopUp: false,
        wasFilterOpen: false,
        isEditing: false,
        isDataTableRowClick: false,
        currentPage: 1,
        sortDirection: "",
        currentRowsPerPage: 10,
        scheduleCurrentPage: 1,
        scheduleCurrentRowsPerPage: 10,
        showScheduleLoader: false,
        historyCurrentPage: 1,
        historyCurrentRowsPerPage: 10,
        showHistoryLoader: false,
    })
    useEffect(() => {
        if ($(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr .selectContainer").length >= state.currentRowsPerPage) {
            $(".taggedDataTable .rdt_TableHeadRow .selectContainer").addClass("active")
        } else {
            $(".taggedDataTable .rdt_TableHeadRow .selectContainer").removeClass("active");
        }
    }, [isAllSelected, state.currentPage, state.sortDirection, state.currentRowsPerPage])
    const handleOnSortDataTable = (column, sortDirection, event) => {
        setState((prevState) => ({
            ...prevState,
            sortDirection,
        }))
        handleSelectedCheckboxesStateChange({selectedArray}, handleIfAllRowsSelected, "campaign-id");
    }

    const handleOnDataTableSearch = () => {
        //    resetSelectedAsinsState();
    }

    const reloadDatatable = () => {
        resetSelectedAsinsState();
    }

    const TableColumns = (itemsToShow) => {
        return getTableColumns(state.currentPage, state.currentRowsPerPage, itemsToShow, handleSelectAllCheckBoxClick, handleCheckBoxClick)
    }
    const ScheduleTableColumn = () => {
        return getScheduleTableColumns(state.scheduleCurrentPage, state.scheduleCurrentRowsPerPage, scheduleFilter ? scheduleFilter.itemsToShow : [6, 7, 8, 9], onRowSelect, onIsActiveStatusChange)
    }
    const onListClick = (selectedRow) => {
        setKeywordState(prevState =>({
            ...prevState,
            selectedRow,
            showKeywordPopup:true,
        }))
        setShouldModelOpen(true);
    }
    const HistoryTableColumn = () => {
        return getHistoryTableColumns(state.historyCurrentPage, state.historyCurrentRowsPerPage, onListClick)
    }
    const handleOnChangePage = (currentPage, totalRows) => {
        setState((prevState) => ({
            ...prevState, currentPage
        }));
        handleSelectedCheckboxesStateChange({selectedArray}, handleIfAllRowsSelected, "campaign-id");
    }
    const onBidMultiplierCloseButtonClicked = (close) => {
        if (selectedRow && selectedRow.selectedObject) {
            setSelectedRow(null);
            setState((prevState) => {
                return {
                    ...prevState,
                    showTagPopUp: false,
                    isEditing: false,
                }
            })
        } else if (close) {
            resetSelectedAsinsState();
        }
    }
    const showDataTableLoader = (shouldReset = true) => {
        dataTableRef.current.helperReloadDataTable(() => {
            if (!shouldReset) {
                setState((prevState) => {
                    return {
                        ...prevState,
                        isAllSelected: false,
                    }
                })
                handleSelectedCheckboxesStateChange({selectedArray}, handleIfAllRowsSelected, "campaign-id");
            } else {
                resetSelectedAsinsState();
            }
        });
    }

    const reloadHistoryDatatable = () => {
        dataTableScheduleRef.current.helperReloadDataTable();
        setState((prevState) => {
            return {
                ...prevState,
                showTagPopUp: false,
                isEditing: false,
            }
        })
    }

    const onIsActiveStatusChange = (event, row) => {
        setState((prevState) => ({
            ...prevState,
            showScheduleLoader: true
        }))
        row.isActive = !row.isActive;
        put(window.baseUrl + "/bidMultiplier/" + row.id,
            {
                isActive: row.isActive
            },//success
            () => {
                setState((prevState) => ({
                    ...prevState,
                    showScheduleLoader: false
                }))
                // props.dispatch(ShowSuccessMsg("Successfull", "Status updated successfully", true, "",()=>{}));
            },//error
            (error) => {

                setState((prevState) => ({
                    ...prevState,
                    showScheduleLoader: false
                }))
                props.dispatch(ShowFailureMsg(error, "", true, ""));
            },
        );
    }
    const {showTagPopUp, isEditing} = state;

    return (
        <>
            <Helmet>
                <title>Pulse Advertising | Bid Multiplier</title>
            </Helmet>
            <div className="">
                <div className="bg-white inline-flex mb-5 overflow-hidden rounded tacosTabs">
                    <div
                        className={`px-3 py-2 justify-center w-56 font-bold flex items-center text-xs ${bidMultiplierTabOne === 1 && "active"}`}
                        onClick={() => toggleTabButton(1)}
                    >
                        Bidding 
                    </div>
                    <div
                        className={`px-3 py-2 justify-center w-56 font-bold flex items-center text-xs ${bidMultiplierTabOne === 2 && "active"}`}
                        onClick={() => toggleTabButton(2)}
                    >
                        Scheduling
                    </div>
                    <div
                        className={`px-3 py-2 justify-center w-56 font-bold flex items-center text-xs ${bidMultiplierTabOne === 3 && "active"}`}
                        onClick={() => toggleTabButton(3)}
                    >
                        History
                    </div>
                </div>
            </div>
            <div className={"dataTableProduct"}>
                {
                    bidMultiplierTabOne === 1 && <ServerSideDatatable
                        ref={dataTableRef}
                        url={BID_MULTIPLIER_CAMPAIGN_FETCH_DATA_URL}
                        dataForAjax={
                            {
                                columnsToSearch: getFilterColumnNames(state.currentPage, state.currentRowsPerPage, filter ? filter.itemsToShow : [3, 4, 5, 6], handleSelectAllCheckBoxClick, handleCheckBoxClick),
                                childBrand: filter.childBrand && filter.childBrand.value,
                                category: filter.category && filter.category.value,
                                strategy: filter.strategy && filter.strategy.value,
                                status: filter.status && filter.status.value,
                            }
                        }
                        title="Bid Multiplier"
                        customClass="productTable"
                        showButtons
                        buttons={
                            <>
                                <IconBtn
                                    BtnLabel={"Filter"}
                                    variant={"contained"}
                                    icon={<SvgLoader
                                        src={productTableFilter}/>}
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
                                            reloadDatatable={reloadDatatable}
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
                                handleSelectedCheckboxesStateChange({selectedArray}, handleIfAllRowsSelected, "campaign-id")
                            }
                        }
                    />
                }
            </div>
            <div className={`datTableProduct TacosScheduleDatatable relative`}>

                {bidMultiplierTabOne === 2 &&
                    <>
                        <div className="graphLoader absolute h-full overflow-hidden w-full top-0 left-0 z-10"
                             style={state.showScheduleLoader ? {display: "block"} : {display: "none"}}>
                            <div
                                className="absolute flex font-bold font-mono h-full items-center justify-center overflow-hidden text-1rem text-sm w-full z-10">
                                Processing...
                            </div>
                        </div>

                        <ServerSideDatatable
                            ref={dataTableScheduleRef}
                            url={BID_MULTIPLIER_CAMPAIGN_SCHEDULE_FETCH_DATA_URL}
                            title="Bid Multiplier Schedule"
                            customClass="productTable"
                            dataForAjax={
                                {

                                    columnsToSearch: getScheduleFilterColumnNames(state.scheduleCurrentPage,
                                        state.scheduleCurrentRowsPerPage,
                                        scheduleFilter ? scheduleFilter.itemsToShow : [5, 6,7],
                                        onRowSelect,
                                        onIsActiveStatusChange),
                                    childBrand: scheduleFilter.childBrand && scheduleFilter.childBrand.value,
                                    category: scheduleFilter.category && scheduleFilter.category.value,
                                    strategy: scheduleFilter.strategy && scheduleFilter.strategy.value,
                                    status: scheduleFilter.status && scheduleFilter.status.value,
                                    startDate: scheduleFilter.startDate && scheduleFilter.startDate,
                                    endDate: scheduleFilter.endDate && scheduleFilter.endDate,
                                }
                            }
                            columns={ScheduleTableColumn()}
                            callBackOnChangePage={(scheduleCurrentPage) => setState((prevState) => ({
                                ...prevState,
                                scheduleCurrentPage
                            }))}
                            callBackOnChangeRowsPerPage={
                                (scheduleCurrentRowsPerPage) => {
                                    setState((prevState) => ({...prevState, scheduleCurrentRowsPerPage}));
                                }
                            }
                            showButtons
                            buttons={
                                <>
                                    <IconBtn
                                        BtnLabel={"Filter"}
                                        variant={"contained"}
                                        icon={<SvgLoader src={productTableFilter}/>}
                                        onClick={handleApplyScheduleFilterButtonClick}
                                    />
                                </>
                            }
                            otherSection={
                                <>
                                    {
                                        showScheduleFilter ?
                                            <BMScheduleFilter
                                                filter={scheduleFilter}
                                                applyFilterOnTable={applyScheduleFilterOnTable}
                                                reloadDatatable={reloadDatatable}
                                            />
                                            : null
                                    }
                                </>
                            }
                            handleRowClickEvent={() => {
                            }}
                        />
                    </>
                }
            </div>
            <div className={`datTableProduct TacosHistoryDatatable relative`}>

                {bidMultiplierTabOne === 3 &&
                    <>
                        <div className="graphLoader absolute h-full overflow-hidden w-full top-0 left-0 z-10"
                             style={state.showHistoryLoader ? {display: "block"} : {display: "none"}}>
                            <div
                                className="absolute flex font-bold font-mono h-full items-center justify-center overflow-hidden text-1rem text-sm w-full z-10">
                                Processing...
                            </div>
                        </div>

                        <ServerSideDatatable
                            ref={dataTableHistoryRef}
                            url={BID_MULTIPLIER_CAMPAIGN_HISTORY_FETCH_DATA_URL}
                            title="Bid Multiplier History"
                            customClass="productTable"
                            dataForAjax={
                                {
                                    columnsToSearch: [...getHistoryFilterColumnNames(state.historyCurrentPage,
                                        state.historyCurrentRowsPerPage),"strCampaignId"],
                                    childBrand: historyFilter.childBrand && historyFilter.childBrand.value,
                                    startDate: historyFilter.startDate && historyFilter.startDate,
                                    endDate: historyFilter.endDate && historyFilter.endDate,
                                }
                            }
                            columns={HistoryTableColumn()}
                            callBackOnChangePage={(historyCurrentPage) => setState((prevState) => ({
                                ...prevState,
                                historyCurrentPage
                            }))}
                            callBackOnChangeRowsPerPage={
                                (historyCurrentRowsPerPage) => {
                                    setState((prevState) => ({...prevState, historyCurrentRowsPerPage}));
                                }
                            }
                            showButtons
                            buttons={
                                <>
                                    <IconBtn
                                        BtnLabel={"Filter"}
                                        variant={"contained"}
                                        icon={<SvgLoader src={productTableFilter}/>}
                                        onClick={handleApplyHistoryFilterButtonClick}
                                    />
                                </>
                            }
                            otherSection={
                                <>
                                    {
                                        showHistoryFilter ?
                                            <BMHistoryFilter
                                                filter={historyFilter}
                                                applyFilterOnTable={applyHistoryFilterOnTable}
                                                reloadDatatable={reloadDatatable}
                                            />
                                            : null
                                    }
                                </>
                            }
                            handleRowClickEvent={() => {
                            }}
                        />
                    </>
                }
            </div>
            {
                showTagPopUp ?

                    <BMContainer
                        dots={selectedRow && selectedRow.selectedArray ? selectedRow.selectedArray.length : selectedArray.length}
                        selectedObject={selectedRow && selectedRow.selectedObject ? selectedRow.selectedObject : selectedObject}
                        row={selectedRow && selectedRow.row}
                        onBidMultiplierPopupClose={onBidMultiplierCloseButtonClicked}
                        showDataTableLoader={showDataTableLoader}
                        reloadHistoryDatatable={reloadHistoryDatatable}
                        showFilter={showFilter}
                        isEditing={isEditing}
                        ChildComponent={PopUpContainer}
                    />
                    :
                    null
            }
            <div className="modelClass">
                <ModalDialog
                    open={shouldModelOpen}
                    title={keywordState.showKeywordPopup ? keywordState.title :"Bid Multiplier"}
                    handleClose={handleModelClose}
                    component={
                    <>
                    {
                        keywordState.showKeywordPopup ?
                        <KeywordsTable
                            selectedRow = {keywordState.selectedRow}
                            url={BID_MULTIPLIER_CAMPAIGN_HISTORY_FETCH_DATA_URL}
                        />
                        :
                        <DeleteBidMultiplier
                            helperReloadDataTable={dataTableScheduleRef.current && dataTableScheduleRef.current.helperReloadDataTable}
                            id={selectedRow && selectedRow.id}
                            handleModalClose={handleModelClose}
                        />
                        
                    }
                    </>
                    }
                    maxWidth={keywordState.showKeywordPopup ? "md":"xs"}
                    fullWidth={true}
                    disable={true}
                    cancel={true}
                >
                </ModalDialog>
            </div>
        </>
    );

};

export default BidMultiplierContainer

const useMultiRowSelector = ({
                                 handleTagPopUp
                             }) => {

    const [state, setState] = useState({
        isAllSelected: false,
        selectedArray: [],
        selectedObject: {},
    });

    useEffect(() => {
        if (state.selectedArray.length <= 0) {
            manageAllRowSelection(false);
            handleTagPopUp();
        }
    }, [state.selectedArray, state.isAllSelected])


    const handleSelectAllCheckBoxClick = (e) => {
        e.stopPropagation();
        e.preventDefault();
        manageAllRowSelection(!state.isAllSelected);

        setState((prevState) => ({
            ...prevState,
            isAllSelected: !state.isAllSelected
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

        if ($(tr).hasClass("activeTr")) {
            selectedArray.push(campaignId);
            setState((prevState) => ({
                ...prevState,
                selectedArray,
                selectedObject: {
                    ...prevState.selectedObject,
                    [campaignId]: {
                        fkProfileId,
                        profileId,
                        campaignId,
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


    const handleIfAllRowsSelected = () => {
        // handleing if all checkbox selected.
        const headerCheckBox = $(".taggedDataTable .rdt_TableHeadRow .selectContainer");
        const trs = $(".taggedDataTable .rdt_TableBody .rdt_TableRow");
        //console.log("AllRowSelected", trs.length, $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length, trs.length == $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length)
        if (trs.length >= $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length) {
            setState((prevState) => {
                $(headerCheckBox).addClass("active");
                return {
                    ...prevState,
                    isAllSelected: true
                }
            })
        } else {
            setState((prevState) => {
                $(headerCheckBox).removeClass("active");
                return {
                    ...prevState,
                    isAllSelected: false
                }
            })
        }
        handleTagPopUp();
    }
    const resetSelectedCheckBoxState = () => {
        setState((prevState) => {
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
                            
