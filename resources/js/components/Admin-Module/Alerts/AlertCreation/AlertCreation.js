import React, {Component} from 'react';
import SingleSelect from "../../../../general-components/Select";
import {Grid} from "@material-ui/core";
import TextFieldInput from "../../../../general-components/Textfield";
import CcEmail from "../../../../general-components/EmailCC/EmailChips";
import CheckBox from "../../../../general-components/CheckBox";
import "../alertManagement.scss";
import TextButton from "../../../../general-components/TextButton";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import {getChildBrandData, storeAlertForm, updateAlertForm} from "../ApiCalls";
import * as Yup from "yup";
import {objectRequiredValidationHelper, stringRequiredValidationHelper} from "../../../../helper/yupHelpers";
import {ShowSuccessMsg} from "../../../../general-components/successDailog/actions";
import {ShowFailureMsg} from "../../../../general-components/failureDailog/actions";
import {connect} from "react-redux";
import LinearProgress from "@material-ui/core/LinearProgress";
import {filterSelectedUsers} from "../../Manage-Brands/apiCalls";
import {values} from "lodash/object";

class AlertCreation extends Component {
    constructor(props) {
        super(props);
        this.state = {
            id:null,
            alertName: "",
            childSelected: null,
            childBrandOptions: [],
            ccEmail: [],
            items: [],
            reset: false,
            tacos: false,
            biddingRule: false,
            dayParting: false,
            bidMultiplier: false,
            budgetMultiplier: false,
            isProcessing: false,
            isEdit: false,
            errors: {
                alertNameE: "",
                childBrandE: "",
                noCheckBox: ""
            }
        }
    }

    componentDidMount() {
        this.getChildBranData()
    }
    getChildBranData = () => {
        getChildBrandData((childBrandOptions) => {
            this.setState({
                childBrandOptions
            }, () => {

                if(this.props.isEdit){
                    let selected = childBrandOptions.filter(filterValue => this.props.row.fkProfileId == filterValue.value)
                        .map((obj, idx) => {
                            return obj
                    })
                    let childSelected = {
                            value: selected[0].value,
                            label: selected[0].label,
                            accountId:selected[0].accountId
                    }
                    console.log(
                        'child seleceted',
                        childSelected
                    )
                    this.setState({
                        childSelected,
                        isProcessing: false,
                    })
                }
            })

        })
    }
    static getDerivedStateFromProps(nextProps, prevState){

        if(nextProps.isEdit && prevState.isEdit == false){
            return {
                alertName:nextProps.row.alertName,
                tacos:(nextProps.row.tacosAlertsStatus == 1),
                biddingRule:(nextProps.row.biddingRuleAlertsStatus == 1),
                dayParting:(nextProps.row.dayPartingAlertsStatus == 1),
                bidMultiplier:(nextProps.row.bidMultiplierAlertsStatus == 1),
                budgetMultiplier:(nextProps.row.budgetMultiplierAlertsStatus == 1),
                ccEmail: (nextProps.row.addCC.length > 0) ? nextProps.row.addCC.split(',') : [],
                id: nextProps.row.id,
                isEdit: nextProps.isEdit
            }
        }
        return null;
    }
    onChangeText = (event) => {
        this.setState({
            alertName: event.target.value
        }, () => {
            this.resetErrors("alertNameE")
        })
    }
    onProfileChange = (value) => {

        this.setState({
            childSelected: value,
        }, () => {
            this.resetErrors('childBrandE')
        })
    }

    resetErrors = (key) => {
        const {errors} = this.state;
        errors[key] = "";
        this.setState({
            ...errors
        })
    }

    onCheckBoxesChangeHandler = (e) => {
        this.setState({
            [e.target.name]: e.target.checked
        }, () => {
        })
    }
    getUpdatedItems = (items) => {
        this.setState({
            ccEmail: items
        })
    }

    setMessageOfCheckBox = (message, key) => {
        const {errors} = this.state;
        errors[key] = message
        this.setState({
            ...errors
        })
    }
    formSubmission = (event) => {
        event.preventDefault();
        var isCheckBoxError = true
        let tacos = this.state.tacos;
        let biddingRule = this.state.biddingRule;
        let dayParting = this.state.dayParting;
        let bidMultiplier = this.state.bidMultiplier;
        let budgetMultiplier = this.state.budgetMultiplier;

        if (!tacos && !biddingRule && !dayParting && !bidMultiplier && !budgetMultiplier) {
            this.setMessageOfCheckBox('Select at least one module', 'noCheckBox')
            isCheckBoxError = false
        } else {
            this.setMessageOfCheckBox('', 'noCheckBox')
        }

        let validationSchema = Yup.object().shape({
            alertNameE: stringRequiredValidationHelper("alert Name"),
            childBrandE: objectRequiredValidationHelper("child Brand"),
        });

        let dataToValidateObject = {
            alertNameE: this.state.alertName,
            childBrandE: this.state.childSelected,
        }

        let validationFormData = htk.validateAllFields(validationSchema, dataToValidateObject);

        if (Object.size(validationFormData) > 0 || !isCheckBoxError) {
            const {errors} = this.state;
            $.each(validationFormData, function (indexInArray, valueOfElement) {
                errors[indexInArray] = valueOfElement;
            });

            this.setState((prevState) => ({
                errors: errors
            }));
        } else {

            let params = {
                alertName: this.state.alertName,
                fkAccountId: this.state.childSelected.accountId,
                fkProfileId: this.state.childSelected.value,
                tacosAlertsStatus: this.state.tacos,
                biddingRuleAlertsStatus: this.state.biddingRule,
                dayPartingAlertsStatus: this.state.dayParting,
                bidMultiplierAlertsStatus: this.state.bidMultiplier,
                budgetMultiplierAlertsStatus: this.state.budgetMultiplier,
                addCC: this.state.ccEmail
            }
            this.setState({
                isProcessing: true
            })

            if (this.props.isEdit){
                params.id = this.props.row.id

                this.updateAlert(params)
            }else{
                this.addAlert(params)
            }
        }
    }

    addAlert = (params) =>{

        storeAlertForm(params, (data) => {

            this.setState({
                isProcessing: false
            })
            if (data.ajax_status == true) {

                this.props.dispatch(ShowSuccessMsg(data.success, "", true, "", this.props.updateDataTableAfterSubmit()));
            } else {
                this.props.dispatch(ShowFailureMsg(data.error, "", true, "", ""))
            }
        })
    }

    updateAlert = (params) => {
        updateAlertForm(params, (data) => {

            this.setState({
                isProcessing: false
            })
            if (data.ajax_status == true) {

                this.props.dispatch(ShowSuccessMsg(data.success, "", true, "", this.props.updateDataTableAfterSubmit()));
            } else {
                this.props.dispatch(ShowFailureMsg(data.error, "", true, "", ""))
            }
        })
    }
    formUpdate = (event) => {
        event.preventDefault();
        let tacos = this.state.tacos;
        let biddingRule = this.state.biddingRule;
        let dayParting = this.state.dayParting;
        let bidMultiplier = this.state.bidMultiplier;
        let budgetMultiplier = this.state.budgetMultiplier;

        if (!tacos && !biddingRule && !dayParting && !bidMultiplier && !budgetMultiplier) {
            this.setMessageOfCheckBox('Select at least one module', 'noCheckBox')

        } else {
            this.setMessageOfCheckBox('', 'noCheckBox')
        }

        let validationSchema = Yup.object().shape({
            alertNameE: stringRequiredValidationHelper("alert Name"),
            childBrandE: objectRequiredValidationHelper("child Brand"),
        });

        let dataToValidateObject = {
            alertNameE: this.state.alertName,
            childBrandE: this.state.childSelected,
        }

        let validationFormData = htk.validateAllFields(validationSchema, dataToValidateObject);

        if (Object.size(validationFormData) > 0) {
            const {errors} = this.state;
            $.each(validationFormData, function (indexInArray, valueOfElement) {
                errors[indexInArray] = valueOfElement;
            });

            this.setState((prevState) => ({
                errors: errors
            }));
        } else {

            let params = {
                alertName: this.state.alertName,
                fkAccountId: this.state.childSelected.accountId,
                fkProfileId: this.state.childSelected.value,
                tacosAlertsStatus: this.state.tacos,
                biddingRuleAlertsStatus: this.state.biddingRule,
                dayPartingAlertsStatus: this.state.dayParting,
                bidMultiplierAlertsStatus: this.state.bidMultiplier,
                budgetMultiplierAlertsStatus: this.state.budgetMultiplier,
                addCC: this.state.ccEmail
            }
            this.setState({
                isProcessing: true
            })
            storeAlertForm(params, (data) => {

                this.setState({
                    isProcessing: false
                })
                if (data.ajax_status == true) {

                    this.props.dispatch(ShowSuccessMsg(data.success, "", true, "", this.props.updateDataTableAfterSubmit()));
                } else {
                    this.props.dispatch(ShowFailureMsg(data.error, "", true, "", ""))
                }
            })
        }
    }

    render() {

        return (
            <>
                <div className="p-5 rounded-lg">
                    <div className="graphLoader bg-white absolute h-full overflow-hidden w-full top-0 left-0 z-10"
                         style={this.state.isProcessing ? {display: "block"} : {display: "none"}}>
                        <LinearProgress/>
                        <div
                            className="absolute flex font-bold font-mono h-full items-center justify-center overflow-hidden text-1rem text-sm w-full z-10">
                            Processing...
                        </div>
                    </div>
                    <form>
                        <Grid container justify="center" spacing={3}>
                            <Grid item xs={12} sm={6} md={6} lg={6}
                            >
                                <label className="inline-block mb-2 ml-2 text-sm">
                                    Alert Name <span className="required-asterisk">*</span>
                                </label>

                                <TextFieldInput
                                    placeholder="Alert Name"
                                    type="text"
                                    className="alertTextField rounded-full bg-white"
                                    name="alertName"
                                    value={this.state.alertName}
                                    onChange={this.onChangeText}
                                    fullWidth={true}
                                />
                                <div className="error pl-2">{this.state.errors.alertNameE}</div>

                            </Grid>
                            <Grid item xs={12} sm={6} md={6} lg={6}>
                                <label className="text-sm  ml-2">
                                    Child Brand <span className="required-asterisk">*</span>
                                </label>
                                <SingleSelect
                                    placeholder="Child Brand"
                                    name={"childBrand"}
                                    value={this.state.childSelected}
                                    onChangeHandler={this.onProfileChange}
                                    fullWidth={true}
                                    Options={this.state.childBrandOptions}
                                    isClearable={false}
                                />
                                <div className="error pl-2">{this.state.errors.childBrandE}</div>

                            </Grid>
                            <Grid item xs={12} className="pt-0 pb-0">
                                <div className="error pl-2">{this.state.errors.noCheckBox}</div>
                            </Grid>
                            <Grid item xs={12} sm={2} md={2} lg={2} className="h-px flex items-center alertCheckBoxes">
                                <CheckBox
                                    label="TACOS"
                                    checked={this.state.tacos}
                                    onChange={this.onCheckBoxesChangeHandler}
                                    name="tacos"
                                />

                            </Grid>
                            <Grid item xs={12} sm={2} md={2} lg={2} className="h-px flex items-center alertCheckBoxes">
                                <CheckBox
                                    label="BIDDING RULE"
                                    checked={this.state.biddingRule}
                                    onChange={this.onCheckBoxesChangeHandler}
                                    name="biddingRule"
                                />

                            </Grid>
                            <Grid item xs={12} sm={2} md={2} lg={2} className="h-px flex items-center alertCheckBoxes">
                                <CheckBox
                                    label="DAY PARTING"
                                    checked={this.state.dayParting}
                                    onChange={this.onCheckBoxesChangeHandler}
                                    name="dayParting"
                                />

                            </Grid>
                            <Grid item xs={12} sm={2} md={2} lg={2} className="h-px flex items-center alertCheckBoxes">
                                <CheckBox
                                    label="Bid Multiplier"
                                    checked={this.state.bidMultiplier}
                                    onChange={this.onCheckBoxesChangeHandler}
                                    name="bidMultiplier"
                                />

                            </Grid>
                            <Grid item xs={12} sm={2} md={2} lg={2} className="h-px flex items-center alertCheckBoxes">
                                <CheckBox
                                    label="Budget Multiplier"
                                    checked={this.state.budgetMultiplier}
                                    onChange={this.onCheckBoxesChangeHandler}
                                    name="budgetMultiplier"
                                />

                            </Grid>
                            <Grid item xs={12} sm={12} md={12} lg={12}>
                                <label className="text-sm  ml-2 inline-block mb-2">
                                    Cc Email
                                </label>
                                <CcEmail items={this.state.isEdit  ? this.state.ccEmail : []} isReset={this.state.reset} getUpdatedItems={this.getUpdatedItems}>

                                </CcEmail>

                            </Grid>
                            <Grid item xs={12} md={12} lg={12} className="text-center">
                                <Grid container justify="center" spacing={2}>
                                    <Grid item xs={2} md={2} lg={2}>
                                        <TextButton
                                            btntext={" Cancel "}
                                            color="primary"
                                            onClick={this.props.handleModalClose}/>

                                    </Grid>
                                    <Grid item xs={2} md={2} lg={2}>
                                        <PrimaryButton
                                            btnlabel={this.props.isEdit ? "Update" : "Submit"}
                                            variant={"contained"}
                                            onClick={this.formSubmission}/>

                                    </Grid>
                                </Grid>
                            </Grid>
                        </Grid>
                    </form>
                </div>
            </>
        )
    }


}

export default (connect(null)(AlertCreation))