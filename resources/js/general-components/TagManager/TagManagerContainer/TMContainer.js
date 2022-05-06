import React, {Component} from 'react';
import clsx from 'clsx';
import './../CustomMaterailize.scss';
import CloseIcon from '@material-ui/icons/Close';
import CheckIcon from '@material-ui/icons/Check';
import {post, put} from './../../../service/service';
import $ from 'jquery';
import './../TagManager.scss';
import './TMContainer.scss';
import './../CampaignTagging.scss';
import ModalDialog from './../../../general-components/ModalDialog';
import TacosConfirmationPopup
    from '../../../components/Manager-Module/Tacos/TacosConfirmationPopup/Delete/TacosConfirmationPopup';
import {countProducts} from "../../../components/Manager-Module/Tacos/TacosHelper";

const DotForTM = (props) => <span className="itemAdded"></span>;
const ExtraDotCounterForTM = (props) => <span className="extraDotCounter">+{props.count}</span>;

function generate(element, elements) {
    return Array.from({length: elements}, (_, idx) => `${++idx}`).map((value, index) => {
        return React.cloneElement(element, {
            key: value,
        })
    });
}//end function

function isAlphaNumaric(value) {
    var letters = /^[0-9a-zA-Z]/gi;
    if (value.match(letters) == null) return false;
    return true;
}//end function
export default class TMContainer extends Component {
    constructor(props) {
        super(props);
        this.state = {
            dotsLimit: 0,
            dotCounter: props.dots,
            move: false,
            isMoved: true,
            tacoss: [],
            tacosData: {
                tacos: "",
                metric: "acos",
                min: "",
                max: "",
                isSaving: false,
            },
            tacosPopup: {
                isLoading: false,
                isEditing: false,
            },
            tacosErrorMessage: {
                value: "#",
                message: "Write Tacos Then Press Enter",
                isValid: true,
                isLoading: false,
            },
            tacosTimer: 3,
            tacosConfirmation: {
                openTacosConfirmationPoup: false,
                confirmationField: ""
            },
            SBCount: 0,
            SPCount: 0,
            SDCount: 0,
        }
    }

    componentDidMount() {
        let itemCounterWidth = $(".itemCounts").width();
        let dotsLimit = Math.floor(itemCounterWidth / 12) - 1;
        this.setState({
            dotsLimit,
            tacosData: {
                tacos: this.props.row ? this.props.row.tacos : "",
                metric: this.props.row ? this.props.row.metric : "acos",
                min: this.props.row && this.props.row.min != 0 ? this.props.row.min : "",
                max: this.props.row && this.props.row.max != 0 ? this.props.row.max : "",
                isEditing: this.props.isEditing,
                isSaving: false,
            },
            tacosConfirmation: {
                openTacosConfirmationPoup: false,
                confirmationField: ""
            }
        })
    }

    static getDerivedStateFromProps(nextProps, prevState) {
        if (nextProps.dots) {
            return ({dotCounter: nextProps.dots});
        }
        return null;
    }

    handleAddTacosOnKeyUp = (e) => {

        if (this.state.tacosErrorMessage.isLoading || !this.state.tacosErrorMessage.isValid) return;
        let campaignIds = this.props.selectedObject;
        let SBProducts = countProducts(campaignIds, 'sponsoredBrands');
        let SDProducts = countProducts(campaignIds, 'sponsoredDisplay');
        let SPProducts = countProducts(campaignIds, 'sponsoredProducts');


        const {metric, tacos, min, max, isEditing} = this.state.tacosData;
        if (
            (!e.keyCode || e.keyCode == 13) &&
            (parseFloat(tacos) > 0) &&

            Object.size(campaignIds) > 0
        ) {
            this.setState({
                SBCount: SBProducts.length,
                SPCount: SPProducts.length,
                SDCount: SDProducts.length,
            })

            if (min == "") {
                this.helperShowAddTacosInputInvalid("Please fill out all fields");
                return;
            }

            if (parseFloat(min) < 0.02 && SDProducts.length > 0 || parseFloat(min) < 0.02 && SPProducts.length > 0) {
                this.openTacosConfirmation("min")
                return;
            }

            if (parseFloat(min) < 0.10 && SBProducts.length > 0) {
                this.openTacosConfirmation("min")
                return;
            }

            // if ((max != "" && parseFloat(max) < 0.02)) {
            //     this.openTacosConfirmation("max")
            //     return;
            // }
            if (min.toString().length > 0 && max.toString().length > 0 && parseFloat(min) >= parseFloat(max)) {
                this.helperShowAddTacosInputInvalid("Minimum bid must be less than maximum bid");
                return;
            }
            this.helperSetAddTacosLoader(true);
            var ajaxData = {
                metric: metric,
                tacos: tacos,
                min: min == "" ? 0.00 : min,
                max: max == "" ? 0.00 : max,
                campaignIds: campaignIds,
                _token: $("body").attr("csrf")
            };
            ajaxData[e.target.name] = e.target.value;


            ajaxData.min = ajaxData.min == "" ? 0.00 : ajaxData.min;
            ajaxData.max = ajaxData.max == "" ? 0.00 : ajaxData.max;

            isEditing ? this.updateTacos(ajaxData) : this.addTacos(ajaxData);

        } //end if
        else {
            if (!e.keyCode || e.keyCode === 13)
                this.helperShowAddTacosInputInvalid(parseFloat(tacos) <= 0 ? "Tacos value must be greater than 0" : "Please fill out all fileds", 2000);
        }
    }//end function
    addTacos = (ajaxData) => {
        post(window.baseUrl + "/tacos",
            {
                ...ajaxData,
            },//success
            () => {
                this.helperSetAddTacosLoader(false);
                this.props.showDataTableLoader();
            },//error
            (message) => {
                this.helperShowAddTacosInputInvalid("Internal Server Error");
                this.helperSetAddTacosLoader(false);
            },
        );
    }

    updateTacos = (ajaxData) => {
        delete ajaxData.campaignIds;
        delete ajaxData._token;
        put(window.baseUrl + "/tacos/" + this.props.row.id,
            {
                ...ajaxData,
            },//success
            (repsonse) => {
                this.helperSetAddTacosLoader(false);
                this.props.reloadHistoryDatatable && this.props.reloadHistoryDatatable();
            },//error
            (error) => {
                console.error(error);
                this.helperShowAddTacosInputInvalid("Fail to update tacos see console for further details");
                this.helperSetAddTacosLoader(false);
            },
        );
    }
    handleRacosPopupLodaer = (show) => {
        this.setState((prevState) => ({
            tacosPopup: {
                ...prevState.tacosPopup,
                isLoading: show,
            }
        }));
    }
    helperSetAddTacosInputValue = (value, message, isValid) => {
        const {tacosErrorMessage} = this.state;

        if (!value && !message && isValid == null) return;

        if (value) {
            tacosErrorMessage.value = value;
        }
        if (message) {
            tacosErrorMessage.message = message;
        }
        if (isValid != null) {
            tacosErrorMessage.isValid = isValid;
        }
        this.setState({
            tacosErrorMessage: (tacosErrorMessage)
        });
    }
    helperSetAddTacosLoader = (isLoading) => {
        const {tacosErrorMessage, tacosPopup} = this.state;
        tacosErrorMessage.isLoading = isLoading;
        tacosPopup.isLoading = isLoading;
        this.setState({
            tacosErrorMessage,
            tacosPopup
        });
    }

    helperShowAddTacosInputInvalid(message = "Error please try again :-(", messageDisappearingTime = 3000) {
        this.setState({tacosTimer: (messageDisappearingTime / 1000)})
        this.helperSetAddTacosInputValue(null, message, false);
        let _self = this;

        const tacosTimerInterval = setInterval(() => {
            _self.setState({tacosTimer: _self.state.tacosTimer >= 0 ? _self.state.tacosTimer - 1 : 0})
        }, 1000)
        setTimeout(function () {
            _self.helperSetAddTacosInputValue(null, message, true);
            _self.setState({tacosTimer: 3})
            clearInterval(tacosTimerInterval);
        }, messageDisappearingTime);
    }

    handleTacosMangerPopupClose = (e) => {
        this.setState((prevState) => ({
            tacosData: {
                ...prevState.tacosData,
                tacosType: null
            }
        }));
        this.props.onTacosPopupClose && this.props.onTacosPopupClose(true);
    }
    handleTacosPopupLodaer = (show) => {
        this.setState((prevState) => ({
            tacosData: {
                ...prevState.tacosData,
                isSaving: show,
            }
        }));
    }
    getInput = (name, value) => {
        this.setState(prevState => ({
            tacosData: {
                ...prevState.tacosData,
                [name]: value
            },
            tacosConfirmation: {
                openTacosConfirmationPoup: false,
                confirmationField: ""
            }
        }))
    }
    openTacosConfirmation = (name) => {
        this.setState({
            tacosConfirmation: {
                openTacosConfirmationPoup: true,
                confirmationField: name,
            }
        });
    }

    render() {
        const {
            dotsLimit,
            dotCounter,
            move,
            tacosErrorMessage,
            tacosPopup,
            tacosData,
            tacosTimer,
            tacosConfirmation
        } = this.state;
        const {ChildComponent} = this.props;
        var totalDots = [];
        if (dotCounter <= dotsLimit) {
            totalDots = generate(<DotForTM/>, dotCounter);
        } else {
            totalDots = generate(<DotForTM/>, dotsLimit);
            totalDots.push(generate(<ExtraDotCounterForTM count={(dotCounter - dotsLimit)}/>, 1));
        }
        return (
            <>
                <div
                    className={`tacosGroupManager tagGroupManager shadow overflow-hidden materializeCss ${!tacosErrorMessage.isValid && "invalid"}`}>
                    <div className="responseMessage">
                        <p>{tacosErrorMessage.message}</p>
                        <div class="absolute centerChild flex responseMessageTime">{tacosTimer}</div>
                    </div>
                    <div className="flex row">
                        <div className="progress" style={tacosPopup.isLoading ? {display: "block"} : {display: "none"}}>
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
                                    tacosData={this.state.tacosData}
                                    handleAddTacosOnKeyUp={this.handleAddTacosOnKeyUp}
                                />
                                <div className="control deleteControl" unselectable="on"
                                     onClick={this.handleAddTacosOnKeyUp}>
                                    <CheckIcon/>
                                    <div>submit</div>
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center justify-center section4 sectionCustom w-1/12">
                            <div className="closeButton" onClick={this.handleTacosMangerPopupClose}>
                                <CloseIcon/>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="modelClass">
                    <ModalDialog
                        open={tacosConfirmation.openTacosConfirmationPoup}
                        title={"Tacos"}
                        handleClose={() => this.setState({
                            tacosConfirmation: {
                                openTacosConfirmationPoup: false,
                                confirmationField: "",
                            }
                        })}
                        component={<TacosConfirmationPopup
                            getInput={this.getInput}
                            closeTacosConfirmationPopup={
                                () => {
                                    this.setState({
                                        tacosConfirmation: {
                                            openTacosConfirmationPoup: false,
                                            confirmationField: "",
                                        }
                                    })
                                }
                            }
                            name={tacosConfirmation.confirmationField}
                            fieldName={tacosConfirmation.confirmationField === "min" ? "Minimum" : "Maximum"}
                            data={this.state}
                        />}
                        maxWidth={"sm"}
                        fullWidth={true}
                        disable={true}
                        cancel={true}
                        modelClass="TacosConfirmation"
                    >
                    </ModalDialog>
                </div>
            </>
        )
    }
}
