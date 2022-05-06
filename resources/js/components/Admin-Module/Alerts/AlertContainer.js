import React, {Component} from "react";
import {Helmet} from "react-helmet";
import Card from '@material-ui/core/Card';
import {withStyles} from "@material-ui/core/index";
import AlertCreation from "./AlertCreation/AlertCreation";
import SearchIcon from "@material-ui/icons/Search";
import PrimaryButton from "../../../general-components/PrimaryButton";
import './alertManagement.scss';
import clsx from "clsx";
import DataTable from "react-data-table-component";
import {LinearIndeterminate} from "../../../general-components/DT-Linear-ProgressBar/DataTablePB";
import {getAlertsDataApi} from "./ApiCalls";
import {alertColumns} from "./Datatables/DataTableColumns";
import ModalDialog from "../../../general-components/ModalDialog";
import DeleteAlert from "./Delete/DeleteAlert";
import moment from "moment";


const styles = theme => ({
    card: {
        borderRadius: 15,
        border: '1px solid #e1e1e3',
        backgroundColor: '#fffff',
        boxShadow: "none",
    },
});

export const ViewPermissions = (props) => {

    return (
        <>
            <div className="flex flex-col justify-center items-center text-gray-600">
                    <div>
                        <div className="font-medium">Alert have following Module Permissions</div>
                        <ul>
                            {props.row.dayPartingAlertsStatus ? <li>Day Parting</li> : ''}
                            {props.row.biddingRuleAlertsStatus ? <li>Bidding Rule</li> : ''}
                            {props.row.tacosAlertsStatus ? <li>Tacos</li> : ''}
                            {props.row.bidMultiplierAlertsStatus ? <li>Bid Multiplier</li> : ''}
                        </ul>
                    </div>

                <PrimaryButton
                    btnlabel={"OK"}
                    variant={"contained"}
                    onClick={props.handleModalClose}
                />
            </div>

        </>
    )
}

class AlertContainer extends Component {
    constructor(props) {
        super(props);
        this.state = {
            loading: true,
            openAddModal: false,
            infoModal: false,
            confirmMsgModal: false,
            rowId: null,
            originalData: [],
            data: [],
            totalRows: 0,
            perPage: 10,
            isDataTableReload: false,
            isEdit: false,
            brandOptions: [],
            openModal: false,
            modalBody: '',
            maxWidth: 'md',
        }
    }

    componentDidMount() {
        this.getAllAlerts()
    }

    getAllAlerts = () => {
        getAlertsDataApi((response) => {
            let data = response.alerts;
            this.setState({
                data,
                originalData: data,
                totalRows: data.length,
                loading: false,
            })
        })
    }

    onDataTableSearch = (e) => {

        if (e.target.value.length > 0) {
            var result = this.state.originalData.filter(row => {
                return row.alertName.toString().toLowerCase().includes(e.target.value.toLowerCase())
                    || row.serial.toString().toLowerCase().includes(e.target.value)
                    || row.accounts.toLowerCase().includes(e.target.value.toLowerCase())
            });
            this.setState({
                data: result,
                totalRows: result.length
            })
        } else {
            this.setState({
                data: this.state.originalData,
                totalRows: this.state.originalData.length
            })
        }
    }
    updateDataTableAfterSubmit = () => {
        this.setState({
            openModal: false,
            modalBody: '',
            maxWidth: 'md',
        }, () => {
            this.getAllAlerts();
        })
    }
    openAddForm = () => {
        this.setState({
            isEdit: false
        }, () => {
            this.setState({
                modalTitle: 'Add Alert',
                openModal: true,
                openAddModal: true,
                modalBody: <AlertCreation isEdit={this.state.isEdit}
                                          updateDataTableAfterSubmit={this.updateDataTableAfterSubmit}
                                          handleModalClose={this.handleModalClose}/>,
            })
        })

    }

    editAlert = (row) => {

        this.setState({
            isEdit: true
        }, () => {
            this.setState({
                modalTitle: 'Edit Alert',
                openModal: true,
                openAddModal: true,
                modalBody: <AlertCreation isEdit={this.state.isEdit} row={row}
                                          updateDataTableAfterSubmit={this.updateDataTableAfterSubmit}
                                          handleModalClose={this.handleModalClose}/>,
            })
        })
    }

    deleteAlert = (rowId) => {
        this.setState({
            isEdit: false
        }, () => {
            this.setState({
                modalTitle: 'Delete Alert',
                openModal: true,
                openAddModal: true,
                modalBody: <DeleteAlert rowId={rowId} updateDataTableAfterSubmit={this.updateDataTableAfterSubmit}
                                        handleModalClose={this.handleModalClose}/>,
                maxWidth: 'xs'
            })
        })
    }

    viewPermission = (row) => {
        this.setState({
            isEdit: false
        }, () => {
            this.setState({
                modalTitle: 'View Permissions',
                openModal: true,
                openAddModal: true,
                modalBody: <ViewPermissions row={row} handleModalClose={this.handleModalClose}/>,
                maxWidth: 'xs'
            })
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
        const {classes} = this.props;
        return (
            <>
                <Helmet>
                    <title>Pulse Advertising | Alerts</title>
                </Helmet>

                {/*Alert Creation*/}
                <div className="overflow-hidden" style={{display: 'table', tableLayout: 'fixed', width: '100%'}}
                     className={"alertManagementModule"}>
                    <Card classes={{root: classes.card}}>
                        <div className="flex p-5">
                            <div className="font-semibold w-3/12">Alerts List</div>
                            <div className="searchDataTable w-9/12">
                                <div
                                    className="border border-gray-300 border-solid flex inputGroup mr-4 px-3 py-1 rounded-full w-9/12 ml-auto">
                                    <input type="text"
                                           className="border-0 flex-1 focus:outline-none font-semibold outline-none px-2 text-xs"
                                           placeholder="Search"
                                           onChange={this.onDataTableSearch}
                                    />
                                    <SearchIcon className="text-gray-300"/>
                                </div>
                            </div>

                            <div className="w-2/12">
                                <PrimaryButton
                                    btnlabel={"Add Alert"}
                                    variant={"contained"}
                                    onClick={this.openAddForm}
                                />
                            </div>

                        </div>
                        <div className={clsx("w-full dataTableContainer")}>
                            <DataTable
                                Clicked
                                noHeader={true}
                                wrap={false}
                                responsive={true}
                                columns={alertColumns(this.deleteAlert, this.editAlert, this.viewPermission)}
                                data={this.state.data}
                                pagination
                                paginationTotalRows={this.state.totalRows}
                                progressPending={this.state.loading}
                                progressComponent={<LinearIndeterminate/>}
                                persistTableHead
                            />
                        </div>
                    </Card>
                </div>
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
                    modelClass={"alertFormPopUp"}
                />
            </>
        )
    }
}

export default withStyles(styles)(AlertContainer)