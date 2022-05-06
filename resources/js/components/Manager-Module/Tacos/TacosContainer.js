import React, {useEffect, useState} from 'react';
import {withStyles} from '@material-ui/core/styles';
import "./Tacos.scss"
import SvgLoader from "./../../../general-components/SvgLoader";
import productTableFilter from './../../../app-resources/svgs/manager/productTableFilter.svg';
import ModalDialog from './../../../general-components/ModalDialog';
import IconBtn from "./../../../general-components/IconBtn";
import Filter from './TacosFilters/Filter';
import HistoryFilter from './TacosFilters/HistoryFilter';
import {default as ScheduleFilter} from './TacosFilters/HistoryFilter';
import {Helmet} from 'react-helmet';
import {
    manageAllRowSelection,
    handleSelectedCheckboxesStateChange,
} from './../ProductTable/Helpers/ProductTableHelper';

import {
    getTableColumns,
    getFilterColumnNames,
    getHistoryColumnOptions,
    getHistoryFilterColumnNames,
    getScheduleFilterColumnNames,
    getHistoryTableColumns,
    getScheduleTableColumns,
} from './TacosHelper';
import {
    TACOS_CAMPAIGN_FETCH_DATA_URL,
    TACOS_CAMPAIGN_SCHEDULE_FETCH_DATA_URL,
    TACOS_CAMPAIGN_HISTORY_FETCH_DATA_URL,
} from './apiCalls';
import ServerSideDatatable from '../../../general-components/ServerSideDatatable/ServerSideDatatable';
import useFilter from './../ProductTable/CustomHooks/useFilter';
import TMContainer from '../../../general-components/TagManager/TagManagerContainer/TMContainer';
import TacosPopupContainer from './TacosPopup/TacosPopupContainer';
import {ShowSuccessMsg} from "./../../../general-components/successDailog/actions";
import {ShowFailureMsg} from "./../../../general-components/failureDailog/actions";
import DeleteTacos from './TacosHistory/Delete/DeleteTacos';
import {put} from './../../../service/service';
import {connect} from "react-redux"
import KeywordsTable from '../../../general-components/Keywords/KeywordsTable';

const classStyles = theme => ({
    mainClass: {},
    productTable: {},
    ptTooltip: {
        color: "#000",
        backgroundColor: "rgb(255 255 255 / 90%)",
        boxShadow: "1px 1px 10px #0000003b",
        overflow: "hidden",
    },
    ptArrow: {
        color: "#fff"
    },
});

const TacosContainer = (props) => {
    const dataTableRef = React.useRef();
    const dataTableHistoryRef = React.useRef();
    const dataTableScheduleRef = React.useRef();

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
    const resetSelectedAsinsState = () => {
        setState((prevState) => {
            return {
                ...prevState,
                showTagPopUp: false,
                isEditing: false,
            }
        })
        resetSelectedCheckBoxState();
    }
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
        helperLoadFilterAgain: helperLoadScheduleFilterAgain,
        handleApplyFilterButtonClick: handleApplyScheduleFilterButtonClick,
    } = useFilter(
        dataTableScheduleRef,
        resetScheduleSelectedAsins,
        {
            filter: {
                childBrand: null,
                category: null,
                strategy: null,
                tag: null,
                itemsToShow: [6, 7, 8, 9]
            }
        }
    );
    const {
        filter: historyFilter,
        showFilter: showHistoryFilter,
        applyFilterOnTable: applyHistoryFilterOnTable,
        helperLoadFilterAgain: helperLoadHistoryFilterAgain,
        handleApplyFilterButtonClick: handleApplyHistoryFilterButtonClick,
    } = useFilter(
        dataTableHistoryRef,
        resetHistorySelectedAsins,
        {
            filter: {
                childBrand: null,
                category: null,
                strategy: null,
                tag: null,
                itemsToShow: [9, 10, 11, 12, 13, 14]
            }
        }
    );
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
    });
    
    const [keywordState, setKeywordState] = useState({
        selectedRow:null,
        showKeywordPopup:false,
        title:"Tacos Keywords"
    })
    const [tacosTabOne, setTacosTabOne] = useState(1);
    const [shouldModelOpen, setShouldModelOpen] = useState(false);
    const [selectedRow, setSelectedRow] = useState(null);
    const toggleTabButton = (status) => {
        setSelectedRow(null);
        resetSelectedCheckBoxState();
        setTacosTabOne(status);
        setState((prevState) => {
            switch (tacosTabOne) {
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
        return getHistoryTableColumns(state.historyCurrentPage, state.historyCurrentRowsPerPage, historyFilter ? historyFilter.itemsToShow : [9, 10, 11, 12, 13, 14], onListClick)
    }
    const onIsActiveStatusChange = (event, row) => {
        setState((prevState) => ({
            ...prevState,
            showHistoryLoader: true
        }))
        row.isActive = !row.isActive;
        put(window.baseUrl + "/tacos/" + row.id,
            {
                isActive: row.isActive
            },//success
            () => {
                setState((prevState) => ({
                    ...prevState,
                    showHistoryLoader: false
                }))
                // props.dispatch(ShowSuccessMsg("Successfull", "Status updated successfully", true, "",()=>{}));
            },//error
            (error) => {

                setState((prevState) => ({
                    ...prevState,
                    showHistoryLoader: false
                }))
                props.dispatch(ShowFailureMsg(error, "", true, ""));
            },
        );
    }
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
                        campaignType: row.campaignType,
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
    const handleOnChangePage = (currentPage, totalRows) => {
        setState((prevState) => ({
            ...prevState, currentPage
        }));
        handleSelectedCheckboxesStateChange({selectedArray}, handleIfAllRowsSelected, "campaign-id");
    }
    const onTacosPopupCloseButtonClicked = (close) => {
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
    const relaodDatatable = () => {
        resetSelectedAsinsState();
    }
    const handleOnDataTableSearch = () => {
        resetSelectedAsinsState();
    }
    const reloadScheduleDatatable = () => {
        dataTableScheduleRef.current.helperReloadDataTable();
        setState((prevState) => {
            return {
                ...prevState,
                showTagPopUp: false,
                isEditing: false,
            }
        })
    }
    const {showTagPopUp, isEditing} = state;
    return (
        <>
            <Helmet>
                <title>Pulse Advertising | TACOS Bidding Rule</title>
            </Helmet>
            <div className="">
                <div className="bg-white inline-flex mb-5 overflow-hidden rounded tacosTabs">
                    <div
                        className={`px-3 py-2 justify-center w-56 font-bold flex items-center text-xs ${tacosTabOne === 1 && "active"}`}
                        onClick={() => {
                            toggleTabButton(1);
                        }}
                    >
                        Bidding
                    </div>
                    <div
                        className={`px-3 py-2 justify-center w-56 font-bold flex items-center text-xs ${tacosTabOne === 2 && "active"}`}
                        onClick={() => {
                            toggleTabButton(2);
                        }}
                    >
                        Scheduling
                    </div>
                    <div
                        className={`px-3 py-2 justify-center w-56 font-bold flex items-center text-xs ${tacosTabOne === 3 && "active"}`}
                        onClick={() => {
                            toggleTabButton(3);
                        }}
                    >
                        History
                    </div>
                </div>
            </div>

            <div className={`datTableProduct `}>

                {
                    tacosTabOne === 1 && <ServerSideDatatable
                        ref={dataTableRef}
                        url={TACOS_CAMPAIGN_FETCH_DATA_URL}
                        dataForAjax={
                            {
                                columnsToSearch: getFilterColumnNames(state.currentPage, state.currentRowsPerPage, filter ? filter.itemsToShow : [3, 4, 5, 6], handleSelectAllCheckBoxClick, handleCheckBoxClick),
                                childBrand: filter.childBrand && filter.childBrand.value,
                                category: filter.category && filter.category.value,
                                strategy: filter.strategy && filter.strategy.value,
                                status: filter.status && filter.status.value,
                            }
                        }
                        title="TACOS"
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
                                            relaodDatatable={relaodDatatable}
                                        ></Filter>
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
                {
                    tacosTabOne === 2 &&
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
                            url={TACOS_CAMPAIGN_SCHEDULE_FETCH_DATA_URL}
                            title="TACOS Schedule"
                            customClass="productTable"
                            columns={ScheduleTableColumn()}
                            dataForAjax={
                                {
                                    columnsToSearch: getScheduleFilterColumnNames(state.historyCurrentPage, state.historyCurrentRowsPerPage, scheduleFilter ? scheduleFilter.itemsToShow : [6, 7, 8, 9], onRowSelect, onIsActiveStatusChange),
                                    childBrand: scheduleFilter.childBrand && scheduleFilter.childBrand.value,
                                    category: scheduleFilter.category && scheduleFilter.category.value,
                                    strategy: scheduleFilter.strategy && scheduleFilter.strategy.value,
                                    adType: scheduleFilter.adType && scheduleFilter.adType.value,
                                    tag: scheduleFilter.tag && scheduleFilter.tag.value,
                                    startDate: scheduleFilter.startDate && scheduleFilter.startDate,
                                }
                            }
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
                                            <ScheduleFilter
                                                filter={historyFilter}
                                                applyFilterOnTable={applyScheduleFilterOnTable}
                                                relaodDatatable={relaodDatatable}
                                            ></ScheduleFilter>
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
                {
                    tacosTabOne === 3 &&
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
                            url={TACOS_CAMPAIGN_HISTORY_FETCH_DATA_URL}
                            title="TACOS History"
                            customClass="productTable"
                            columns={HistoryTableColumn()}
                            dataForAjax={
                                {
                                    columnsToSearch: getHistoryFilterColumnNames(state.historyCurrentPage, state.historyCurrentRowsPerPage, historyFilter ? historyFilter.itemsToShow : [9, 10, 11, 12, 13, 14], onListClick),
                                    childBrand: historyFilter.childBrand && historyFilter.childBrand.value,
                                    category: historyFilter.category && historyFilter.category.value,
                                    strategy: historyFilter.strategy && historyFilter.strategy.value,
                                    adType: historyFilter.adType && historyFilter.adType.value,
                                    tag: historyFilter.tag && historyFilter.tag.value,
                                    startDate: historyFilter.startDrpDate && historyFilter.startDrpDate,
                                    endDate: historyFilter.endDrpDate && historyFilter.endDrpDate,
                                }
                            }
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
                                            <HistoryFilter
                                                filter={historyFilter}
                                                hasDateRangePicker
                                                columnOptions={getHistoryColumnOptions([9, 10, 11, 12, 13, 14])}
                                                applyFilterOnTable={applyHistoryFilterOnTable}
                                                relaodDatatable={relaodDatatable}
                                            ></HistoryFilter>
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
                    <TMContainer
                        dots={selectedRow && selectedRow.selectedArray ? selectedRow.selectedArray.length : selectedArray.length}
                        selectedObject={selectedRow && selectedRow.selectedObject ? selectedRow.selectedObject : selectedObject}
                        row={selectedRow && selectedRow.row}
                        onTacosPopupClose={onTacosPopupCloseButtonClicked}
                        showDataTableLoader={showDataTableLoader}
                        reloadHistoryDatatable={reloadScheduleDatatable}
                        showFilter={showFilter}
                        isEditing={isEditing}
                        ChildComponent={TacosPopupContainer}
                    />
                    :
                    null
            }
            <div className="modelClass">
                <ModalDialog
                    open={shouldModelOpen}
                    title={keywordState.showKeywordPopup ? keywordState.title :"Tacos"}
                    handleClose={handleModelClose}
                    component={
                        <>
                        {
                            keywordState.showKeywordPopup ?
                            <KeywordsTable
                                selectedRow = {keywordState.selectedRow}
                                url={TACOS_CAMPAIGN_HISTORY_FETCH_DATA_URL}
                            />
                            :
                            <DeleteTacos
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

    )
};

export default connect(null)(withStyles(classStyles)(TacosContainer))


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
        let campaignType = $(rowTitle).attr("campaign-type");

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
                        campaignType
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
        console.log("AllRowSelected", trs.length, $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length, trs.length == $(".taggedDataTable .rdt_TableBody .rdt_TableRow.activeTr").length)
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