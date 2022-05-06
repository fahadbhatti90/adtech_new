import React, {Component} from "react";
import DataTable from "react-data-table-component";
import {Card} from "@material-ui/core";
import {getActivatedSchedules, newDpFunction} from "../helper/helper";
import '../dayparting.scss'

export const headerActivateSchedule = () => [
    {
        name: 'Schedule Name',
        selector:'activatedScheduleName',
        wrap:true,
        cell: (row) => {
            return getActivatedSchedules(row.activatedScheduleName);
        }
    },
    {
        name: 'Campaign',
        selector:'activatedCampaignName',
        wrap:true,
        cell: (row) => {
            return row.activatedCampaignName;
        }
    },
    {
        name: 'Monday (Start / End)',
        selector: 'mon',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return newDpFunction('monday', row.existTimingsOfCampaign);
        },
        minWidth: '120px',
    },
    {
        name: 'Tuesday (Start / End)',
        selector: 'tue',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return newDpFunction('tuesday', row.existTimingsOfCampaign);
        },
        minWidth: '120px',
    },
    {
        name: 'Wednesday (Start / End)',
        selector: 'wed',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return newDpFunction('wednesday', row.existTimingsOfCampaign);
        },
        minWidth: '120px',
    },
    {
        name: 'Thursday (Start / End)',
        selector: 'thu',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return newDpFunction('thursday', row.existTimingsOfCampaign);
        },
        minWidth: '120px',
    },
    {
        name: 'Friday (Start / End)',
        selector: 'fri',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return newDpFunction('friday', row.existTimingsOfCampaign);
        },
        minWidth: '120px',
    },
    {
        name: 'Saturday (Start / End)',
        selector: 'sat',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return newDpFunction('saturday', row.existTimingsOfCampaign);
        },
        minWidth: '120px',
    },
    {
        name: 'Sunday (Start / End)',
        selector: 'sun',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return newDpFunction('sunday', row.existTimingsOfCampaign);
        },
        minWidth: '120px',
    },
]
export default class ActivatedSchedules extends Component{
    constructor(props) {
        super(props);
        this.state = {
            data: []
        }
    }
    componentDidMount() {
        this.setState({
            data: this.props.activatedSchedules
        })
    }

    render() {
        //const {classes} = styles();
        return(
            <>
                <div className="p-5 rounded-lg dayPartingDatatable">
                    <Card className="overflow-hidden">
                        {/*<div className="font-semibold mb-3 ml-3 mt-2 w-4/12">Activated Schedules</div>*/}
                        <div className={"w-full dataTableContainer"}>
                            <DataTable
                                noHeader={true}
                                wrap={false}
                                responsive={true}
                                columns={headerActivateSchedule()}
                                //columns={columns}
                                //data={data}
                                data={this.state.data}
                                //progressPending={props.dataLoading}
                                persistTableHead
                            />
                        </div>
                    </Card>
                </div>
            </>
        )
    }
}