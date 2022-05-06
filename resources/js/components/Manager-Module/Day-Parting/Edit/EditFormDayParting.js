import React, {Component} from 'react';
import {withStyles} from "@material-ui/core/styles";
import {styles} from "../styles";
import {getCampaigns, showEditPartingForm, storeEditDayPartingForm} from '../apiCalls'
import moment from "moment";
import {connect} from 'react-redux';
import MomentUtils from "@date-io/moment";
import {Grid} from "@material-ui/core";
import TextFieldInput from "./../../../../general-components/Textfield";
import SingleSelect from "./../../../../general-components/Select";
import MultiSelect from "./../../../../general-components/MultiSelect";
import CheckBox from "./../../../../general-components/CheckBox";
import {MuiPickersUtilsProvider, TimePicker} from "@material-ui/pickers";
import CcEmail from "./../../../../general-components/EmailCC/EmailChips";
import {breakCampaignName} from "./../../../../helper/helper";
import DayPartingModal from "../DayPartingModal";
import PortfolioCampaignRemove from "./DeleteEditScheduleOptions";
import ActivatedSchedules from "../Datatables/ActivatedSchedules";
import * as Yup from "yup";
import {ShowSuccessMsg} from "./../../../../general-components/successDailog/actions";
import {ShowFailureMsg} from "./../../../../general-components/failureDailog/actions";
import TextButton from "./../../../../general-components/TextButton";
import PrimaryButton from "./../../../../general-components/PrimaryButton";
import {objectRequiredValidationHelper, stringRequiredValidationHelper} from "./../../../../helper/yupHelpers";
import {daysInitialState} from "../daysInitialState";
import CustomDatePicker from "../DatePicker/CustomDatePicker";
import DayRows from "../DatePicker/CustomTime/DayRows";
import HeaderRow from "../DatePicker/CustomTime/HeaderRow";
//import EditHoursRows from "../DatePicker/CustomTime/HoursRows";
import EditHoursRows from "../DatePicker/CustomTime/EditHoursRows";
import Selecto from "react-selecto";

const list = (errorsData) => {

    return (
        <>
            {errorsData.map(item => (
                <li className="list-none"> {item} </li>
            ))}
        </>
    )
};

const hours = [
    "0-2",
    "3-5",
    "6-8",
    "9-11",
    "12-14",
    "15-17",
    "18-20",
    "21-23"
]
const sorter = {
    "days": 0, // << if sunday is first day of week
    "monday": 1,
    "tuesday": 2,
    "wednesday": 3,
    "thursday": 4,
    "friday": 5,
    "saturday": 6,
    "sunday": 7
}

class EditFormDayParting extends Component {
    constructor(props) {
        super(props);
        this.editDpRef = React.createRef();
        this.state = {
            ...daysInitialState,
            id: '',
            genericShowError: false,
            scheduleName: "",
            scheduleNameError: '',
            selectedProfile: null,
            selectedPfCampaign: null,
            tempSelectedPfCampaign: '',
            selectedPfCampaignError: '',
            oldPortfolioCampaignType: null,
            selectedPfCampaignOption: [],
            selectedPortfoliosCampaigns: null,
            selectedPortfoliosCampaignsError: '',
            PortfoliosCampaignsOptions: [],
            tempPortfoliosCampaignsOptions: [],
            tempPortfoliosCampaigns: [],
            removePortfoliosCampaigns: [],
            startDateDP: moment(new Date()).format("MM-DD-YYYY"),
            isStartDateDisable: true,
            endDateDP: '',
            showDRP: false,
            showDRPEnd: false,
            emailReceiptStart: false,
            emailReceiptEnd: false,
            ccEmail: [],
            modalTitle: "",
            modalBody: "",
            maxWidth: "xs",
            callback: 0,
            preSelectEmail: [],
            scheduleCountPopup: 1,
            campaignOptionSelected: 1,
            errorStartDate: "",
            errorEndDate: "",
            errors: {
                scheduleN: "",
                selectedPfCp: "",
                selectedPfsCps: ""
            }
        }
    }

    componentDidMount = () => {
        let id = this.props.id;
        let editParams = {
            'scheduleId': id
        }

        showEditPartingForm(editParams, (response) => {

            let allPortfolios = response.allPortfolios;
            let allCampaigns = response.allCampaignListRecord;
            let selectedCampaigns = response.allScheduleData.campaigns;
            let selectedPortfolios = response.allScheduleData.portfolios;
            let scheduleData = response.allScheduleData;

            if (scheduleData.portfolioCampaignType == 'Campaign') {
                if (allCampaigns.length > 0) {
                    let campaigns = allCampaigns.map((obj, idx) => {
                        return {
                            value: obj.id + '|' + obj.name,
                            label: obj.name
                        }
                    })

                    this.setState({
                        PortfoliosCampaignsOptions: campaigns,
                        tempPortfoliosCampaignsOptions: campaigns,
                        selectedPfCampaign: scheduleData.portfolioCampaignType
                    })
                }

                if (selectedCampaigns.length > 0) {
                    let selectedCamp = selectedCampaigns.map((obj, idx) => {
                        return {
                            value: obj.id + '|' + obj.name,
                            label: obj.name
                        }
                    })

                    this.setState({
                        selectedPortfoliosCampaigns: selectedCamp,
                        tempPortfoliosCampaigns: selectedCamp
                    })
                }
            }

            if (scheduleData.portfolioCampaignType == 'Portfolio') {
                if (allPortfolios.length > 0) {

                    let portfolios = allPortfolios.map((obj, idx) => {
                        return {
                            value: obj.id + '|' + obj.name,
                            label: obj.name
                        }
                    })

                    this.setState({
                        PortfoliosCampaignsOptions: portfolios,
                        tempPortfoliosCampaignsOptions: portfolios,
                        selectedPfCampaign: scheduleData.portfolioCampaignType
                    })
                }

                if (selectedPortfolios.length > 0) {
                    let selectedPort = selectedPortfolios.map((obj, idx) => {
                        return {
                            value: obj.id + '|' + obj.name,
                            label: obj.name,
                            selected: 'selected'
                        }
                    })

                    this.setState({
                        selectedPortfoliosCampaigns: selectedPort,
                        tempPortfoliosCampaigns: selectedPort
                    })
                }
            }
            let days = scheduleData.selectionHours;

            let tmpDays = [];
            Object.keys(days).map(function (key) {
                let value = days[key];
                let index = sorter[key.toLowerCase()];

                tmpDays[index] = {
                    key: key,
                    value: value
                };
            });

            let orderedHoursData = {};
            tmpDays.forEach(function (obj) {
                orderedHoursData[obj.key] = obj.value;
            });
            let currentD = this.state.startDateDP;
            let startD = moment(scheduleData.startDate).format("MM-DD-YYYY");
            if (startD < currentD) {
                this.setState({
                    isStartDateDisable: false
                })
            }

            this.setState({
                id: id,
                scheduleName: scheduleData.scheduleName,
                selectedPfCampaign: {
                    value: scheduleData.portfolioCampaignType,
                    label: scheduleData.portfolioCampaignType,
                    selected: 'selected'
                },
                selectedPfCampaignOption: [
                    {
                        value: "Campaign", label: 'Campaign'
                    },
                    {value: "Portfolio", label: 'Portfolio'}
                ],
                tempSelectedPfCampaign: {
                    value: scheduleData.portfolioCampaignType,
                    label: scheduleData.portfolioCampaignType
                },
                oldPortfolioCampaignType: scheduleData.portfolioCampaignType,
                startDateDP: moment(scheduleData.startDate).format("MM-DD-YYYY"),
                endDateDP: (scheduleData.endDate != null) ? moment(scheduleData.endDate).format("MM-DD-YYYY") : '',
                selectedProfile: scheduleData.fkProfileId,
                emailReceiptStart: (scheduleData.emailReceiptStart == 1),
                emailReceiptEnd: (scheduleData.emailReceiptEnd == 1),
                ccEmail: (scheduleData.ccEmails.length > 0) ? scheduleData.ccEmails.split(',') : [],
                days: orderedHoursData
            }, () => {

                console.log('days updated', orderedHoursData)
                let selectedBoxes = $(`.editDpGridView div.bg-yellow-600`).toArray();
                this.editDpRef.current.setSelectedTargets(selectedBoxes)
            })
        })
    }

    checkIfDataIsAvailable = (temp, actualValue) => {
        let getRemovedValues = [];
        //if (temp && actualValue && actualValue.length > 0) {
        if (temp) {
            getRemovedValues = temp.filter(function (el) {
                let isExists = false;
                $.each(actualValue, function (indexInArray, valueOfElement) {
                    if (isExists) return;
                    isExists = el.value == valueOfElement.value;
                });
                return !isExists;
            });
        }
        return getRemovedValues;
    }
    makeRemovableCampaigns = (temp, actualValue) => {

        if (temp) {
            let getRemovedValues = temp.filter(function (el) {
                let isExists = false;
                $.each(actualValue, function (indexInArray, valueOfElement) {
                    if (isExists) return;
                    isExists = el.value == valueOfElement.value;
                });
                return !isExists;
            });

            this.setState({
                removePortfoliosCampaigns: getRemovedValues
            })
        }
    }
    // This functions calls when Add or Remove Portfolio and campaign
    onPortfoliosCampaignsChange = (value) => {
        if (value && value.length == 0) {
            value = null
        }
        let result = this.checkIfDataIsAvailable(this.state.tempPortfoliosCampaigns, value);
        if (result.length > 0 && this.state.scheduleCountPopup == 1) {
            this.setState({
                scheduleCountPopup: this.state.scheduleCountPopup + 1,
                modalTitle: 'Day Parting Schedule',
                maxWidth: 'sm',
                modalBody: <PortfolioCampaignRemove handleModalClose={this.handleModalClose}
                                                    onRadioChange={this.onRadioChange}
                                                    campaignOptionSelected={this.state.campaignOptionSelected}
                                                    removeCampaignsAfterConfirmation={this.removeCampaignsAfterConfirmation}
                />,
                openModal: true,
            }, () => {
                let tempPc = this.state.selectedPortfoliosCampaigns;
                this.setState({
                    selectedPortfoliosCampaigns: value
                }, () => {
                    if (tempPc && value && tempPc.length <= value.length) {
                        $(".autoScrl .select__value-container").animate({
                            scrollTop: $('.autoScrl .select__value-container').get(0).scrollHeight
                        });
                    }
                    this.resetErrors("selectedPfsCps");
                    this.makeRemovableCampaigns(this.state.tempPortfoliosCampaigns, value)
                })

            })
        } else {
            this.setState({
                selectedPortfoliosCampaigns: value
            }, () => {
                this.resetErrors("selectedPfsCps");
                this.makeRemovableCampaigns(this.state.tempPortfoliosCampaigns, value)
            })
        }
    }
    onRadioChange = (value) => {
        this.setState({
            campaignOptionSelected: value
        }, () => {

        })
    }
    // Get Campaigns on the basis of profile
    getPortfolioCampaigns = () => {
        let pf = this.state.selectedProfile;
        let cpt = this.state.selectedPfCampaign.value;
        let oldCpt = this.state.oldPortfolioCampaignType.value;

        getCampaigns(pf, cpt, oldCpt, 'edit', (data) => {
            if (data.length > 0) {
                //success
                this.setState({
                    PortfoliosCampaignsOptions: data,
                    tempPortfoliosCampaigns: [],
                })
            } else {
                //this.props.dispatch(ShowFailureMsg("No Data Found ", "Failed", true, ""));
            }
        }, (err) => {
            //error
            // this.props.dispatch(showSnackBar());
        });
    }

    onChangeText = (e) => {
        this.setState({
            scheduleName: e.target.value
        }, () => {
            this.resetErrors("scheduleN")
        })

    }

    checkIfTypePreSelect = (temp, inputValue) => {
        let isReturn = false;
        if ((inputValue) && inputValue.value == temp) {
            isReturn = true;
        }
        return isReturn;
    }

    onPfCampaignChange = (value) => {

        let returnResult = this.checkIfTypePreSelect(this.state.oldPortfolioCampaignType, value)

        if (!returnResult) {
            // three options pop up will come here
            this.setState({
                modalTitle: 'Day Parting Schedule',
                maxWidth: 'sm',
                modalBody: <PortfolioCampaignRemove handleModalClose={this.handleModalClose}
                                                    onRadioChange={this.onRadioChange}
                                                    campaignOptionSelected={this.state.campaignOptionSelected}
                                                    removeCampaignsAfterConfirmation={this.removeCampaignsAfterConfirmation}/>,
                openModal: true
            }, () => {

                this.setState({
                    selectedPfCampaign: value,

                }, () => {
                    this.resetErrors("selectedPfCp");
                    this.setState({
                        selectedPortfoliosCampaigns: null,
                        PortfoliosCampaignsOptions: null,
                        removePortfoliosCampaigns: [],
                    })
                    this.callPortfolioCampaign();
                })
            })
        } else {
            this.setState({
                selectedPfCampaign: value,
            }, () => {
                this.resetErrors("selectedPfCp");
                this.setState({
                    selectedPortfoliosCampaigns: null,
                    PortfoliosCampaignsOptions: null,
                    removePortfoliosCampaigns: [],
                })
                this.callPortfolioCampaign();
            })
        }
    }
    removeCampaignsAfterConfirmation = () => {
        this.handlePopUpPfCampaignClose();
    }
    callPortfolioCampaign = () => {
        if (this.state.selectedPfCampaign) {
            this.getPortfolioCampaigns();
        }
    }

    onCheckBoxesChangeHandler = (e) => {
        this.setState({
            [e.target.name]: e.target.checked
        }, () => {
        })
    }

    onStartDateChangeHandler = (startTime) => {
        this.setState({
            startTime: startTime,
            endTime: moment(startTime, [moment.ISO_8601, 'HH:mm']).add(1, 'minutes')
        })
    };

    onEndDateChangeHandler = (endTime) => {
        this.setState({
            endTime: endTime
        })
    };

    getUpdatedItems = (items) => {
        this.setState({
            ccEmail: items
        })
    }

    handlePopUpPfCampaignClose = () => {
        this.setState({
            openModal: false,
            modalBody: '',
            maxWidth: 'md'
        })
    }

    handleModalClose = () => {
        this.setState({
            openModal: false,
            modalBody: '',
            maxWidth: 'md',
            scheduleCountPopup: 1,
            selectedPortfoliosCampaigns: this.state.tempPortfoliosCampaigns,
            selectedPfCampaign: this.state.tempSelectedPfCampaign,
            PortfoliosCampaignsOptions: this.state.tempPortfoliosCampaignsOptions
        })
    }

    formSubmissionDayParting = event => {
        event.preventDefault();
        var isDateLess = false;
        let startDate = this.state.startDateDP;
        let endDate = this.state.endDateDP;
        if (endDate != '' && (new Date(startDate) > new Date(endDate)) || (new Date(endDate) < new Date(startDate))) {
            this.setState({
                errorEndDate: "End date should be greater or equal to start date",
            })
            isDateLess = true
        } else {
            this.setState({
                errorEndDate: ""
            })
        }
        let validationSchema = Yup.object().shape({
            scheduleN: stringRequiredValidationHelper("schedule name")
                .matches(
                    /^[a-zA-Z0-9:_-]+$/,
                    "The name can only consist of alphabetical,number,underscore,hyphen and colon"
                ),
            selectedPfCp: stringRequiredValidationHelper("portfolio/campaign"),
            selectedPfsCps: objectRequiredValidationHelper("portfolios/campaigns"),
        });

        let dataToValidateObject = {
            scheduleN: this.state.scheduleName,
            selectedPfCp: this.state.selectedPfCampaign,
            selectedPfsCps: this.state.selectedPortfoliosCampaigns,
        };

        let validationFormData = htk.validateAllFields(validationSchema, dataToValidateObject);

        if (Object.size(validationFormData) > 0 || isDateLess) {
            const {errors} = this.state;
            $.each(validationFormData, function (indexInArray, valueOfElement) {
                errors[indexInArray] = valueOfElement;
            });
            this.setState((prevState) => ({
                errors: errors
            }));
        } else {

            var removeCampaigns;
            if (this.state.removePortfoliosCampaigns.length > 0) {
                let tempRemove = this.state.removePortfoliosCampaigns.map(item => {
                    return breakCampaignName(item.value);
                });
                removeCampaigns = tempRemove.join(',');
            }
            let id = this.state.id;
            let scheduleName = this.state.scheduleName;
            let profileId = this.state.selectedProfile;
            let selectedPfCampaign = this.state.selectedPfCampaign;
            let oldPortfolioCampaignType = this.state.oldPortfolioCampaignType;
            let selectedPortfoliosCampaigns = this.state.selectedPortfoliosCampaigns;
            let startDate = this.state.startDateDP;
            let endDate = this.state.endDateDP;
            let ccEmails = this.state.ccEmail;
            let emailReceiptStart = this.state.emailReceiptStart;
            let emailReceiptEnd = this.state.emailReceiptEnd;
            let userSelectionStatus = this.state.campaignOptionSelected;
            let hoursGrid = this.state.days;
            let params = {
                'scheduleId': id,
                'scheduleName': scheduleName,
                'fkProfileId': profileId,
                'portfolioCampaignType': selectedPfCampaign.value,
                'portfolioCampaignEditTypeOldValue': oldPortfolioCampaignType,
                'pfCampaigns': selectedPortfoliosCampaigns.map(item => {
                    return item.value;
                }),
                'removeCampaigns': (removeCampaigns) ? removeCampaigns : null,
                'startDate': moment(startDate).format('DD-MM-YYYY'),
                'endDate': (endDate != '') ? moment(endDate).format('DD-MM-YYYY') : '',
                'campaignOptionSelected': userSelectionStatus,
                'ccEmails': ccEmails,
                'emailReceiptStart': emailReceiptStart,
                'emailReceiptEnd': emailReceiptEnd,
                'hoursGridSet': hoursGrid
            }

            if (params != null) {
                storeEditDayPartingForm(params, (data) => {

                        if (data.ajax_status != false) {
                            this.props.getAllSchedulesFromDb();
                            this.props.handleModalClose();
                            this.props.dispatch(ShowSuccessMsg(data.success, "Successfully", true, ""));

                        } else {
                            if (data.timeOverLap != false) {
                                this.props.dispatch(ShowFailureMsg("", "", true, "", list(data.error)))
                            } else {
                                this.setState({
                                    modalTitle: 'Day Parting Already Schedule',
                                    maxWidth: 'md',
                                    modalBody: <ActivatedSchedules
                                        activatedSchedules={data.error}
                                    />,
                                    openModal: true
                                })
                            }
                        }
                    }
                )
            }


        }
    }

    handleOnEndDateRangeClick = (e) => {
        this.setState({
            showDRPEnd: true
        })
    }
    handleOnStartDateRangeClick = (e) => {
        this.setState({
            showDRP: true
        })
    }

    handleSingleDateChange = (date) => {
        this.setState({
            startDateDP: moment(date).format('MM-DD-YYYY'),
//            endDateDP: moment(date).format('MM-DD-YYYY'),
            showDRP: false
        }, () => {
            this.resetErrors('errorStartDate')
        })
    }

    handleSingleEndDateChange = (date) => {
        this.setState({
            endDateDP: moment(date).format('MM-DD-YYYY'),
            showDRPEnd: false
        }, () => {
            this.resetErrors('errorStartDate')
        })
    }
    helperCloseDRP = (event) => {
        this.setState({
            showDRP: false
        })
    }

    helperCloseEndDRP = (event) => {
        this.setState({
            showDRPEnd: false
        })
    }
    resetErrors = (key) => {
        let {errors} = this.state;
        errors[key] = ""
        this.setState({
            ...errors
        })
    }

    checkAllHoursBoxes = (name, isChecked) => {

        if (name == 'Days') {
            let tempDays = this.state.days;
            tempDays["Days"]["isChecked"] = isChecked
            Object.keys(tempDays).forEach((key, idx) => {
                if (idx != 0) {
                    tempDays[key]["hours"] = {};
                    tempDays[key]["isChecked"] = isChecked;
                    [...Array(24)].forEach((hour, index) => {
                        tempDays[key]["hours"][index] = isChecked;
                    })
                }
            });
            this.setState({days: tempDays}, () => {
                let selectedBoxes = $(`.dpGridView div.bg-yellow-600`).toArray();
                this.editDpRef.current.setSelectedTargets(selectedBoxes)
            })
        } else {
            const hours = {};
            [...Array(24)].forEach((hour, index) => {
                hours[index] = isChecked;
            })

            this.setState(({
                days: {
                    ...this.state.days,
                    [name]: {
                        isChecked,
                        hours
                    }

                }
            }), () => {
                let selectedBoxes = $(`.dpGridView div.bg-yellow-600`).toArray();
                this.editDpRef.current.setSelectedTargets(selectedBoxes)

                // This step is to find if any day is uncheck then we have to un check day all checkbox
                let newDays = {...this.state.days};
                let checkAllDays = Object.keys(newDays).map((item1, index) => {
                    if (index !== 0) {
                        return newDays[item1].isChecked
                    }
                }).filter(filter1 => {
                    return filter1 !== undefined;
                })

                let checkIfBoxAnyUncheck = checkAllDays.some((val) => val === false);

                this.setState({
                    days: {
                        ...this.state.days,
                        "Days" : {
                            "isChecked": !checkIfBoxAnyUncheck
                        }
                    }
                })
            })
        }

    }

    render() {
        const {classes} = this.props;
        return (
            <MuiPickersUtilsProvider utils={MomentUtils}>
                <div className="bg-gray-100 dayPartingModule pl-5 pr-10 rounded-lg">
                    <form>
                        <Grid container spacing={3}>
                            <Grid className="dayPartingGridElement" item xs={12} sm={6} md={6} lg={6}>
                                <label className="inline-block mb-2 ml-2 text-sm">
                                    Schedule Name <span className="required-asterisk">*</span>
                                </label>

                                <TextFieldInput
                                    placeholder="Schedule Name"
                                    id="dr"
                                    type="text"
                                    className="dayPartingTextField rounded-full bg-white"
                                    name="scheduleName"
                                    value={this.state.scheduleName}
                                    onChange={this.onChangeText}
                                    fullWidth={true}
                                />

                                <div className="error pl-2">{this.state.errors.scheduleN}</div>

                            </Grid>
                            <Grid className="dayPartingGridElement" item xs={12} sm={6} md={6} lg={6}>
                                <label className="text-sm  ml-2">
                                    Portfolio/Campaign <span className="required-asterisk">*</span>
                                </label>
                                <SingleSelect
                                    placeholder="Portfolio/Campaign"
                                    id="sc"
                                    name="selectedCampaign"
                                    value={this.state.selectedPfCampaign}
                                    onChangeHandler={this.onPfCampaignChange}
                                    Options={this.state.selectedPfCampaignOption}
                                    fullWidth={true}
                                    isClearable={false}
                                />
                                <div className="error pl-2">{this.state.errors.selectedPfCp}</div>
                            </Grid>
                            <Grid className="dayPartingGridElement" item xs={12} sm={6} md={6} lg={6}>
                                <label className="text-sm  ml-2">
                                    Portfolios/Campaigns <span className="required-asterisk">*</span>
                                </label>
                                <MultiSelect
                                    placeholder="Portfolios/Campaigns"
                                    id="pt"
                                    name="text"
                                    value={this.state.selectedPortfoliosCampaigns}
                                    onChangeHandler={this.onPortfoliosCampaignsChange}
                                    Options={this.state.PortfoliosCampaignsOptions}
                                />
                                <div className="error pl-2">{this.state.errors.selectedPfsCps}</div>
                            </Grid>
                            <Grid item xs={12} sm={6} md={6} lg={6}>
                                <label className="inline-block mb-2 ml-2 text-sm">
                                    Start Date <span className="required-asterisk">*</span>
                                </label>
                                <div onClick={this.state.isStartDateDisable ? this.handleOnStartDateRangeClick : ''}>
                                    <TextFieldInput
                                        placeholder="Start Date"
                                        type="text"
                                        className="dayPartingTextField rounded-full bg-white"
                                        value={this.state.startDateDP}
                                        fullWidth={true}
                                        disable={this.state.isStartDateDisable}
                                    />
                                    <div className="error pl-2">{this.state.errorStartDate}</div>
                                </div>
                                <div className={`absolute z-50 ${classes.datepickerClass}`}>
                                    {
                                        this.state.showDRP ?
                                            <CustomDatePicker
                                                helperCloseDRP={this.helperCloseDRP}
                                                setSingleDate={this.handleSingleDateChange}
                                                startDate={new Date()}
                                                direction="vertical"
                                                isEndDate={false}
                                            />
                                            : null
                                    }
                                </div>
                            </Grid>
                            <Grid item xs={12} sm={4} md={4} lg={4}>
                                <label className="inline-block mb-2 ml-2 text-sm">
                                    End Date
                                </label>
                                <div onClick={this.handleOnEndDateRangeClick}>
                                    <TextFieldInput
                                        placeholder="End Date"
                                        type="text"
                                        value={this.state.endDateDP}
                                        fullWidth={true}
                                        className="dayPartingTextField rounded-full bg-white"
                                    />
                                    <div className="error pl-2">{this.state.errorEndDate}</div>
                                </div>
                                <div className={`absolute z-50 ${classes.datepickerClass}`}>
                                    {
                                        this.state.showDRPEnd ?
                                            <CustomDatePicker
                                                helperCloseDRP={this.helperCloseEndDRP}
                                                setSingleDate={this.handleSingleEndDateChange}
                                                endDate={this.state.endDateDP}
                                                direction="vertical"
                                                isEndDate={true}
                                            />
                                            : null
                                    }
                                </div>
                            </Grid>
                            <Grid item xs={12} sm={12} md={12} lg={12}>
                                <div className="p-1 mt-3 items-center flex justify-start editDpGridView">
                                    <div className="flex flex-col w-1/5">
                                        {
                                            this.state.days && Object.keys(this.state.days).map((item, idx) =>
                                                <DayRows
                                                    checkAll={this.checkAllHoursBoxes}
                                                    check={item && true}
                                                    day={item}
                                                    isChecked={this.state.days[item] && this.state.days[item].isChecked}
                                                    key={`d${idx}`
                                                    }/>
                                            )}
                                    </div>
                                    <div className="editGridSelectionDp flex flex-col w-10/12 border">
                                        <div className="flex flex-row">
                                            {
                                                hours.map((item, index) => (
                                                    <HeaderRow
                                                        header={item}
                                                        key={`h${index}`}
                                                    />
                                                ))}
                                        </div>
                                        <Selecto
                                            ref={this.editDpRef}
                                            // The container to add a selection element
                                            container={document.querySelector(".editDpGridView")}
                                            // The area to drag selection element (default: container)
                                            dragContainer={document.querySelector(".editDpGridView")}
                                            selectableTargets={[".editDpGridView .editHourRows"]}
                                            keyContainer={document.querySelector(".editDpGridView")}
                                            continueSelect={true}
                                            hitRate={5}
                                            selectByClick={true}
                                            selectFromInside={true}
                                            toggleContinueSelect={["shift"]}
                                            //ratio={0}
                                            onSelectEnd={e => {
                                                let newDays = {...this.state.days};

                                                Object.keys(newDays).forEach((item, idx) => {
                                                    if (idx !== 0){
                                                        console.log('item', item)
                                                        const selectedItemForAParent = $(`div[parenteditname='${item}']`)
                                                        const currentSelected = {};
                                                        $.each(selectedItemForAParent, function (indexInArray, element) {
                                                            if ($(element).hasClass("bg-yellow-600")) {
                                                                currentSelected[$(element).attr("itemindex")] = true;
                                                            } else {
                                                                currentSelected[$(element).attr("itemindex")] = false;
                                                            }
                                                        });
                                                        console.log('selected ', Object.keys(currentSelected).filter(item => currentSelected[item]).length)
                                                        console.log('selectedItemForAParent ', selectedItemForAParent.length)
                                                        newDays[item] = {
                                                            isChecked: selectedItemForAParent.length === Object.keys(currentSelected).filter(item => currentSelected[item]).length,
                                                            hours: {
                                                                ...currentSelected
                                                            }
                                                        }
                                                    }
                                                })

                                                let checkAllDays = Object.keys(newDays).map((item1, index) => {
                                                    if (index !== 0) {
                                                        return newDays[item1].isChecked
                                                    }
                                                }).filter(filter1 => {
                                                    return filter1 !== undefined;
                                                })

                                                let checkIfAnyUncheck = checkAllDays.some((val) => val === false)

                                                newDays["Days"] = {
                                                    "isChecked" : !checkIfAnyUncheck
                                                }

                                                this.setState((prev) => ({
                                                    days: {
                                                        ...newDays
                                                    }
                                                }))
                                            }}
                                            onSelect={e => {
                                                e.added.forEach(el => {
                                                    el.classList.add("bg-yellow-600");
                                                });

                                                e.removed.forEach(el => {
                                                    el.classList.remove("bg-yellow-600");
                                                });
                                            }}
                                        />
                                        {
                                            this.state.days && Object.keys(this.state.days).map((item, idx) => {

                                                return (
                                                    this.state.days[item] && this.state.days[item].hours &&
                                                    <div className="flex flex-row" key={`dp${idx}`}>
                                                        {
                                                            Object.keys(this.state.days[item].hours).map((value, index) => (
                                                                <EditHoursRows
                                                                    key={index}
                                                                    itemIndex={value}
                                                                    isChecked={this.state.days[item].hours[value]}
                                                                    parentName={item}
                                                                />
                                                            ))
                                                        }
                                                    </div>
                                                )


                                            })}
                                    </div>
                                </div>
                            </Grid>
                            <Grid className="timePickerDayPartingElement" item xs={12} sm={12} md={12} lg={12}>
                                <label className="text-sm  ml-2 inline-block mb-2">
                                    Cc Email
                                </label>
                                <CcEmail items={this.state.ccEmail} getUpdatedItems={this.getUpdatedItems}>

                                </CcEmail>
                            </Grid>
                            <Grid container xs={12}>
                                <Grid item xs={6} sm={6} md={6} lg={6}
                                      className="flex items-center dayPartingCheckBoxes">
                                    <label className="text-sm  ml-2 mr-10">
                                        Email Receipts
                                    </label>
                                    <CheckBox
                                        label="Start"
                                        checked={this.state.emailReceiptStart}
                                        onChange={this.onCheckBoxesChangeHandler}
                                        name="emailReceiptStart"
                                    />
                                    <CheckBox
                                        label="End"
                                        checked={this.state.emailReceiptEnd}
                                        onChange={this.onCheckBoxesChangeHandler}
                                        name="emailReceiptEnd"
                                    />
                                </Grid>
                            </Grid>
                            <Grid item xs={12} md={12} lg={12} className="text-center">
                                <Grid container justify="center" spacing={2}>
                                    <Grid item xs={2} md={2} lg={2}>
                                        <TextButton
                                            BtnLabel={" Cancel "}
                                            color="primary"
                                            onClick={this.props.handleModalClose}/>

                                    </Grid>
                                    <Grid item xs={2} md={2} lg={2}>
                                        <PrimaryButton
                                            btnlabel={"  Submit  "}
                                            variant={"contained"}
                                            onClick={this.formSubmissionDayParting}/>

                                    </Grid>
                                </Grid>
                            </Grid>

                        </Grid>
                    </form>
                </div>
                <DayPartingModal
                    openModal={this.state.openModal}
                    modalTitle={this.state.modalTitle}
                    id={this.state.id}
                    handleClose={this.handleModalClose}
                    modalBody={this.state.modalBody}
                    maxWidth={this.state.maxWidth}
                    cancelEvent={this.handleModalClose}
                    fullWidth={true}
                />
            </MuiPickersUtilsProvider>
        );
    }
}

export default withStyles(styles)(connect(null)(EditFormDayParting));