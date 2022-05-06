import React, {Component} from "react";
import {makeStyles, withStyles} from "@material-ui/core/styles";
import LinearProgress from "@material-ui/core/LinearProgress";
import Card from "@material-ui/core/Card";
import SearchIcon from "@material-ui/icons/Search";
import DataTable from "react-data-table-component";
import {styles} from "../../Day-Parting/styles";
import {connect} from "react-redux";
import {getAllBudgetRules} from '../apiCalls';
import ActionButtons from "./ActionBtns";
import Tooltip from "@material-ui/core/Tooltip";
import Button from "@material-ui/core/Button";
import DeleteBudgetRule from "../Delete/DeleteBudgetRule";
import ModalDialog from "../../../../general-components/ModalDialog";
import EditBudgetRuleForm from "../Edit/EditBudgetRuleForm";

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

class BudgetRuleDataTables extends Component {
    constructor(props) {
        super(props)
        this.state = {
            id: "",
            modalTitle: "",
            modalBody: "",
            maxWidth: "sm",
            data: [],
            originalData: [],
            loading: false,
            openModal: false,
            totalRows: 0,
            perPage: 10,
            isDataTableReload: false,
        }
    }

    budgetRuleColumns = () => {
        return [
            {
                name: 'Rule Name',
                selector: 'ruleName',
                wrap: true,
                cell: row => {
                    const name = row.ruleName;
                    if (name && name.length > 0) {
                        if (name.length > 10) {
                            const shortName = name.slice(0, 10) + "...";
                            return <Tooltip title={name} placement="top" arrow>
                                <span>{shortName}</span>
                            </Tooltip>
                        } else {
                            return name;
                        }
                    }else{
                        return "NA";
                    }
                }
            },
            {
                name: 'Campaigns',
                selector: 'campaigns',
                wrap: true,
                cell: (row) => {
                    return this.getTooltipCampaigns(row);
                }

            },
            {
                name: 'Rule Type',
                selector: 'ruleType',
                wrap: true,
            },
            {
                name: 'Start / End Date',
                selector: 'startEndDate',
                wrap: true,
                cell: (row) => {
                    return this.startAndEndDate(row);
                }
            },
            {
                name: 'Budget Value (%)',
                selector: 'raiseBudget',
                wrap: true,
            },
            {
                name: 'Recurrence',
                selector: 'recurrence',
                wrap: true,
            },
            {
                name: 'Action',
                sortable: false,
                cell: row => <ActionButtons
                    row={row}
                    deleteBudgetRule={this.handleRowClickEventDelete}
                    editBudgetRule={this.handleRowClickEventEdit}
                />,
                ignoreRowClick: true,
                allowOverflow: true,
                button: true,
            },
        ]
    }

    startAndEndDate = (row) => {
        let startDate = row.startDate
        let endDate = (row.endDate != null) ? ' / ' + row.endDate : ''
        return startDate + endDate
    }
    getTooltipCampaigns = (row) => {

        let getCampaigns = row.budget_rule_campaigns;
        if (getCampaigns.length > 0) {
            var listItems = getCampaigns.map(
                (obj, idx) => {
                    return <li className='list-disc' key={idx}>{obj.name}</li>
                }
            );

            let heading = <div className="font-semibold">Campaign</div>
            let ulList = <ul className='m-1 mr-5 pl-5 pr-3'>{listItems}</ul>
            let allData = <div className={ulList.length > 0 ? "h-32 overflow-auto" : ""}>
                {heading}
                {ulList}
            </div>
            return <>
                <Tooltip title={allData} placement="top" arrow
                         interactive>
                    <Button>View Campaigns</Button>
                </Tooltip>
            </>
        }
    }
    handleRowClickEventDelete = (rowId) => {
        this.setState({
            modalTitle: 'Delete Budget Multiplier',
            openModal: true,
            openAddModal: true,
            modalBody: <DeleteBudgetRule
                rowId={rowId}
                updateDataTableAfterSubmit={this.getAllBudgetRulesFromDb}
                handleModalClose={this.handleModalClose}
            />,
            maxWidth: 'xs'
        })
    }

    handleRowClickEventEdit = (row) => {
        this.setState({
            modalTitle: 'Edit Budget Multiplier',
            openModal: true,
            openAddModal: true,
            modalBody: <EditBudgetRuleForm
                row={row}
                updateDataTableAfterSubmit={this.getAllBudgetRulesFromDb}
                handleModalClose={this.handleModalClose}
            />,
            maxWidth: 'lg'
        })
    }

    componentDidMount() {
        this.setState({loading: true});
        this.getAllBudgetRulesFromDb();
    }

    componentDidUpdate(prevProps, prevState, snapshot) {

        if (snapshot !== null) {
            if (this.props.isDataTableReload || this.state.isDataTableLoaded) {
                this.getAllBudgetRulesFromDb();
            }
            return null;
        }
    }

    getAllBudgetRulesFromDb = () => {
        getAllBudgetRules((data) => {
            if (this.props.isDataTableReload || this.state.isDataTableLoaded) {
                this.props.updateDataTable();
            }
            this.setState({
                data: data,
                originalData: data,
                totalRows: data.length,
                loading: false,
            })
        });
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
                     className="budgetRuleDataTable scrollableDatatable">
                    <Card className="overflow-hidden">
                        <div className="flex p-5">
                            <div className="font-semibold w-3/12">Active Budget Multiplier Rules</div>
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
                                columns={this.budgetRuleColumns()}
                                data={data}
                                pagination
                                paginationTotalRows={totalRows}
                                progressPending={loading}
                                progressComponent={<LinearIndeterminate/>}
                                persistTableHead
                            />
                        </div>
                    </Card>

                    <ModalDialog
                        open={this.state.openModal}
                        title={this.state.modalTitle}
                        id={this.state.id}
                        handleClose={this.handleModalClose}
                        component={this.state.modalBody}
                        maxWidth={this.state.maxWidth}
                        fullWidth={true}
                        cancelEvent={this.handleModalClose}
                        disable
                        modelClass={"budgetRuleModule"}
                    />
                </div>
            </>
        )
    }
}

export default withStyles(styles)(connect(null)(BudgetRuleDataTables));