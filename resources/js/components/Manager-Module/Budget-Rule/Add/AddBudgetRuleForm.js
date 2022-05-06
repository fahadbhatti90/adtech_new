import React, {Component} from "react";
import {connect} from "react-redux";
import {Grid} from "@material-ui/core";
import TextFieldInput from "../../../../general-components/Textfield";
import SingleSelect from "../../../../general-components/Select";
import MultiSelect from "../../../../general-components/MultiSelect";
import moment from "moment";
import CustomDatePicker from "../../Day-Parting/DatePicker/CustomDatePicker";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import CheckBox from "../../../../general-components/CheckBox";
import FormControl from "@material-ui/core/FormControl";
import RadioGroup from "@material-ui/core/RadioGroup";
import FormControlLabel from "@material-ui/core/FormControlLabel";
import {withStyles} from "@material-ui/core/styles";
import {primaryColor} from "../../../../app-resources/theme-overrides/global";
import Radio from "@material-ui/core/Radio";
import {getProfiles, getCampaigns, addBudgetRule, getRecommendedEvents} from "../apiCalls";
import {ShowFailureMsg} from "../../../../general-components/failureDailog/actions";
import * as Yup from "yup";
import {objectRequiredValidationHelper, stringRequiredValidationHelper} from "../../../../helper/yupHelpers";
import {breakProfileId} from "../../../../helper/helper";
import {ShowSuccessMsg} from "../../../../general-components/successDailog/actions";
import '../budgetRule.scss';
import LinearProgress from "@material-ui/core/LinearProgress";
import {initialStates} from "../initialStates";
import ModalDialog from "../../../../general-components/ModalDialog";
import {RecommendedEventPopUp} from "../RecommendedEventPopUp";

const list = (errorsData) => {
    return (
        <>
            {errorsData.map(item => (
                <li className="list-none"> {item} </li>
            ))}
        </>
    )
};
const ThemeRadio = withStyles({
    root: {
        color: primaryColor,
        '&$checked': {
            color: primaryColor,
        },
    },
    checked: {},
})((props) => <Radio color="default" {...props} />);

const scheduleBase = {
    value: 'SCHEDULE'
};

const performanceBase = {
    value: 'PERFORMANCE'
};

const adTypeOptions = [
    {label: "Product", value: 'sponsoredProducts', className: 'awesome-class'},
    /*    {label: "Brand", value: 'sponsoredBrands', className: 'custom-class', disabled: true},*/
];

const ruleTypeOptions = [
    {label: "PERFORMANCE", value: 'PERFORMANCE', className: 'custom-class'},
    {label: "SCHEDULE", value: 'SCHEDULE', className: 'awesome-class'}
];

const metricOptions = [
    {label: "ACOS", value: 'ACOS', className: 'awesome-class'},
    {label: "CTR", value: 'CTR', className: 'awesome-class'},
    {label: "CVR", value: 'CVR', className: 'awesome-class'},
    {label: "ROAS", value: 'ROAS', className: 'awesome-class'},
]

const conditionOptions = [
    {label: "GREATER THAN", value: 'GREATER_THAN', className: 'custom-class'},
    {label: "LESS THAN", value: 'LESS_THAN', className: 'awesome-class'},
    // {label: "EQUAL TO", value: 'EQUAL_TO', className: 'awesome-class'},
    {label: "GREATER THAN OR EQUAL TO", value: 'GREATER_THAN_OR_EQUAL_TO', className: 'custom-class'},
    {label: "LESS THAN OR EQUAL TO", value: 'LESS_THAN_OR_EQUAL_TO', className: 'awesome-class'},
];

const recurrence = {
    'WEEKLY': 'WEEKLY',
    'DAILY': 'DAILY',
}

class AddBudgetRuleForm extends Component {

    constructor(props) {
        super(props);
        this.state = {
            ...initialStates,
            profileOptions: [],
            isProcessing: false,
            errors: {
                ruleNameE: "",
                selectedProfileE: "",
                selectedAdTypeE: "",
                selectedCampaignsE: "",
                selectedRuleTypeE: "",
                selectedMetricE: "",
                selectedConditionE: "",
                thresholdValueE: "",
                raiseBudgetE: "",
            }
        }
    }

    componentDidMount() {
        this.setState({isProcessing: true});
        let params = {
            "id": "",
        }
        getProfiles(params, ({profileOptions, CampaignsOptions}) => {
            this.setState({
                profileOptions
            }, () => {
                this.setState({isProcessing: false});
            })

        })
    }

    onTextChange = (e) => {
        const name = e.target.name;
        this.setState({
            [name]: e.target.value
        }, () => {
            this.resetErrors(name + 'E')
        })
    }
    onChangeHandler = (value, element) => {
        const name = element.name;
        this.setState({
            [name]: value
        }, () => {

            this.resetErrors(name + 'E')
        })
    }

    onChangeRuleType = (value, element) => {

        let initialStatesLatest = initialStates;
        this.setState({
            ...initialStatesLatest
        }, () => {
            this.setState({
                "selectedRuleType" : value
            })
            if (this.state.selectedProfile && this.state.selectedAdType) {
                this.getPortfolioCampaigns();
            }
            this.resetErrors(name + 'E')
        })
    }

    onDropDownChangeHandler = (value, element) => {
        const name = element.name;
        this.setState({
            [name]: value
        }, () => {
            this.resetErrors(name + 'E')
            if (this.state.selectedProfile && this.state.selectedAdType) {
                this.getPortfolioCampaigns();
            }
        })
    }

    onProfileChangeHandler = (value, element) => {
        const name = element.name;
        this.setState({
            [name]: value
        }, () => {
            this.resetErrors(name + 'E')
            if (this.state.selectedProfile && this.state.selectedAdType) {
                let initialStatesLatest = initialStates;
                delete initialStatesLatest.ruleName;
                delete initialStatesLatest.selectedAdType;
                delete initialStatesLatest.selectedProfile;
                //delete initialStatesLatest.CampaignsOptions;
                //console.log('initial ', ruleName, ' rule type', ruleName)
                this.setState({
                    ...initialStatesLatest
                }, () => {

                })
                this.getPortfolioCampaigns();
            }
        })
    }

    getPortfolioCampaigns = () => {

        let profile = this.state.selectedProfile.value;
        let adType = this.state.selectedAdType.value;

        getCampaigns(profile, adType, (CampaignsOptions) => {

            if (CampaignsOptions.length > 0) {
                //success
                this.setState({
                    CampaignsOptions
                })
            } else {
                this.props.dispatch(ShowFailureMsg("No Data Found ", "", true, ""));
            }
        }, (err) => {
            //error
            // this.props.dispatch(showSnackBar());
        });
    }

    onInputChangeHandler = (e) => {
        const name = e.target.name;
        this.setState({
            [name]: e.target.value
        }, () => {
            this.resetErrors(name + 'E')
        })
    }

    onCheckBoxesChangeHandler = (e) => {
        this.setState({
            [e.target.name]: e.target.checked
        }, () => {

        })
    }

    handleSingleDateChange = (date) => {
        this.setState({
            startDateDP: moment(date).format('MM-DD-YYYY'),
            showDRP: false
        }, () => {
            this.resetErrors('startDateDPE')
        })
    }
    handleSingleEndDateChange = (date) => {
        this.setState({
            endDateDP: moment(date).format('MM-DD-YYYY'),
            showDRPEnd: false
        }, () => {

        })
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

    helperCloseDRP = () => {
        this.setState({
            showDRP: false
        })
    }

    helperCloseEndDRP = () => {
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

    onCampaignsChange = (value) => {
        if (value && value.length == 0) {
            value = null
        }
        let tempPc = this.state.selectedCampaigns;
        this.setState({
            selectedCampaigns: value
        }, () => {

            if (tempPc && value && tempPc.length <= value.length) {
                $(".autoScrl .select__value-container").animate({
                    scrollTop: $('.autoScrl .select__value-container').get(0).scrollHeight
                });
            }
            this.resetErrors("selectedCampaignsE");
        })
    }

    checkRecommendationEvents = (event) => {
        event.preventDefault();
        let validationSchema = Yup.object().shape({
            selectedCampaignsE: objectRequiredValidationHelper("campaign"),
            selectedProfileE: objectRequiredValidationHelper("child profile"),
            selectedAdTypeE: objectRequiredValidationHelper("ad type"),
        });


        let dataToValidateObject = {
            selectedCampaignsE: this.state.selectedCampaigns,
            selectedProfileE: this.state.selectedProfile,
            selectedAdTypeE: this.state.selectedAdType,
        };

        let validationFormData = htk.validateAllFields(validationSchema, dataToValidateObject);

        if (Object.size(validationFormData) > 0) {
            const {errors} = this.state;
            $.each(validationFormData, function (indexInArray, valueOfElement) {
                errors[indexInArray] = valueOfElement;
            });
            this.setState(({
                ...errors
            }));
        } else {
            this.setState({isProcessing: true})
            let profile = this.state.selectedProfile;
            let selectedAdType = this.state.selectedAdType;
            let selectedCampaigns = this.state.selectedCampaigns;

            let params = {
                'fkProfileId': breakProfileId(profile.value),
                'profileId': profile.value.split("|")[1],
                'configId': +profile.value.split("|")[2],
                'selectedAdType': selectedAdType.value,
                'selectedCampaigns': selectedCampaigns.map(item => {
                    return item.title;
                }),
            }

            getRecommendedEvents(params, (res) => {
                if (res.data.status) {
                    let events = res.data.events;
                    this.setState({
                        recommendedEvents: events,
                        recommendedData: events,
                        totalRows: events.length,
                        recommendedPopUp: true
                    })
                } else {
                    this.props.dispatch(ShowFailureMsg("No events found.. please try again", "", true, ""))
                }
                this.setState({isProcessing: false})
                console.log('data', res.data)
            })
        }
    }

    scheduleBaseValidation = () => {
        let validationSchema = Yup.object().shape({
            ruleNameE: stringRequiredValidationHelper("rule name"),
            selectedProfileE: objectRequiredValidationHelper("child profile"),
            selectedAdTypeE: objectRequiredValidationHelper("ad type"),
            selectedCampaignsE: objectRequiredValidationHelper("campaign"),
            selectedRuleTypeE: objectRequiredValidationHelper("rule type"),
            startDateDPE: stringRequiredValidationHelper("start Date"),
            raiseBudgetE: stringRequiredValidationHelper("raise budget")
                .matches(
                    /^\d+(\.\d{1,2})?$/,
                    "Raise budget must not be greater than 100.00"
                ).max(6, 'Raise budget must not be greater than 100.00'),
        });

        let dataToValidateObject = {
            ruleNameE: this.state.ruleName,
            selectedProfileE: this.state.selectedProfile,
            selectedAdTypeE: this.state.selectedAdType,
            selectedCampaignsE: this.state.selectedCampaigns,
            selectedRuleTypeE: this.state.selectedRuleType,
            startDateDPE: this.state.startDateDP,
            raiseBudgetE: this.state.raiseBudget,
        };

        return htk.validateAllFields(validationSchema, dataToValidateObject);
    }
    performanceValidation = () => {
        let validationSchema = Yup.object().shape({
            ruleNameE: stringRequiredValidationHelper("rule name"),
            selectedProfileE: objectRequiredValidationHelper("child profile"),
            selectedAdTypeE: objectRequiredValidationHelper("ad type"),
            selectedCampaignsE: objectRequiredValidationHelper("campaign"),
            selectedRuleTypeE: objectRequiredValidationHelper("rule type"),
            selectedMetricE: objectRequiredValidationHelper("metric"),
            selectedConditionE: objectRequiredValidationHelper("condition"),
            startDateDPE: stringRequiredValidationHelper("start Date"),
            thresholdValueE: stringRequiredValidationHelper("threshold value")
                .matches(
                    /^\d+(\.\d{1,2})?$/,
                    "Threshold must not be greater than 100.00 "
                ).max(6, 'Threshold must not be greater than 100.00'),
            raiseBudgetE: stringRequiredValidationHelper("raise budget")
                .matches(
                    /^\d+(\.\d{1,2})?$/,
                    "Raise budget must not be greater than 100.00"
                ).max(6, 'Raise budget must not be greater than 100.00'),
        });

        let dataToValidateObject = {
            ruleNameE: this.state.ruleName,
            selectedProfileE: this.state.selectedProfile,
            selectedAdTypeE: this.state.selectedAdType,
            selectedCampaignsE: this.state.selectedCampaigns,
            selectedRuleTypeE: this.state.selectedRuleType,
            selectedMetricE: this.state.selectedMetric,
            selectedConditionE: this.state.selectedCondition,
            startDateDPE: this.state.startDateDP,
            thresholdValueE: this.state.thresholdValue,
            raiseBudgetE: this.state.raiseBudget,
        };

        let thresholdValue = this.state.thresholdValue;
        if (parseFloat(thresholdValue) > 100.00) {

            this.setState(prevState => ({
                errors: {
                    ...prevState.errors,
                    thresholdValueE: 'Threshold must not be greater than 100.00'
                }
            }))
        }

        return htk.validateAllFields(validationSchema, dataToValidateObject);
    }
    onSubmit = (event) => {
        event.preventDefault();
        var isDateLess = false;
        let startDate = this.state.startDateDP;
        let endDate = this.state.endDateDP;
        const {mon, tue, wed, thu, fri, sat, sun} = this.state;


        if (endDate != '' && (new Date(startDate) > new Date(endDate)) || (new Date(endDate) < new Date(startDate))) {
            const errors = this.state.errors;
            this.setState({
                errorEndDateDPE: "End date should be greater or equal to start date"
            })
            isDateLess = true
        } else {
            this.setState({
                errorEndDateDPE: ""
            })
        }

        let raiseBudget = this.state.raiseBudget;

        if (parseFloat(raiseBudget) > 100.00) {

            this.setState(prevState => ({
                errors: {
                    ...prevState.errors,
                    raiseBudgetE: 'Raise budget must not be greater than 100.00'
                }
            }))
        }

        let selectedRuleType = this.state.selectedRuleType;

        let validationFormData = '';
        if (selectedRuleType.value == scheduleBase.value) {
            validationFormData = this.scheduleBaseValidation()
        } else {
            validationFormData = this.performanceValidation()
        }

        if (Object.size(validationFormData) > 0 || isDateLess) {
            const {errors} = this.state;
            $.each(validationFormData, function (indexInArray, valueOfElement) {
                errors[indexInArray] = valueOfElement;
            });
            this.setState(({
                ...errors
            }));
        } else {

            this.setState({isProcessing: true})

            let ruleName = this.state.ruleName;
            let eventId = this.state.eventId;
            let eventName = this.state.eventName;
            let profile = this.state.selectedProfile;
            let selectedAdType = this.state.selectedAdType;
            let selectedCampaigns = this.state.selectedCampaigns;
            //let selectedRuleType = this.state.selectedRuleType;
            let startDate = this.state.startDateDP;
            let endDate = this.state.endDateDP;
            let recurrence = this.state.dailyWeekly;
            let selectedMetric = this.state.selectedMetric;
            let selectedCondition = this.state.selectedCondition;
            let thresholdValue = this.state.thresholdValue;

            let params = {
                'ruleName': ruleName,
                'eventId': eventId,
                'eventName': eventName,
                'fkProfileId': breakProfileId(profile.value),
                'profileId': profile.value.split("|")[1],
                'configId': +profile.value.split("|")[2],
                'selectedAdType': selectedAdType.value,
                'selectedCampaigns': selectedCampaigns.map(item => {
                    return item.value;
                }),
                'ruleType': selectedRuleType.value,
                'startDate': moment(startDate).format('DD-MM-YYYY'),
                'endDate': (endDate != '') ? moment(endDate).format('DD-MM-YYYY') : '',
                'recurrence': recurrence,
                'metric': selectedMetric && selectedMetric.value ? selectedMetric.value : null,
                'comparisonOperator': selectedCondition && selectedCondition.value ? selectedCondition.value : null,
                'threshold': thresholdValue,
                'raiseBudget': raiseBudget,
                'mon': mon,
                'tue': tue,
                'wed': wed,
                'thu': thu,
                'fri': fri,
                'sat': sat,
                'sun': sun,
            }
            addBudgetRule(params, (data) => {
                this.setState({isProcessing: false});
                if (data.status) {
                    this.props.dispatch(ShowSuccessMsg(data.success, "Successfully", true, "", this.props.updateDataTable));
                    this.setState({
                         ...initialStates
                    })

                } else {

                    this.props.dispatch(ShowFailureMsg("", "", true, "", list(data.error)))
                }
            })

        }

    }

    eventSelected = (row) => {
        console.log('row selected here it is', row)
        this.setState({
            recommendedPopUp: false,
            eventName: row.eventName,
            eventId: row.eventId,
            raiseBudget: row.suggestedBudgetIncreasePercent,
            startDateDP: moment(row.startDate).format('MM-DD-YYYY'),
            endDateDP: moment(row.endDate).format('MM-DD-YYYY'),
            isEventSelected: false
        })
    }

    render() {
        const classes = this.props;
        return (
            <>
                <div className="graphLoader bg-white absolute h-full overflow-hidden w-full top-0 left-0 z-10"
                     style={this.state.isProcessing ? {display: "block"} : {display: "none"}}>
                    <LinearProgress/>
                    <div
                        className="absolute flex font-bold font-mono h-full items-center justify-center overflow-hidden text-1rem text-sm w-full z-10">
                        Processing...
                    </div>
                </div>
                <div className="p-5 rounded-lg">
                    <form>
                        <Grid container spacing={1}>
                            <Grid container spacing={1} alignItems="flex-start" className="px-2 py-0 rounded-lg">
                                <Grid item xs={12} sm={6} md={6} lg={4}>
                                    <label className="text-xs font-normal ml-2">
                                        Rule Name <span className="font-black text-red-500 text-sm">*</span>
                                    </label>
                                    <div className="ThemeInput mr-5">
                                        <TextFieldInput
                                            placeholder="Enter Rule"
                                            type="text"
                                            name="ruleName"
                                            value={this.state.ruleName}
                                            onChange={this.onTextChange}
                                            fullWidth={true}
                                            customclassname="mr-5 ThemeSelect"
                                        />
                                        <div className="error pl-3">{this.state.errors.ruleNameE}</div>
                                    </div>
                                </Grid>
                                <Grid item xs={12} sm={6} md={6} lg={4}>
                                    <label className="text-xs font-normal ml-2">
                                        Rule Type <span className="font-black text-red-500 text-sm">*</span>
                                    </label>
                                    <SingleSelect
                                        placeholder="Select Rule Type"
                                        value={this.state.selectedRuleType}
                                        name="selectedRuleType"
                                        onChangeHandler={this.onChangeRuleType}
                                        fullWidth={true}
                                        Options={ruleTypeOptions}
                                        customclassname="mr-5 ThemeSelect"
                                        isOptionDisabled={(option) => option.disabled}
                                        isClearable={false}
                                    />
                                    <div className="error pl-3">{this.state.errors.selectedRuleTypeE}</div>
                                </Grid>
                                <Grid item xs={12} sm={6} md={6} lg={4}>
                                    <label className="text-xs font-normal ml-2">
                                        Select Child Brand <span className="font-black text-red-500 text-sm">*</span>
                                    </label>
                                    <SingleSelect
                                        placeholder="Child Brand"
                                        value={this.state.selectedProfile}
                                        name="selectedProfile"
                                        onChangeHandler={this.onProfileChangeHandler}
                                        fullWidth={true}
                                        Options={this.state.profileOptions}
                                        customclassname="mr-5 ThemeSelect"
                                        isClearable={false}
                                    />
                                    <div className="error pl-3">{this.state.errors.selectedProfileE}</div>
                                </Grid>
                                <Grid item xs={12} sm={6} md={6} lg={4}>
                                    <label className="text-xs font-normal ml-2">
                                        AdType <span className="font-black text-red-500 text-sm">*</span>
                                    </label>
                                    <SingleSelect
                                        placeholder="Select AdType"
                                        value={this.state.selectedAdType}
                                        onChangeHandler={this.onDropDownChangeHandler}
                                        name="selectedAdType"
                                        fullWidth={true}
                                        Options={adTypeOptions}
                                        customclassname="mr-5 ThemeSelect"
                                        isOptionDisabled={(option) => option.disabled}
                                        isClearable={false}
                                    />
                                    <div className="error pl-3">{this.state.errors.selectedAdTypeE}</div>
                                </Grid>
                                <Grid item xs={12} sm={6} md={6} lg={4} className="autoScrl">
                                    <label className="text-sm  ml-2">
                                        Campaigns <span className="required-asterisk">
                                        {
                                            this.state.selectedRuleType != null && this.state.selectedRuleType.value == scheduleBase.value ?
                                                <PrimaryButton
                                                    customclasses="recommendedBtn"
                                                    btnlabel={"Recommended Events"}
                                                    variant={"contained"}
                                                    onClick={this.checkRecommendationEvents}
                                                /> : null
                                        }
                                        *</span>
                                    </label>
                                    <MultiSelect
                                        placeholder="Campaigns"
                                        name="text"
                                        value={this.state.selectedCampaigns}
                                        onChangeHandler={this.onCampaignsChange}
                                        Options={this.state.CampaignsOptions}
                                        customclassname="mr-5 ThemeSelect"
                                    />
                                    <div className="error pl-2">{this.state.errors.selectedCampaignsE}</div>
                                </Grid>

                                <Grid item xs={12} sm={6} md={6} lg={4}>
                                    <label className="text-xs font-normal ml-2">
                                        Start Date <span className="required-asterisk">*</span>
                                    </label>
                                    <div onClick={this.state.isEventSelected ? this.handleOnStartDateRangeClick : ''}>
                                        <div className="ThemeInput mr-5">
                                            <TextFieldInput
                                                placeholder="Start Date"
                                                type="text"
                                                value={this.state.startDateDP}
                                                fullWidth={true}
                                                customclassname="mr-5 ThemeSelect"
                                            />
                                        </div>
                                        <div className="error pl-2">{this.state.errorStartDateDPE}</div>
                                    </div>
                                    <div className={`absolute z-50 ${classes.datepickerClass}`}>
                                        {
                                            this.state.showDRP ?
                                                <CustomDatePicker
                                                    helperCloseDRP={this.helperCloseDRP}
                                                    setSingleDate={this.handleSingleDateChange}
                                                    startDate={new Date()}
                                                    direction="vertical"
                                                    disable={this.state.isEventSelected.toString()}
                                                    isEndDate={false}
                                                />
                                                : null
                                        }
                                    </div>
                                </Grid>
                                <Grid item xs={12} sm={6} md={6} lg={4}>
                                    <label className="text-xs font-normal ml-2">
                                        End Date
                                    </label>
                                    <div onClick={this.state.isEventSelected ? this.handleOnEndDateRangeClick : ''}>
                                        <div className="ThemeInput mr-5">
                                            <TextFieldInput
                                                placeholder="End Date"
                                                type="text"
                                                value={this.state.endDateDP}
                                                fullWidth={true}
                                                disable={this.state.isEventSelected.toString()}
                                                customclassname="mr-5 ThemeSelect"
                                            />
                                        </div>
                                        <div className="error pl-2">{this.state.errorEndDateDPE}</div>
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
                                <Grid item xs={12} sm={3} md={3} lg={3} className="flex items-center">
                                    <FormControl component="div">
                                        <RadioGroup aria-label="dailyWeekly" className="dailyWeekly" name="dailyWeekly"
                                                    value={this.state.dailyWeekly} onChange={this.onInputChangeHandler}>
                                            <FormControlLabel value="DAILY" control={<ThemeRadio size="small"/>}
                                                              label="Daily" labelPlacement="start"/>
                                            {this.state.isEventSelected ?
                                                <FormControlLabel value="WEEKLY" control={<ThemeRadio size="small"/>}
                                                                  label="Weekly" labelPlacement="start"/>
                                                : null
                                            }

                                        </RadioGroup>
                                    </FormControl>
                                </Grid>
                                {this.state.dailyWeekly == recurrence.WEEKLY ?
                                    <Grid item xs={12} sm={5} md={5} lg={5}
                                          className="flex items-center daysCheckBoxGrid">
                                        <CheckBox
                                            label="Mon"
                                            checked={this.state.mon}
                                            onChange={this.onCheckBoxesChangeHandler}
                                            name="mon"
                                        />
                                        <CheckBox
                                            label="Tue"
                                            checked={this.state.tue}
                                            onChange={this.onCheckBoxesChangeHandler}
                                            name="tue"
                                        />
                                        <CheckBox
                                            label="Wed"
                                            checked={this.state.wed}
                                            onChange={this.onCheckBoxesChangeHandler}
                                            name="wed"
                                        />
                                        <CheckBox
                                            label="Thu"
                                            checked={this.state.thu}
                                            onChange={this.onCheckBoxesChangeHandler}
                                            name="thu"
                                        />
                                        <CheckBox
                                            label="Fri"
                                            checked={this.state.fri}
                                            onChange={this.onCheckBoxesChangeHandler}
                                            name="fri"
                                        />
                                        <CheckBox
                                            label="Sat"
                                            checked={this.state.sat}
                                            onChange={this.onCheckBoxesChangeHandler}
                                            name="sat"
                                        />
                                        <CheckBox
                                            label="Sun"
                                            checked={this.state.sun}
                                            onChange={this.onCheckBoxesChangeHandler}
                                            name="sun"
                                        />
                                    </Grid> : !this.state.isEventSelected ?
                                        <>
                                            <Grid item xs={12} sm={1} md={1} lg={1}>
                                            </Grid>
                                            <Grid item xs={12} sm={6} md={6} lg={4}>
                                                <label className="text-xs font-normal ml-2">
                                                    Event Name <span
                                                    className="font-black text-red-500 text-sm">*</span>
                                                </label>
                                                <div className="ThemeInput mr-5">
                                                    <TextFieldInput
                                                        placeholder="Enter Rule"
                                                        type="text"
                                                        name="ruleName"
                                                        value={this.state.eventName}
                                                        disable={true}
                                                        fullWidth={true}
                                                        customclassname="mr-5 ThemeSelect"
                                                    />
                                                </div>
                                            </Grid>
                                        </>

                                        : null
                                }

                            </Grid>
                            {

                                this.state.selectedRuleType == null || this.state.selectedRuleType.value == performanceBase.value ?

                                    <Grid container alignItems="flex-start" className="mt-5 bg-gray-100 p-3 rounded-lg">
                                        <Grid item xs={12} sm={6} md={6} lg={4}>
                                            <div className="flex condition">
                                                <label
                                                    className="flex items-center justify-center w-2/12 text-xs font-normal conditionLabel">if</label>
                                                <SingleSelect
                                                    placeholder="Select"
                                                    closeMenuOnSelect={true}
                                                    name="selectedMetric"
                                                    value={this.state.selectedMetric}
                                                    onChangeHandler={this.onChangeHandler}
                                                    Options={metricOptions}
                                                    customclassname="w-10/12 mr-5 ThemeSelect"
                                                />
                                            </div>
                                            <div className="error pl-3">{this.state.errors.selectedMetricE}</div>
                                        </Grid>
                                        <Grid item xs={12} sm={6} md={6} lg={4}>
                                            <div className="flex condition">
                                                <label
                                                    className="flex items-center justify-center w-2/12 text-xs font-normal conditionLabel"> is</label>
                                                <SingleSelect
                                                    placeholder="Select"
                                                    closeMenuOnSelect={true}
                                                    value={this.state.selectedCondition}
                                                    name="selectedCondition"
                                                    onChangeHandler={this.onChangeHandler}
                                                    Options={conditionOptions}
                                                    customclassname="w-10/12 mr-5 ThemeSelect"
                                                />
                                            </div>
                                            <div className="error pl-3">{this.state.errors.selectedConditionE}</div>
                                        </Grid>
                                        <Grid item xs={12} sm={12} md={6} lg={4}>
                                            <div className="flex mt-0 condition integerNumber">
                                                <label
                                                    className="flex items-center justify-center w-3/12 text-xs font-normal conditionLabel">Enter</label>
                                                <TextFieldInput
                                                    placeholder="Value"
                                                    type="text  "
                                                    name="thresholdValue"
                                                    value={this.state.thresholdValue}
                                                    onChange={this.onTextChange}
                                                    fullWidth={true}
                                                    customclassname="w-6/12 mr-5 ThemeSelect"
                                                />

                                            </div>
                                            <div className="error pl-3">{this.state.errors.thresholdValueE}</div>
                                        </Grid>
                                    </Grid>
                                    : null
                            }


                            <Grid item xs={12} sm={6} md={6} lg={6}>
                                <div className="flex condition integerNumber mt-3">
                                    <label
                                        className="flex items-center justify-center w-6/12 text-xs font-normal conditionLabel">
                                        Then Raise Budget by (%)
                                    </label>
                                    <TextFieldInput
                                        placeholder="Raise Budget by"
                                        type="text"
                                        name="raiseBudget"
                                        value={this.state.raiseBudget}
                                        onChange={this.onTextChange}
                                        fullWidth={true}
                                        customclassname="w-6/12 mr-5 ThemeSelect"
                                    />
                                </div>
                                <div className="error pl-3">{this.state.errors.raiseBudgetE}</div>
                            </Grid>


                        </Grid>
                        <div className="flex float-right items-center justify-around p-4 mt-8">
                            <PrimaryButton
                                btnlabel={"Submit"}
                                variant={"contained"}
                                onClick={this.onSubmit}
                            />
                        </div>
                    </form>
                </div>
                <div>
                    <ModalDialog
                        open={this.state.recommendedPopUp}
                        title={"List Of Recommended Events"}
                        handleClose={() => this.setState({
                            recommendedPopUp: false,
                        })}
                        component={<RecommendedEventPopUp
                            closePopup={
                                () => {
                                    this.setState({
                                        recommendedPopUp: false,
                                    })
                                }
                            }
                            eventSelected={this.eventSelected}
                            recommendedEvents={this.state.recommendedEvents}
                        />}
                        maxWidth={"md"}
                        fullWidth={true}
                        disable={true}
                        cancel={true}
                        modelClass={"RecommendedEvents"}
                    />
                </div>
            </>
        )
    }
}

export default (connect(null)(AddBudgetRuleForm))

