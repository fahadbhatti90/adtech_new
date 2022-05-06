import React, {Component} from 'react';
import clsx from 'clsx';
import {connect} from "react-redux";
import {getAllNavigationData} from './../GBS/apiCalls';
import {logoutFromBackend} from './apiCalls';
import {currentDate} from "./../../../../helper/helper";
import {SetNotificationCount} from './../../../../general-components/Notification/actions'
import PageHeading from './PageHeading';
import AppBarScearchElement from './AppBarScearchElement';
import AppBar from '@material-ui/core/AppBar';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';
import Toolbar from '@material-ui/core/Toolbar';
import {SetIsAdmin} from './../../../SideBars/redux/sideBarAction';
import GBSModel from './../GBS/GBSModel';
import GBSwitcherControls from './../GBS/GBSwitcherControls';
import AppBarUserElement from './AppBarUserElement';
//import InfoIcon from '@material-ui/icons/Info';
import InfoIcon from '../../../../app-resources/svgs/info.png';
import LogoBlackIdeo from "../../../../app-resources/svgs/Ideo-Black.svg";
import SvgLoader from "../../../../general-components/SvgLoader";
import Tooltip from "@material-ui/core/Tooltip";

class Header extends Component {
    constructor(props) {
        super(props);
        this.state = {
            addGBS: false,
            parentBrands: {
                brands: [],
                selected: 0,
                selectedBrandName: "No Brand Assigned",
            },
            default: {
                selected: 0,
                selectedBrandName: "No Brand Assigned",
            },
            modal: {
                open: false,
                modalComponent: null,
                modalTitle: ""
            },
            showPreloader: false,
        }
    }

    componentDidMount() {
        if (!htk.isUserLoggedIn() && htk.activeRole != 3 && htk.isSuperAdmin()) {
            return;
        }
        this.getLatestNavigationInfo(parseInt(htk.activeRole));
    }

    handleParentBrandButtonClick = (e) => {
        this.setState((prevState) => ({
            modal: {
                ...prevState.modal,
                open: true,
                modalTitle: "Parent Brand Switcher"
            },
        }));

        getAllNavigationData(
            {
                switchingPortalTo: parseInt(htk.activeRole)
            },
            (response) => {
                let res = response.data;
                this.props.dispatch(SetNotificationCount(res.notiCount));
                if (res.status && res.data.length > 0) {
                    let parentBrands = {
                        brands: res.data
                    }
                    this.setState((prevState) => ({
                        modal: {
                            ...prevState.modal,
                            modalComponent: <GBSwitcherControls
                                id={0}
                                handleModalClose={this.handleModalClose}
                                propHandlerForModelClosing={this.propHandlerForModelClosing}
                                parentBrands={parentBrands}
                                selectedBrandId={res.selected}
                                selectedBrandName={res.selectedBrandName}
                                setSelectedBrand={this.setSelectedBrand}
                            />,
                        },
                        parentBrands: {
                            ...prevState.parentBrands,
                            brands: res.data,
                            selected: res.selected,
                            selectedBrandName: res.selectedBrandName,
                        },
                        default: {
                            selected: res.selected,
                            selectedBrandName: res.selectedBrandName,
                        }
                    }));
                }
                this.setState({
                    showPreloader: false,
                });
            },
            (error) => {
                this.setState({
                    showPreloader: false,
                });
                console.log("Error While Fetching Parent Brands => ", error)
            }
        );
    }
    setSelectedBrand = ({id, name}) => {
        this.setState(prevState => ({
            parentBrands: {
                ...prevState.parentBrands,
                selected: id,
                selectedBrandName: name,
            },
        }));
    }
    propHandlerForModelClosing = () => {
        this.setState({
            default: {
                selected: this.state.parentBrands.selected,
                selectedBrandName: this.state.parentBrands.selectedBrandName,
            }
        });
    }
    handleModalClose = (e) => {
        this.setState((prevState) => ({
            modal: {
                ...prevState.modal,
                open: false,
                modalComponent: null,
            }
        }))
    }

    getLatestNavigationInfo = (switchingPortalTo = null, isSwitchingPortal = false) => {
        getAllNavigationData({switchingPortalTo,},
            (response) => {
                let res = response.data;
                this.props.dispatch(SetNotificationCount(res.notiCount));
                if (switchingPortalTo == 3)
                    this.setBrandAndNotiCount(res);
                if (isSwitchingPortal)
                    this.portalSwitchingStuff();
            },
            (error) => {
                console.log("Error While Fetching Parent Brands => ", error)
            }
        );
    }
    portalSwitchingStuff = () => {
        htk.history.replace(htk.activeRole == 2 ? "/" : "/admin");
        this.props.dispatch(SetIsAdmin());
    }
    setBrandAndNotiCount = (res) => {
        if (res.status && res.data.length > 0) {
            this.setState((prevState) => ({
                parentBrands: {
                    ...prevState.parentBrands,
                    brands: res.data,
                    selected: res.selected,
                    selectedBrandName: res.selectedBrandName,
                },
                default: {
                    selected: res.selected,
                    selectedBrandName: res.selectedBrandName,
                }
            }));
        }
    }

    showInfoTime = () => {

    }

    render() {
        const {
            classes,
            handleDrawerToggle,
            openNotificationPopup,
            setOpenNotificationPopup,
        } = this.props;

        return (
            <>
                <AppBar position="absolute" className={clsx(classes.appBar, "appBar")}>
                    <div className="flex flex-row content-around">
                        <Toolbar className="flex-1 PageTitleContainer">
                            <IconButton
                                color="inherit"
                                aria-label="open drawer"
                                edge="start"
                                onClick={handleDrawerToggle}
                                className={classes.menuButton}
                            >
                                <MenuIcon/>
                            </IconButton>
                            <PageHeading/>
                        </Toolbar>
                        <AppBarScearchElement
                            openNotificationPopup={openNotificationPopup}
                            setOpenNotificationPopup={setOpenNotificationPopup}
                        />
                        <AppBarUserElement
                            logoutFromBackend={logoutFromBackend}
                            getLatestNavigationInfo={this.getLatestNavigationInfo}
                        />
                    </div>
                    <div className="flex items-center justify-between w-full">
                        <div className="appBarDateTime flex items-center mb-2 mt-2 text-xs">
                            <span className="block border border-solid border-gray-500 h-0 line mr-4 w-12"></span>
                            <span className="font-medium infoLabel mr-1 text-gray-500">Show:</span>
                            <span className="date font-bold">{currentDate()}</span>
                            <Tooltip placement="top" disableFocusListener title="All the scheduled date and time in portal are Pacific Standard Time (PST)">
                                <span className="cursor-pointer" onClick={this.showInfoTime}><SvgLoader
                                    src={InfoIcon}
                                    height="22px"
                                /></span>
                            </Tooltip>

                        </div>
                        {htk.activeRole == "3" ?
                            <div className="appBarGBS flex items-center mb-2 mt-2 text-xs">
                            <span className="date font-bold mr-3 cursor-pointer"
                                  onClick={this.handleParentBrandButtonClick}>
                                {this.state.default.selectedBrandName}
                            </span>
                                <span className="block border border-solid border-gray-500 h-0 line mr-4 w-12"></span>
                            </div> : ""
                        }
                    </div>

                </AppBar>
                <GBSModel
                    open={this.state.modal.open}
                    handleModalClose={this.handleModalClose}
                    modalComponent={this.state.modal.modalComponent}
                    modalTitle={this.state.modal.modalTitle}
                />
            </>
        )
    }
}

export default connect(null)(Header)