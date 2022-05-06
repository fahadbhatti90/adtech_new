import React, {Component} from "react";
import {Card} from "@material-ui/core";
import DataTable from "react-data-table-component";
import moment from "moment";
import {withStyles} from "@material-ui/core/styles";
import {primaryColor} from "../../../app-resources/theme-overrides/global";
import Radio from "@material-ui/core/Radio";
import {RadioButtonREvent} from "./RadioButtonREvent";



export const fetchedEvents = (props) => [
    {
        name: '',
        wrap: true,
        cell: (row) =>
            <RadioButtonREvent
                row={row}
                handleClick={props.eventSelected}
            />,
        maxWidth: '40px',
        minWidth: '20px',
    },
    {
        name: 'Event Id',
        selector: 'eventId',
        wrap: true,
        cell: (row) => {
            return row.eventId;
        }
    },
    {
        name: 'Event Name',
        selector: 'eventName',
        wrap: true,
        cell: (row) => {
            return row.eventName;
        }
    },
    {
        name: 'Start Date',
        selector: 'startDate',
        wrap: true,
        cell: (row) => {
            return moment(row.startDate).format('YYYY-MM-DD');
        }
    },
    {
        name: 'End Date',
        selector: 'endDate',
        wrap: true,
        cell: (row) => {
            return moment(row.endDate).format('YYYY-MM-DD');
        }
    },
    {
        name: 'Suggested Budget Increase (%)',
        selector: 'suggestedBudgetIncreasePercent',
        wrap: true,
        cell: (row) => {
            return row.suggestedBudgetIncreasePercent;
        }
    },
];

export const data = () => [
    {
        eventId: 1,
        eventName: 'Beetlejuice',
        startDate: '20211214',
        endDate: '20211215',
        suggestedBudgetIncreasePercent: '16',
    },
    {
        eventId: 2,
        eventName: 'Beetlejuice',
        startDate: '20211114',
        endDate: '20211115',
        suggestedBudgetIncreasePercent: '20',
    },
    {
        eventId: 3,
        eventName: 'Beetlejuice',
        startDate: '20211114',
        endDate: '20211115',
        suggestedBudgetIncreasePercent: '30',
    },
    {
        eventId: 4,
        eventName: 'Beetlejuice',
        startDate: '20211114',
        endDate: '20211115',
        suggestedBudgetIncreasePercent: '40',
    },
    {
        eventId: 5,
        eventName: 'Beetlejuice',
        startDate: '20211114',
        endDate: '20211115',
        suggestedBudgetIncreasePercent: '10',
    },
];

export class RecommendedEventPopUp extends Component {

    constructor(props) {
        super(props);
    }

    render() {
        return (
            <>
                <div className="p-5 rounded-lg dayPartingDatatable">
                    <Card className="overflow-hidden">
                        <div className={"w-full dataTableContainer"}>
                            <DataTable
                                noHeader={true}
                                wrap={false}
                                responsive={true}
                                columns={fetchedEvents(this.props)}
                                data={this.props.recommendedEvents}
 //                               data={data()}
                                persistTableHead
                            />
                        </div>
                    </Card>
                </div>
            </>
        )
    }
}