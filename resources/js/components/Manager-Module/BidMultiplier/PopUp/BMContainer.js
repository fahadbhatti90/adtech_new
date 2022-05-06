import React, {Component} from 'react';
import clsx from 'clsx';
import '../../../../general-components/TagManager/CustomMaterailize.scss';
import CloseIcon from '@material-ui/icons/Close';
import CheckIcon from '@material-ui/icons/Check';
import {getNumericValFromString} from "../Helper";
import {post, bidMultiplierPut} from '../../../../../js/service/service';
import $ from 'jquery';
import '../../../../general-components/TagManager/TagManager.scss';
import '../../../../general-components/TagManager/TagManagerContainer/TMContainer.scss';
import '../../../../general-components/TagManager/CampaignTagging.scss';
import moment from "moment";
import '../bidMultiplier.scss';
import ModalDialog from '../../../../general-components/ModalDialog';
import {Card} from "@material-ui/core";
import DataTable from "react-data-table-component";

const DotForTM = (props) => <span className="itemAdded"></span>;
const ExtraDotCounterForTM = (props) => <span className="extraDotCounter">+{props.count}</span>;

function generate(element, elements) {
    return Array.from({length: elements}, (_, idx) => `${++idx}`).map((value, index) => {
        return React.cloneElement(element, {
            key: value,
        })
    });
}//end function
export const headerOverLapCampaigns = () => [
    {
        name: 'Campaign Name',
        selector: 'name',
        wrap: true,
        cell: (row) => {
            return row.name;
        }
    },
    {
        name: 'Start Date',
        selector: 'startDate',
        wrap: true,
        cell: (row) => {
            return row.startDate;
        }
    },
    {
        name: 'End Date',
        selector: 'endDate',
        sortable: false,
        wrap: false,
        cell: (row) => {
            return row.endDate;
        },
        minWidth: '120px',
    }]

const CampaignOverlapPopUp = (props) => {

    return (
        <>
            <div className="p-5 rounded-lg dayPartingDatatable">
                <Card className="overflow-hidden">
                    <div className={"w-full dataTableContainer"}>
                        <DataTable
                            noHeader={true}
                            wrap={false}
                            responsive={true}
                            columns={headerOverLapCampaigns()}
                            data={props.campaignData}
                            persistTableHead
                        />
                    </div>
                </Card>
            </div>
        </>
    )
}

export class BMContainer extends Component {
    constructor(props) {
        super(props);
        this.state = {
            dotsLimit: 0,
            dotCounter: props.dots,
            move: false,
            isMoved: true,
            startDate: moment(new Date()).format('l'),
            endDate: moment(new Date()).format('l'),
            bidMultiplierData: {
                increaseBid: "",
                decreaseBid: "",
                isSaving: false,
            },
            bidMultiplierPopup: {
                isLoading: false,
                isEditing: false,
            },
            bidMErrorMessage: {
                message: "Write something Then Press Enter",
                isValid: true,
                isLoading: false,
            },
            bidMTimer: 3,
            bidConfirmation: {
                openConfirmationPopup: false,
                confirmationField: ""
            },
            overlapCampaigns: [],
            isStartDateDisable:true,
            isEndDateDisable:true,
        }
    }

    componentDidMount() {

        let itemCounterWidth = $(".itemCounts").width();
        let dotsLimit = Math.floor(itemCounterWidth / 12) - 1;
        let currentD = this.state.startDate;
        let startD = this.props.row && this.props.row.startDate ? moment(this.props.row.startDate).format("l") : '';
        let endD = this.props.row && this.props.row.endDate ? moment(this.props.row.endDate).format("l") : '';

        if (startD != '' && new Date(currentD) >=  new Date(startD)) {
            this.setState({
                isStartDateDisable: false
            })
        }

        if (endD != '' && new Date(endD) <  new Date(currentD)) {
            this.setState({
                isEndDateDisable: false
            })
        }
        this.setState({
            dotsLimit,
            bidMultiplierData: {
                increaseBid: this.props.row && this.props.row.bid.indexOf('+') == 0 ? getNumericValFromString(this.props.row.bid) : "",
                decreaseBid: this.props.row && this.props.row.bid.indexOf('-') == 0 ? getNumericValFromString(this.props.row.bid) : "",
                isEditing: this.props.isEditing,
                isSaving: false,
            },
            startDate:this.props.row && this.props.row.startDate ? moment(this.props.row.startDate).format('l') : this.state.startDate,
            endDate:this.props.row && this.props.row.endDate ? moment(this.props.row.endDate).format('l'): this.state.endDate,
            bidConfirmation: {
                openConfirmationPopup: false,
                confirmationField: ""
            }
        })
    }

    static getDerivedStateFromProps(nextProps, prevState) {

        if (nextProps.dots) {
            return ({
                dotCounter: nextProps.dots,
            });
        }
        return null;
    }

    onDateChange = (range) => {

        let startDate = moment(range.startDate).format('l');
        let endDate = moment(range.endDate).format('l');

        this.setState({
            startDate: startDate,
            endDate: endDate
        })
    }

    handleAddOnKeyUp = (e) => {

        if (this.state.bidMErrorMessage.isLoading || !this.state.bidMErrorMessage.isValid) return;
        let campaignIds = this.props.selectedObject;
        var bidValue = '';
        const {increaseBid, decreaseBid, isEditing} = this.state.bidMultiplierData;
        const {startDate, endDate} = this.state;

        if ((!e.keyCode || e.keyCode == 13) && Object.size(campaignIds) > 0) {
            if (increaseBid == "" && decreaseBid == "") {
                this.helperShowInputInvalidMessage("Please fill out increase or decrease bid", 3000);
                return;
            } else {
                bidValue = (increaseBid != "") ? '+' + increaseBid + '%' : '-' + decreaseBid + '%';
            }
            if ( endDate != '' && (new Date(startDate) > new Date(endDate)) || (new Date(endDate) < new Date(startDate))) {
                this.helperShowInputInvalidMessage("End date should be greater or equal to start date", 3000);
                return;
            }

            this.helperSetLoader(true);
            let ajaxData = {
                bid: bidValue,
                campaignIds: campaignIds,
                startDate: moment(startDate).format('YYYY-MM-DD'),
                endDate: moment(endDate).format('YYYY-MM-DD'),
                _token: $("body").attr("csrf")
            };

            if (isEditing){
                ajaxData.id = this.props.row.id;
                ajaxData.campaignId = this.props.row.strCampaignId;
                this.updateBidMultiplier(ajaxData)
            }else{
                this.addBidMultiplier(ajaxData)
            }

        } //end if
    }//end function
    addBidMultiplier = (ajaxData) => {
        delete ajaxData.id;
        axios.post(
            window.baseUrl + "/bidMultiplier",
            {
                ...ajaxData,
            },
        ).then((response) => {
            if (response.data.status) {
                this.helperSetLoader(false);
                this.props.showDataTableLoader();
            } else {
                this.openPopUpConfirmation(response.data.overlapCampaigns);
//                this.helperShowAddInputInvalid("Internal Server Error");
                this.helperSetLoader(false);
            }
        }).catch((error) => {
            this.openPopUpConfirmation();
//            this.helperShowAddInputInvalid(error);
            this.helperSetLoader(false);
        });
    }

    updateBidMultiplier = (ajaxData) => {
        delete ajaxData._token;
        delete ajaxData.campaignIds;
        axios.put(
            window.baseUrl + "/bidMultiplier/" + this.props.row.id,
            {
                ...ajaxData,
            },
        ).then((response) => {
            if (response.data.status) {
                this.helperSetLoader(false);
                this.props.reloadHistoryDatatable();
            } else {
                this.openPopUpConfirmation(response.data.overlapCampaigns);
                this.helperSetLoader(false);
            }
        }).catch((error) => {
            this.helperShowAddInputInvalid("Fail to update bid see console for further details");
            this.helperSetLoader(false);
        });
    }

    helperShowAddInputInvalid(message = "Error please try again :-(", messageDisappearingTime = 3000) {
        this.setState({bidMTimer: (messageDisappearingTime / 1000)})
        this.helperSetAddBidMInputValue(null, message, false);
        let _self = this;

        const bidMTimerInterval = setInterval(() => {
            _self.setState({tacosTimer: _self.state.bidMTimer >= 0 ? _self.state.bidMTimer - 1 : 0})
        }, 1000)
        setTimeout(function () {
            _self.helperSetAddBidMInputValue(null, message, true);
            _self.setState({bidMTimer: 3})
            clearInterval(bidMTimerInterval);
        }, messageDisappearingTime);
    }

    helperSetAddBidMInputValue = (value, message, isValid) => {
        const {bidMErrorMessage} = this.state;

        if (!value && !message && isValid == null) return;

        if (message) {
            bidMErrorMessage.message = message;
        }
        if (isValid != null) {
            bidMErrorMessage.isValid = isValid;
        }
        this.setState({
            bidMErrorMessage: (bidMErrorMessage)
        });
    }
    helperSetLoader = (isLoading) => {
        const {bidMErrorMessage, bidMultiplierPopup} = this.state;
        bidMErrorMessage.isLoading = isLoading;
        bidMultiplierPopup.isLoading = isLoading;
        this.setState({
            bidMErrorMessage,
            bidMultiplierPopup
        });
    }

    helperShowInputInvalidMessage(message = "Error please try again :-(", messageDisappearingTime = 3000) {
        this.setState({bidMTimer: (messageDisappearingTime / 1000)})
        this.helperSetAddBidMInputValue(null, message, false);
        let _self = this;

        const timeInterval = setInterval(() => {
            _self.setState({bidMTimer: _self.state.bidMTimer >= 0 ? _self.state.bidMTimer - 1 : 0})
        }, 1000)
        setTimeout(function () {
            _self.helperSetAddBidMInputValue(null, message, true);
            _self.setState({bidMTimer: 3})
            clearInterval(timeInterval);
        }, messageDisappearingTime);
    }

    handlePopupClose = (e) => {
        this.setState((prevState) => ({
            bidMultiplierData: {
                ...prevState.bidMultiplierData,
            }
        }));
        this.props.onBidMultiplierPopupClose && this.props.onBidMultiplierPopupClose(true);
    }

    getInput = (name, value) => {

        this.setState(prevState => ({
            bidMultiplierData: {
                ...prevState.bidMultiplierData,
                [name]: value
            },
            bidConfirmation: {
                openConfirmationPopup: false,
            }
        }))
    }
    openPopUpConfirmation = (campaignData) => {

        if (campaignData) {
            this.setState({
                overlapCampaigns: campaignData
            });
        }
        this.setState({
            bidConfirmation: {
                openConfirmationPopup: true
            }
        });
    }
    handleSingleDateChange = (date) => {
        this.setState({
            startDate: moment(date).format('l'),
        })
    }

    handleSingleEndDateChange = (date) => {
        this.setState({
            endDate: moment(date).format('l'),
        })
    }

    render() {
        const {
            dotsLimit,
            dotCounter,
            move,
            bidMErrorMessage,
            bidMultiplierPopup,
            bidMultiplierData,
            bidMTimer,
            bidConfirmation
        } = this.state;
        const {ChildComponent} = this.props;
        let totalDots = [];
        if (dotCounter <= dotsLimit) {
            totalDots = generate(<DotForTM/>, dotCounter);
        } else {
            totalDots = generate(<DotForTM/>, dotsLimit);
            totalDots.push(generate(<ExtraDotCounterForTM count={(dotCounter - dotsLimit)}/>, 1));
        }


        return (
            <>
                <div
                    className={`bidMultiplier tacosGroupManager tagGroupManager shadow overflow-hidden materializeCss ${!bidMErrorMessage.isValid && "invalid"}`}>
                    <div className="responseMessage">
                        <p>{bidMErrorMessage.message}</p>
                        <div className="absolute centerChild flex responseMessageTime">{bidMTimer}</div>
                    </div>
                    <div className="flex row">
                        <div className="progress"
                             style={bidMultiplierPopup.isLoading ? {display: "block"} : {display: "none"}}>
                            <div className="indeterminate"></div>
                        </div>
                        <div
                            className="flex h-full items-center justify-center m-0 p relative section1 sectionCustom text-center text-white w-1/12">
                            <div className="counter">{dotCounter}</div>
                        </div>
                        <div
                            className="font-hairline h-full overflow-hidden section2 sectionCustom sm:w-2/12 w-8/12 whitespace-no-wrap">
                            <div className={clsx("min-h-full pl-5 text-sm pt-3 statsSection ", move ? "moved" : "")}
                                 style={move ? {transform: "translateY(-100%)"} : {}}>
                                <span className="itemLabel">Items</span> Selected
                                <div className="itemCounts">
                                    {
                                        dotCounter > 0 ?
                                            totalDots
                                            :
                                            null
                                    }
                                </div>
                            </div>
                        </div>
                        <div
                            className="flex font-normal relative section3 items-center sectionCustom text-right sm:w-8/12 w-2/12">
                            <div className="control settingControl">
                                <div>Settings</div>
                            </div>
                            <div className="controlsContainer">
                                <ChildComponent
                                    getInput={this.getInput}
                                    bidMultiplierData={this.state.bidMultiplierData}
                                    handleAddOnKeyUp={this.handleAddOnKeyUp}
                                    setStartDateValue={this.handleSingleDateChange}
                                    setEndDateValue={this.handleSingleEndDateChange}
                                    startDateDP={this.state.startDate}
                                    endDateDP={this.state.endDate}
                                    isStartDateDisable={this.state.isStartDateDisable}
                                    isEndDateDisable={this.state.isEndDateDisable}

                                />
                                <div className="control deleteControl" unselectable="on"
                                     onClick={this.handleAddOnKeyUp}>
                                    <CheckIcon/>
                                    <div>submit</div>
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center justify-center section4 sectionCustom w-1/12">
                            <div className="closeButton" onClick={this.handlePopupClose}>
                                <CloseIcon/>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <ModalDialog
                        open={bidConfirmation.openConfirmationPopup}
                        title={"List of Campaigns exist in different dates  "}
                        handleClose={() => this.setState({
                            bidConfirmation: {
                                openConfirmationPopup: false,
                                confirmationField: "",
                            }
                        })}
                        component={<CampaignOverlapPopUp
                            getInput={this.getInput}
                            closeTacosConfirmationPopup={
                                () => {
                                    this.setState({
                                        bidConfirmation: {
                                            openConfirmationPopup: false,
                                            confirmationField: "",
                                        }
                                    })
                                }
                            }
                            campaignData={this.state.overlapCampaigns}
                        />}
                        maxWidth={"md"}
                        fullWidth={true}
                        disable={true}
                        cancel={true}
                        modelClass={"BMOverLapCampaigns"}
                    />
                </div>
            </>
        )
    }
}