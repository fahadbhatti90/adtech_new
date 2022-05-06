import React, {Component} from 'react';
import DataTable from 'react-data-table-component';
import {makeStyles, withStyles} from '@material-ui/core/styles';
import LinearProgress from '@material-ui/core/LinearProgress';
import Card from '@material-ui/core/Card';
import SearchIcon from '@material-ui/icons/Search';
import Button from '@material-ui/core/Button';
import Tooltip from '@material-ui/core/Tooltip';
import '../dayparting.scss';
import {getAllSchedules} from '../apiCalls';
import moment from 'moment';
import EditFormDayParting from "../Edit/EditFormDayParting";
import DayPartingModal from '../DayPartingModal'
import PortfolioCampaignRemove from "../DeleteScheduleOptions"
import StopScheduleComp from "./StopSchedule";
import StartScheduleComp from "./StartSchedule";
import {styles} from "../styles";
import {connect} from "react-redux";
import ActionBtns from "./ActionBtns";

const useStyles = makeStyles(theme => ({
    root: {
        width: '100%',
        '& > * + *': {
            marginTop: theme.spacing(2),
        },
    },
}));

const LinearIndeterminate = () => {
    const classes = useStyles();
    return (
        <div className={classes.root}>
            <LinearProgress/>
        </div>
    );
};

class DayPartingDataTables extends Component {
    constructor(props) {
        super(props)
        this.state = {
            id: "",
            modalTitle: "",
            modalBody: "",
            maxWidth: "sm",
            data: [],
            orignalData: [],
            loading: false,
            openModal: false,
            openSMModal: false,
            totalRows: 0,
            perPage: 10,
            isDataTableReload: false,
            removeCampaigns: "1",
            columns: [
                {
                    name: 'Schedule Name',
                    selector: 'scheduleName',
                    sortable: true,
                    cell: (row) => {
                        return this.getScheduleName(row.scheduleName);
                    },
                    minWidth: '145px'
                },
                {
                    name: 'Campaign/Portfolio',
                    selector: 'portfolioCampaignType',
                    sortable: true,
                    cell: (row) => {
                        return row.portfolioCampaignType
                    },
                    minWidth: '140px',
                    maxWidth: '200px'
                },
                {
                    name: 'Include',
                    selector: 'Include',
                    sortable: false,
                    wrap: true,
                    cell: (row) => {
                        return this.getTooltipPortfolioCampaign(row);
                    },
                    minWidth: '140px',
                    maxWidth: '200px'
                },
                {
                    name: 'Monday (Start / End)',
                    selector: 'mon',
                    sortable: false,
                    wrap: false,
                    cell: (row) => {
                        return this.newDpFunction('monday', row.time_campaigns);
                    },
                    minWidth: '120px',
                },
                {
                    name: 'Tuesday (Start / End)',
                    selector: 'tue',
                    sortable: false,
                    wrap: false,
                    cell: (row) => {
                        return this.newDpFunction('tuesday', row.time_campaigns);
                    },
                    minWidth: '120px',
                },
                {
                    name: 'Wednesday (Start / End)',
                    selector: 'wed',
                    sortable: false,
                    wrap: false,
                    cell: (row) => {
                        return this.newDpFunction('wednesday', row.time_campaigns);
                    },
                    minWidth: '120px',
                },
                {
                    name: 'Thursday (Start / End)',
                    selector: 'thu',
                    sortable: false,
                    wrap: false,
                    cell: (row) => {
                        return this.newDpFunction('thursday', row.time_campaigns);
                    },
                    minWidth: '120px',
                },
                {
                    name: 'Friday (Start / End)',
                    selector: 'fri',
                    sortable: false,
                    wrap: false,
                    cell: (row) => {
                        return this.newDpFunction('friday', row.time_campaigns);
                    },
                    minWidth: '120px',
                },
                {
                    name: 'Saturday (Start / End)',
                    selector: 'sat',
                    sortable: false,
                    wrap: false,
                    cell: (row) => {
                        return this.newDpFunction('saturday', row.time_campaigns);
                    },
                    minWidth: '120px',
                },
                {
                    name: 'Sunday (Start / End)',
                    selector: 'sun',
                    sortable: false,
                    wrap: false,
                    cell: (row) => {
                        return this.newDpFunction('sunday', row.time_campaigns);
                    },
                    minWidth: '120px',
                },
                {
                    name: 'Action',
                    selector: 'id',
                    sortable: false,
                    cell: row => <ActionBtns
                        row={row}
                        deleteSchedule={this.handleRowClickEventDelete}
                        editSchedule={this.handleRowClickEventEdit}
                        stopSchedule={this.handleRowClickEventStop}
                        startSchedule={this.handleRowClickEventStart}
                    />,
                    ignoreRowClick: true,
                    allowOverflow: true,
                    button: true,
                },
            ]
        };
    }

    dummyTimeCampaign = (timeCampaigns, day) => {
        if (timeCampaigns && timeCampaigns.length > 0) {
            let listToShow = timeCampaigns.filter(value => value.day === day).map((value, index) => {
                let startTime = value.startTime;
                let endTime = value.endTime;
                if (startTime.length > 8 && endTime.length > 8) {
                    let startTimeArray = startTime.split(",");
                    let endTimeArray = endTime.split(",");
                    let setTimeArray = startTimeArray.map((value1, idx1) => {
                        let finalStartEndTime = value1 + ' / ' + endTimeArray[idx1];
                        return <li className='list-disc' key={idx1.toString()}>
                            {finalStartEndTime}
                        </li>
                    })
                    return <>
                        {setTimeArray}
                    </>

                } else {
                    let timeOfCampaigns = value.startTime + " / " + value.endTime;
                    return <li className='list-disc' key={index.toString()}>
                        {timeOfCampaigns}
                    </li>
                }
            });

            if (listToShow.length > 0) {
                if (listToShow[0].key == 0) {
                    return listToShow[0].props.children;
                }
                let ulList = <ul className='m-1 mr-5 pl-5 pr-3'>{listToShow}</ul>
                let allData = <div className={ulList.length > 0 ? "h-32 overflow-auto" : ""}>
                    {ulList}
                </div>

                return <>
                    <Tooltip title={allData} placement="top" arrow
                             interactive>
                        <Button>Show Timings</Button>
                    </Tooltip>
                </>
            }
        }
        return;
    }
    newDpFunction = (day, timeCampaigns) => {

        switch (day) {
            case 'monday': {
                return this.dummyTimeCampaign(timeCampaigns, day);
                break;
            }
            case 'tuesday': {
                return this.dummyTimeCampaign(timeCampaigns, day);
                break;
            }
            case 'wednesday': {
                return this.dummyTimeCampaign(timeCampaigns, day);
                break;
            }
            case 'thursday': {
                return this.dummyTimeCampaign(timeCampaigns, day);
                break;
            }
            case 'friday': {
                return this.dummyTimeCampaign(timeCampaigns, day);
                break;
            }
            case 'saturday': {
                return this.dummyTimeCampaign(timeCampaigns, day);
                break;
            }
            case 'sunday': {
                return this.dummyTimeCampaign(timeCampaigns, day);
                break;
            }
            default: {
                return 'no days selected';
                break;
            }

        }
    }

    componentDidUpdate(prevProps, prevState, snapshot) {

        if (snapshot !== null) {
            if (this.props.isDataTableReload || this.state.isDataTableLoaded) {
                this.getAllSchedulesFromDb();
            }
            return null;
        }
    }

    getScheduleName = (scheduleName) => {
        const name = scheduleName;
        if (name && name.length > 0) {
            if (name.length > 10) {
                const shortName = name.slice(0, 10) + "...";
                return <Tooltip title={name} placement="top" arrow>
                    <span>{shortName}</span>
                </Tooltip>
            } else {
                return name;
            }
        } else {
            return "NA";
        }
    }

    getTooltipPortfolioCampaign = (row) => {
        switch (row.portfolioCampaignType) {
            case 'Campaign': {
                let listValue = row.expired_campaigns;
                let getCampaigns = row.campaigns;
                if (getCampaigns.length > 0) {
                    var listItems = getCampaigns.map(
                        (obj, idx) => {
                            if (obj.enablingPausingStatus == null) {
                                return <li className='list-disc' key={idx}>{obj.name}</li>
                            }

                        }
                    );
                } else {
                    var listItems = listValue.map(
                        (obj, idx) => {
                            //if (obj.enablingPausingStatus == null) {
                            return <li className='list-disc' key={idx}>{obj.name}</li>
                            //}

                        }
                    );
                }

                let heading = <div className="font-semibold">Campaign</div>
                let ulList = <ul className='m-1 mr-5 pl-5 pr-3'>{listItems}</ul>
                let allData = <div className={ulList.length > 0 ? "h-32 overflow-auto" : ""}>
                    {heading}
                    {ulList}
                </div>
                return <>
                    <Tooltip title={allData} placement="top" arrow
                             interactive>
                        <Button>List</Button>
                    </Tooltip>
                </>
                break;
            }

            default: {
                let listPortfolios = row.expired_portfolios;
                let portfolios = row.portfolios;
                let heading = <div className="font-semibold mb-1">Portfolio</div>
                if (portfolios.length > 0) {
                    var returnListPf = listPortfolios.map((obj1, idx1) => {
                        if (obj1.enablingPausingStatus == null) {
                            let portfolioNames = <div key={idx1} className="ml-3 font-semibold">{obj1.name}</div>
                            const listItems = row.expired_campaigns.map(
                                (obj, idx) => {
                                    if (obj.portfolioId === obj1.portfolioId) {
                                        return <li className='list-disc' key={idx}>{obj.name}</li>
                                    }
                                }
                            );
                            return <>
                                {portfolioNames}
                                <ul key={obj1.id} className="mt-1"> {listItems} </ul>
                            </>
                        }
                    })
                } else {
                    var returnListPf = listPortfolios.map((obj1, idx1) => {
                        let portfolioNames = <div key={idx1} className="ml-3 font-semibold">{obj1.name}</div>
                        const listItems = row.expired_campaigns.map(
                            (obj, idx) => {
                                if (obj.portfolioId === obj1.portfolioId) {
                                    return <li className='list-disc' key={idx}>{obj.name}</li>
                                }
                            }
                        );
                        return <>
                            {portfolioNames}
                            <ul key={obj1.id} className="mt-1"> {listItems} </ul>
                        </>
                    })
                }

                let allData = <div className={returnListPf.length > 0 ? "h-32 overflow-auto" : ""}>
                    {heading}
                    {returnListPf.length > 0 ? returnListPf : ""}
                </div>
                return <Tooltip title={allData} placement="auto"
                                arrow interactive>
                    <Button>List</Button>
                </Tooltip>
                break;
            }
        }
    }


    newDayPartingStartEndTime = (rowName, scheduleStartEndTime) => {
        switch (rowName) {
            case 'Monday': {
                return scheduleStartEndTime;
            }
            case 'Tuesday': {
                return scheduleStartEndTime;
            }
            case 'Wednesday': {
                return scheduleStartEndTime;
            }
            case 'Thursday': {
                return scheduleStartEndTime;
            }
            case 'Friday': {
                return scheduleStartEndTime;
            }
            case 'Saturday': {
                return scheduleStartEndTime;
            }
            case 'Sunday': {
                return scheduleStartEndTime;
            }
            default: {
                return 'no data available'
            }
        }
    }

    getAllSchedulesFromDb = () => {
        getAllSchedules((data) => {
            if (this.props.isDataTableReload || this.state.isDataTableLoaded) {
                this.props.updateDataTable();
            }
            this.setState({
                data: data,
                orignalData: data,
                totalRows: data.length,
                loading: false,
            }).catch(e => {
                this.setState({
                    loading: true,
                });

            });
        });
    }

    componentDidMount() {

        const {perPage} = this.state;
        this.setState({loading: true});
        this.getAllSchedulesFromDb();
    }

    onDataTableSearch = (e) => {
        if (e.target.value.length > 0) {
            var result = this.state.orignalData.filter(row => {
                let time = moment(row.startTime, 'HH:mm').format('hh:mm A') + ' / ' + moment(row.endTime, 'HH:mm').format('hh:mm A');
                return row.scheduleName.toString().toLowerCase().includes(e.target.value.toLowerCase())
                    || row.portfolioCampaignType.toLowerCase().includes(e.target.value.toLowerCase())
                    || time.includes(e.target.value);
            });
            this.setState({
                data: result,
                totalRows: result.length
            })
        } else {
            this.setState({
                data: this.state.orignalData,
                totalRows: this.state.orignalData.length
            })
        }
    }

    /**
     * This function is used to open modal
     * @param id
     */
    handleRowClickEventDelete = (id) => {
        this.setState({
            id: id,
            modalTitle: 'Delete Day Parting Schedule',
            maxWidth: 'sm',
            modalBody: <PortfolioCampaignRemove
                id={id}
                getAllSchedulesFromDb={this.getAllSchedulesFromDb}
                handleModalClose={this.handleModalClose}
            />,
            openModal: true
        })
    }

    handleRowClickEventStart = (id) => {
        this.setState({
            id: id,
            modalTitle: 'Start Day Parting Schedule',
            maxWidth: 'xs',
            modalBody: <StartScheduleComp
                id={id}
                getAllSchedulesFromDb={this.getAllSchedulesFromDb}
                handleModalClose={this.handleModalClose}
            />,
            openModal: true
        })
    }

    handleRowClickEventStop = (id) => {
        this.setState({
            id: id,
            modalTitle: 'Stop Day Parting Schedule',
            maxWidth: 'xs',
            modalBody: <StopScheduleComp
                id={id}
                getAllSchedulesFromDb={this.getAllSchedulesFromDb}
                handleModalClose={this.handleModalClose}
            />,
            openModal: true
        })
    }

    /**
     * This fucntion is used to open Edit Modal
     * @param id
     */
    handleRowClickEventEdit = (id) => {
        this.setState({
            id: id,
            modalTitle: 'Edit Day Parting',
            maxWidth: 'lg',
            modalBody: <EditFormDayParting maxWidth={'xs'} id={id} getAllSchedulesFromDb={this.getAllSchedulesFromDb}
                                           handleModalClose={this.handleModalClose}

            />,
            openModal: true
        })
    }

    handleModalClose = () => {
        this.setState({
            openModal: false,
            modalBody: '',
            maxWidth: 'md',
        })
    }

    render() {
        const {loading, data, totalRows} = this.state;
        return (
            <>
                <div style={{display: 'table', tableLayout: 'fixed', width: '100%'}}
                     className="dayPartingDatatable scrollableDatatable">
                    <Card className="overflow-hidden">
                        <div className="flex p-5">
                            <div className="font-semibold w-3/12">Active Schedules</div>
                            <div className="searchDataTable w-9/12">
                                <div
                                    className="border border-gray-300 border-solid flex inputGroup mr-4 px-3 py-1 rounded-full w-7/12 ml-auto">
                                    <input type="text"
                                           className="border-0 flex-1 focus:outline-none font-semibold outline-none px-2 text-xs"
                                           placeholder="Search"
                                           onChange={this.onDataTableSearch}
                                    />
                                    <SearchIcon className="text-gray-300"/>
                                </div>

                            </div>
                        </div>
                        <div className=" w-full ">
                            <div className="h-full pl-20 w-full"></div>
                            <DataTable
                                className="scrollableDatatable"
                                Clicked
                                noHeader={true}
                                wrap={false}
                                responsive={true}
                                columns={this.state.columns}
                                data={data}
                                pagination
                                paginationTotalRows={totalRows}
                                progressPending={loading}
                                progressComponent={<LinearIndeterminate/>}
                                persistTableHead
                            />
                        </div>
                    </Card>
                    <DayPartingModal
                        openModal={this.state.openModal}
                        modalTitle={this.state.modalTitle}
                        id={this.state.id}
                        handleClose={this.handleModalClose}
                        modalBody={this.state.modalBody}
                        maxWidth={this.state.maxWidth}
                        cancelEvent={this.handleModalClose}
                        callback={this.state.callback}
                        fullWidth={true}
                    />
                </div>
            </>
        )
    }
}

export default withStyles(styles)(connect(null)(DayPartingDataTables));
