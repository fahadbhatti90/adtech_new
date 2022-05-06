import React, {Component} from 'react';
import {
    MuiPickersUtilsProvider,
} from '@material-ui/pickers';
import MomentUtils from '@date-io/moment';
import SingleSelect from "./../../../../general-components/Select";
import MultiSelect from "./../../../../general-components/MultiSelect";
import CheckBox from "./../../../../general-components/CheckBox";
import CcEmail from "./../../../../general-components/EmailCC/EmailChips";
import {Grid, withStyles} from '@material-ui/core/index';
import TextFieldInput from "./../../../../general-components/Textfield";
import "./../dayparting.scss"
import {getCampaigns, getProfiles, storeDayPartingForm} from "../apiCalls";
import {connect} from "react-redux";
import moment from "moment";
import {breakProfileId} from "./../../../../helper/helper";
import {ShowSuccessMsg} from "./../../../../general-components/successDailog/actions";
import {ShowFailureMsg} from "./../../../../general-components/failureDailog/actions";
import * as Yup from 'yup';
import {styles} from "../styles";
import {hideLoader, showLoader} from "./../../../../general-components/loader/action";
import {objectRequiredValidationHelper, stringRequiredValidationHelper} from "./../../../../helper/yupHelpers";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import CustomDatePicker from "../DatePicker/CustomDatePicker";
import DayRows from "../DatePicker/CustomTime/DayRows";
import HeaderRow from "../DatePicker/CustomTime/HeaderRow";
import HoursRows from "../DatePicker/CustomTime/HoursRows";
import {daysInitialState} from "../daysInitialState";
import RotateLeftIcon from '@material-ui/icons/RotateLeft';
import ActivatedSchedules from "../Datatables/ActivatedSchedules";
import DayPartingModal from "../DayPartingModal";
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

class AddFormDayParting extends Component {
    constructor(props) {
        super(props);
        this.myRef = React.createRef();
        this.state = {
            ...daysInitialState,
            scheduleName: "",
            selectedProfile: null,
            profileOptions: [],
            selectedPfCampaign: null,
            selectedPfCampaignOption: [],
            selectedPortfoliosCampaigns: null,
            PortfoliosCampaignsOptions: [],
            startDateDP: moment(new Date()).format("MM-DD-YYYY"),
            endDateDP: '',
            showDRP: false,
            showDRPEnd: false,
            emailReceiptStart: false,
            emailReceiptEnd: false,
            ccEmail: [],
            items: [],
            reset: false,
            btnText: 'Submit',
            isBtnEnableDisable: false,
            modalTitle: "",
            modalBody: "",
            maxWidth: "xs",
            errorStartDate: "",
            errorEndDate: "",
            errors: {
                scheduleN: "",
                selectedP: "",
                selectedPfCp: "",
                selectedPfsCps: ""
            }
        }
    }

    componentDidMount = () => {

        let tempDays = this.state.days;
        Object.keys(tempDays).forEach((key, idx) => {
            if (idx != 0) {
                tempDays[key]["hours"] = {};
                tempDays[key]["isChecked"] = false;
                [...Array(24)].forEach((hour, index) => {
                    tempDays[key]["hours"][index] = false;
                })
            }
        });
        this.setState({days: tempDays})
        this.props.dispatch(showLoader());
        getProfiles((profileOptions) => {
            //success
            this.setState({
                profileOptions
            })
        }, (err) => {
            //error
            // this.props.dispatch(showSnackBar());
        });

        this.setState({
            selectedPfCampaignOption: [
                {value: "Campaign", label: 'Campaign'},
                {value: "Portfolio", label: 'Portfolio'}
            ]
        })
        this.props.dispatch(hideLoader());
    }
    onPortfoliosCampaignsChange = (value) => {
        if (value && value.length == 0) {
            value = null
        }
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
        })
    }

    // Child Brand On Change Event
    onProfileChange = (value) => {
        //success
        this.setState({
            selectedProfile: value,
        }, () => {
            this.resetErrors("selectedP");
            this.setState({
                selectedPortfoliosCampaigns: null,
                PortfoliosCampaignsOptions: null,
            })
            this.callPortfolioCampaign();

        })
    }
    // Get Campaigns on the basis of profile
    getPortfolioCampaigns = () => {
        let pf = this.state.selectedProfile.value;
        let cpt = this.state.selectedPfCampaign.value;

        getCampaigns(pf, cpt, '', '', (data) => {

            if (data.length > 0) {
                //success
                this.setState({
                    PortfoliosCampaignsOptions: data
                })
            } else {
                this.props.dispatch(ShowFailureMsg("No Data Found ", "", true, ""));
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

    onPfCampaignChange = (value) => {

        this.setState({
            selectedPfCampaign: value,
        }, () => {
            this.resetErrors("selectedPfCp");
            this.setState({
                selectedPortfoliosCampaigns: null,
                PortfoliosCampaignsOptions: [],
            })
            this.callPortfolioCampaign();
        })
    }

    callPortfolioCampaign = () => {
        if (this.state.selectedProfile && this.state.selectedPfCampaign) {
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
            //endTime: moment(startTime, [moment.ISO_8601, 'HH:mm']).add(1, 'minutes')
        }, () => {
            this.resetErrors("stTime")
            this.resetErrors("edTime")
        })
    };

    onEndDateChangeHandler = (endTime) => {
        this.setState({
            endTime: endTime
        }, () => {
            this.resetErrors("edTime")
        })
    };

    getUpdatedItems = (items) => {
        this.setState({
            ccEmail: items
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
            selectedP: objectRequiredValidationHelper("child profile"),
            selectedPfCp: objectRequiredValidationHelper("portfolio/campaign"),
            selectedPfsCps: objectRequiredValidationHelper("portfolios/campaigns"),
            startDateDP: objectRequiredValidationHelper("start Date"),
        });

        let dataToValidateObject = {
            scheduleN: this.state.scheduleName,
            selectedP: this.state.selectedProfile,
            selectedPfCp: this.state.selectedPfCampaign,
            selectedPfsCps: this.state.selectedPortfoliosCampaigns,
            startDateDP: this.state.startDateDP,
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

            this.setState({
                btnText: 'Submitting...',
                isBtnEnableDisable: true
            })
            let scheduleName = this.state.scheduleName;
            let profile = this.state.selectedProfile;
            let selectedPfCampaign = this.state.selectedPfCampaign;
            let selectedPortfoliosCampaigns = this.state.selectedPortfoliosCampaigns;
            let startDate = this.state.startDateDP;
            let endDate = this.state.endDateDP;
            let ccEmails = this.state.ccEmail;
            let emailReceiptStart = this.state.emailReceiptStart;
            let emailReceiptEnd = this.state.emailReceiptEnd;
            let hoursGrid = this.state.days;
            let params = {
                'scheduleName': scheduleName,
                'fkProfileId': breakProfileId(profile.value),
                'portfolioCampaignType': selectedPfCampaign.value,
                'pfCampaigns': selectedPortfoliosCampaigns.map(item => {
                    return item.value;
                }),

                'startDate': moment(startDate).format('DD-MM-YYYY'),
                'endDate': (endDate != '') ? moment(endDate).format('DD-MM-YYYY') : '',
                'ccEmails': ccEmails,
                'emailReceiptStart': emailReceiptStart,
                'emailReceiptEnd': emailReceiptEnd,
                'hoursGridSet': hoursGrid
            }

            if (params != null) {
                storeDayPartingForm(params, (data) => {
                        this.setState({
                            btnText: 'Submit',
                            isBtnEnableDisable: false
                        })
                        if (data.ajax_status != false) {

                            let tempDays = this.state.days;

                            Object.keys(tempDays).forEach((key, idx) => {
                                if (idx != 0) {
                                    tempDays[key]["hours"] = {};
                                    tempDays[key]["isChecked"] = false;
                                    [...Array(24)].forEach((hour, index) => {
                                        tempDays[key]["hours"][index] = false;
                                    })
                                }
                            });
                            tempDays["Days"] = {
                                "isChecked" : false
                            }
                            this.setState({days: tempDays})

                            this.setState({
                                scheduleName: "",
                                selectedProfile: null,
                                selectedPfCampaign: null,
                                selectedPortfoliosCampaigns: null,
                                PortfoliosCampaignsOptions: [],
                                startDateDP: moment(new Date()).format("MM-DD-YYYY"),
                                endDateDP: "",
                                emailReceiptStart: false,
                                emailReceiptEnd: false,
                                ccEmail: [],
                                items: [],
                                isDataTableReload: true,
                                reset: true
                            }, () => {
                                this.setState({
                                    reset: false
                                })
                            });
                            this.props.dispatch(ShowSuccessMsg(data.success, "Successfully", true, "", this.props.updateDataTableAfteSubmit()));

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
            //endDateDP: moment(date).format('DD-MM-YYYY'),
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
    handleModalClose = () => {
        this.setState({
            openModal: false,
            modalBody: '',
            maxWidth: 'md',
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
                this.myRef.current.setSelectedTargets(selectedBoxes)
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
                this.myRef.current.setSelectedTargets(selectedBoxes)

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

    resetEndDate = () => {
        alert('reset end date');

        this.setState({
            endDateDP: '',
        })
    }

    render() {
        const {classes} = this.props;
        return (
            <MuiPickersUtilsProvider utils={MomentUtils}>
                <div className="p-5 rounded-lg">

                    <form>
                        <Grid container justify="center" spacing={3}>
                            <Grid item xs={12} sm={4} md={4} lg={4}
                            >
                                <label className="inline-block mb-2 ml-2 text-sm">
                                    Schedule Name <span className="required-asterisk">*</span>
                                </label>

                                <TextFieldInput
                                    placeholder="Schedule Name"
                                    type="text"
                                    className="dayPartingTextField rounded-full bg-white"
                                    name="scheduleName"
                                    value={this.state.scheduleName}
                                    onChange={this.onChangeText}
                                    fullWidth={true}
                                />
                                <div className="error pl-2">{this.state.errors.scheduleN}</div>
                            </Grid>
                            <Grid item xs={12} sm={4} md={4} lg={4}>
                                <label className="text-sm  ml-2">
                                    Child Brand <span className="required-asterisk">*</span>
                                </label>
                                <SingleSelect
                                    placeholder="Child Brand"
                                    name={"selectedProfile"}
                                    value={this.state.selectedProfile}
                                    onChangeHandler={this.onProfileChange}
                                    fullWidth={true}
                                    Options={this.state.profileOptions}
                                    isClearable={false}
                                />
                                <div className="error pl-2">{this.state.errors.selectedP}</div>
                            </Grid>
                            <Grid item xs={12} sm={4} md={4} lg={4} className="autoScrl">
                                <label className="text-sm  ml-2">
                                    Portfolio/Campaign <span className="required-asterisk">*</span>
                                </label>
                                <SingleSelect
                                    placeholder="Portfolio/Campaign"
                                    name="selectedCampaign"
                                    value={this.state.selectedPfCampaign}
                                    onChangeHandler={this.onPfCampaignChange}
                                    Options={this.state.selectedPfCampaignOption}
                                    fullWidth={true}
                                    isClearable={false}
                                />
                                <div className="error pl-2">{this.state.errors.selectedPfCp}</div>
                            </Grid>
                            <Grid item xs={12} sm={4} md={4} lg={4}>
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
                            <Grid item xs={12} sm={4} md={4} lg={4}>
                                <label className="inline-block mb-2 ml-2 text-sm">
                                    Start Date <span className="required-asterisk">*</span>
                                </label>
                                <div onClick={this.handleOnStartDateRangeClick}>
                                    <TextFieldInput
                                        placeholder="Start Date"
                                        type="text"
                                        className="dayPartingTextField rounded-full bg-white"
                                        value={this.state.startDateDP}
                                        fullWidth={true}
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
                                    End Date <span
                                    className="required-asterisk">{/* <RotateLeftIcon color="action" fontSize="small" onClick={this.resetEndDate} /> */}</span>
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
                            <Grid item xs={12} sm={12} md={12} lg={12} className={"dpGridView"}>
                                <div className="p-1 mt-3 items-center flex justify-start overflow-auto">
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
                                    <div className="flex flex-col w-10/12 border selecto-container">
                                        <div className="flex flex-row">
                                            {hours.map((item, index) => (
                                                <HeaderRow
                                                    header={item}
                                                    key={`h${index}`}
                                                />
                                            ))}
                                        </div>
                                        <Selecto
                                            ref={this.myRef}
                                            // The container to add a selection element
                                            container={document.querySelector(".selecto-container")}
                                            // The area to drag selection element (default: container)
                                            dragContainer={document.querySelector(".dpGridView")}
                                            selectableTargets={[".dpGridView .hourRows"]}
                                            continueSelect={true}
                                            hitRate={1}
                                            selectByClick={true}
                                            //selectFromInside={true}
                                            //toggleContinueSelect={["shift"]}
                                            // ratio={0}
                                            onSelectEnd={e => {

                                                let newDays = {...this.state.days};
                                                Object.keys(newDays).forEach((item, idx) => {
                                                    if (idx !== 0) {

                                                        const selectedItemForAParent = $(`div[parentname='${item}']`)
                                                        const currentSelected = {};
                                                        $.each(selectedItemForAParent, function (indexInArray, element) {
                                                            if ($(element).hasClass("bg-yellow-600")) {
                                                                currentSelected[$(element).attr("itemindex")] = true;
                                                            } else {
                                                                currentSelected[$(element).attr("itemindex")] = false;
                                                            }

                                                        });

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

                                                    if (!el.className.includes('bg-yellow-600'))
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

                                                    <div className="flex flex-row" key={`dh${idx}`}>
                                                        {
                                                            Object.keys(this.state.days[item].hours).map((value, index) => (
                                                                <HoursRows
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
                            <Grid item xs={12} sm={12} md={12} lg={12}>
                                <label className="text-sm  ml-2 inline-block mb-2">
                                    Cc Email
                                </label>
                                <CcEmail items={[]} isReset={this.state.reset} getUpdatedItems={this.getUpdatedItems}>
                                </CcEmail>

                            </Grid>
                            <Grid item container xs={12}>
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
                        </Grid>
                        <div className="flex mt-8 justify-end">
                            <PrimaryButton
                                variant={"contained"}
                                disabled={this.state.isBtnEnableDisable}
                                btntext={this.state.btnText}
                                onClick={this.formSubmissionDayParting}
                            />
                        </div>
                    </form>
                </div>
                <DayPartingModal
                    openModal={this.state.openModal}
                    modalTitle={this.state.modalTitle}
                    id={this.state.id}
                    smallDailog={'activeSmallDailog'}
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

export default withStyles(styles)(connect(null)(AddFormDayParting))