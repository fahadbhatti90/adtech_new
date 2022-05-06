import React, {Component} from "react";
import Card from "@material-ui/core/Card/Card";
import {Grid, withStyles} from "@material-ui/core";
import SingleComCard from "../SingleComCards/SingleComCard";
import DataTable from "react-data-table-component";
import {
    profileInfo,
    dataInformation,
    informationLinks,
    reportIdError,
    reportLinkError
} from "../TableContent/DataTablecolumns";
import TextFieldInput from "../../../../general-components/Textfield";
import {useStyles} from "../styles";
import {connect} from "react-redux";
import clsx from 'clsx';
import AreaChart from "../AreaGraphs/container";
import BarChart from "../BarGraphs/container";
import CustomDateRangePicker from "../../../Manager-Module/Events/CustomDateRangePicker";
import moment from "moment";
import "./HealthDashboard.scss";
import TotalReportIdLogo from "../../../../app-resources/svgs/superAdmin/healthDashboard/TotalReportID.svg";
import TotalReportsLogo from "../../../../app-resources/svgs/superAdmin/healthDashboard/TotalReports-01.svg";
import NewProfileLogo from "../../../../app-resources/svgs/superAdmin/healthDashboard/NewProfile-01.svg";
import ActiveProfilesLogo from "../../../../app-resources/svgs/superAdmin/healthDashboard/ActiveProfiles-01.svg";
import InactiveProfileLogo from "../../../../app-resources/svgs/superAdmin/healthDashboard/InactiveProfile-01.svg";
import ProfileInCompatibleLogo from "../../../../app-resources/svgs/superAdmin/healthDashboard/ProfileIncompetible-01.svg";
import AgencyTypeLogo from "../../../../app-resources/svgs/superAdmin/healthDashboard/AgencyType-01.svg";
import {getHealthDashboard} from "../apiCalls";
import SearchIcon from "@material-ui/core/SvgIcon/SvgIcon";
import {initialState} from '../initialState';
import LinearProgress from '@material-ui/core/LinearProgress';

const conditionalRowStyles = [
    {
        when: row => row.flag == 1,
        style: {
            backgroundColor: 'pink',
        },
    },

];
class HealthDashboard extends Component {
    constructor(props) {
        super(props);
        this.state = {
            ...initialState,
            HealthDate: moment(new Date()).subtract(1, 'days').format("DD-MM-YYYY"),
            renderGraph : true,
            isProcessing: false,
        }
    }

    handleOnDateRangeClick = (e) => {
        this.setState({
            showDRP: true
        })
    }

    handleSingleDateChange = (date) => {
        this.setState({
            HealthDate: moment(date).format('DD-MM-YYYY'),
            showDRP: false,
            renderGraph:false,
            isProcessing:true,
            ...initialState
        }, () => {
            this.getHealthDashboardData()
            //this.resetErrors('HealthDateE')
        })
    }

    getHealthDashboardData = () => {
        const params = {
            'healthDate': this.state.HealthDate
        }
        if (params != null) {

            getHealthDashboard(params, (data) => {
                let profileCounts = data.score_card;
                // Profile Score Cards
                if (typeof profileCounts !== 'undefined')
                    this.profileCountStates(profileCounts)


                // Profiles Validate
                let profilesValidate = data.profile_info;
                if (Object.keys(profilesValidate).length > 0) {
                    for (let i = 0; i < Object.keys(profilesValidate).length; ++i) {
                        profilesValidate[i]["serial"] = i + 1;
                    }
                    this.profileInfoStates(profilesValidate)
                }

                // Link Duplication
                let linkDuplication = data.link_duplication;
                if (Object.keys(linkDuplication).length > 0) {
                    for (let i = 0; i < Object.keys(linkDuplication).length; ++i) {
                        linkDuplication[i]["serial"] = i + 1;
                    }
                    this.linkDuplicationStates(linkDuplication)
                }

                // Data Duplication
                let dataDuplication = data.data_duplication;
                if (Object.keys(dataDuplication).length > 0) {
                    for (let i = 0; i < Object.keys(dataDuplication).length; ++i) {
                        dataDuplication[i]["serial"] = i + 1;
                    }
                    this.dataDuplicationStates(dataDuplication)
                }

                this.setState({
                    isProcessing: false,
                    renderGraph:true,
                })
                var mandatoryReportIds = [];
                var mandatoryReportTypeId = [];
                var getReportIds = [];
                var getReportTypeId = [];
                var getPopulateLinks = [];

                //if (data.getReportIdMandatory.length > 0){
                var mandatoryReportIds = data.getReportIdMandatory.map(function (obj) {
                    return +obj.total_report_id;
                });
                var mandatoryReportTypeId = data.getReportIdMandatory.map(function (obj) {
                    return obj.report_type_id;
                });
                // }
                //if (data.getReportId.length > 0){
                var getReportIds = data.getReportId.map(function (obj) {
                    return +obj.total_report_id;
                });
                var getReportTypeId = data.getReportId.map(function (params) {
                    return params.report_type_id;
                });
                //}
                // if (data.getPopulateLink.length > 0){
                var getPopulateLinks = data.getPopulateLink.map(function (obj) {
                    return +obj.total_link_count;
                });
                // }

                // if(mandatoryReportTypeId.length > 0 && getReportTypeId > 0){
                var reportTypeCategories = [...new Set([...mandatoryReportTypeId, ...getReportTypeId])];
                // console.log('union', union);
//                    var reportTypeCategories = mandatoryReportTypeId.filter(x => getReportTypeId.includes(x));
                if (mandatoryReportIds.length > 0)
                    mandatoryReportIds.unshift("Mandatory ID");
                if (getReportIds.length > 0)
                    getReportIds.unshift("Report ID");

                if (mandatoryReportIds.length > 0)
                    getPopulateLinks.unshift("Report Links");

                var areaChartData = [];
                var barChartData = [];
                if(mandatoryReportIds.length > 0 && getReportIds.length > 0)
                var areaChartData = [mandatoryReportIds, getReportIds];

                if(getPopulateLinks.length > 0 && getReportIds.length > 0)
                var barChartData = [getPopulateLinks, getReportIds];

                if (typeof areaChartData == 'undefined')
                    areaChartData = [];

                if (typeof barChartData == 'undefined')
                    barChartData = [];

                this.setState({
                    areaChartData: areaChartData,
                    barChartData: barChartData,
                    reportTypeCategories: reportTypeCategories
                })
                //}

                let reportIdError = data.report_id_error;
                if (Object.keys(reportIdError).length > 0) {
                    this.reportIdErrorStates(reportIdError)
                }
                let reportLinkError = data.report_link_error;
                if (Object.keys(reportLinkError).length > 0) {
                    this.reportLinkErrorStates(reportLinkError)
                }

            })
        }
    }

    setSingleDate = (date) => {
        this.setState({
            HealthDate: moment(date).format('DD-MM-YYYY'),
            showDRP: false
        }, () => {
            // this.resetErrors('HealthDateE')
        })
    }
    helperCloseDRP = (event) => {
        this.setState({
            showDRP: false
        })
    }
    profileValidateDataSearch = (e) => {
        if (e.target.value.length > 0) {
            var result = this.state.profileValidateOriginalData.filter(row => {
                let creationDate = row.creationDate.split(' ')[0];
                return row.profileId.toString().toLowerCase().includes(e.target.value.toLowerCase())
                    || row.name.toLowerCase().includes(e.target.value.toLowerCase())
                    || row.countryCode.toLowerCase().includes(e.target.value.toLowerCase())
                    || creationDate.includes(e.target.value);
            });
            this.setState({
                profileValidateData: result,
                profileValidateTotalRows: result.length
            })
        } else {
            this.setState({
                profileValidateData: this.state.profileValidateOriginalData,
                profileValidateTotalRows: this.state.profileValidateOriginalData.length
            })
        }
    }

    componentDidMount() {
        this.getHealthDashboardData();
    }

    profileCountStates = (profileCounts) => {
        this.setState({
            totalReportIdCount: profileCounts.Total_Report_Id,
            totalReportsCount: profileCounts.Total_Link_Reports,
            newProfileCount: profileCounts.New_Profile,
            activeProfilesCount: profileCounts.Active_Profile,
            inactiveProfileCount: profileCounts.InActive_Profile,
            profileIncompatibleCount: profileCounts.Profile_Incompatible_with_SD,
            agencyType: profileCounts.Agency_Type
        })
    }

    profileInfoStates = (profilesValidate) => {
        this.setState({
            profileValidateData: profilesValidate,
            profileValidateOriginalData: profilesValidate,
            profileValidateTotalRows: Object.keys(profilesValidate).length,
            profileLoading: false,
        })
    }

    linkDuplicationStates = (linkDuplication) => {
        this.setState({
            linkDuplicationData: linkDuplication,
            linkDuplicationOriginalData: linkDuplication,
            linkDuplicationLoading: false,
            linkDuplicationTotalRows: Object.keys(linkDuplication).length
        })
    }

    dataDuplicationStates = (dataDuplication) => {
        this.setState({
            dataDuplicationData: dataDuplication,
            dataDuplicationOriginalData: dataDuplication,
            dataDuplicationLoading: false,
            dataDuplicationTotalRows: Object.keys(dataDuplication).length
        })
    }
    reportIdErrorStates = (reportIdError) => {
        this.setState({
            reportIdErrorData: reportIdError,
            reportIdErrorOriginalData: reportIdError,
            reportIdErrorLoading: false,
            reportIdErrorPerPage: Object.keys(reportIdError).length,
        })
    }

    reportLinkErrorStates = (reportLinkError) => {
        this.setState({
            reportLinkErrorData: reportLinkError,
            reportLinkErrorOriginalData: reportLinkError,
            reportLinkErrorLoading: false,
            reportLinkErrorPerPage: Object.keys(reportLinkError).length,
        })
    }

    render() {
        const {classes} = this.props;
        const {loading, profileValidateData} = this.state;
        return (
            <div>
                <div className="graphLoader bg-white absolute h-full overflow-hidden w-full top-0 left-0 z-10"
                     style={this.state.isProcessing ? {display: "block"} : {display: "none"}}>
                    <LinearProgress/>
                    <div
                        className="absolute flex font-bold font-mono h-full items-center justify-center overflow-hidden text-1rem text-sm w-full z-10">
                        Processing...
                    </div>
                </div>

                <div className="manageBrand healthDashbaord">
                    <Grid className="flex items-center" item xs={12} sm={6} md={6} lg={6}>
                        <label className="inline-block ml-2 text-sm">
                            Date <span className="required-asterisk">*</span>
                        </label>
                        <div className="w-9/12" onClick={this.handleOnDateRangeClick}>
                            <TextFieldInput
                                placeholder="Date"
                                type="text"
                                name={"HealthDate"}
                                value={this.state.HealthDate}
                                fullWidth={true}
                                classesstyle={classes}
                            />
                            <div className="error pl-2">{this.state.HealthDateE}</div>
                        </div>
                        <div className={`absolute z-50 ${classes.datepickerClass}`}>
                            {
                                this.state.showDRP ?
                                    <CustomDateRangePicker range={this.state.dateRangeObj}
                                                           helperCloseDRP={this.helperCloseDRP}
                                                           setSingleDate={this.handleSingleDateChange}
                                                           date={new Date()}
                                                           direction="vertical"
                                                           isDateRange={false}/>
                                    : null
                            }
                        </div>
                    </Grid>
                    <div className="flex justify-center items-center h-full py-6">
                        {/* <Card classes={{root: classes.card}}> */}
                        <Grid container>
                            <Grid item xs={6} sm={4} md={3} lg={3}
                                  className="rounded border healthDashboardCardShadow bg-white px-6 py-4">
                                <SingleComCard
                                    toolTip={true} healthTitle="TOTAL REPORT ID"
                                    tooltipTitle={this.state.totalReportIdCount}
                                    value={this.state.totalReportIdCount}
                                    logo={TotalReportIdLogo}
                                />
                            </Grid>
                            <Grid item xs={6} sm={4} md={3} lg={3}
                                  className="rounded border healthDashboardCardShadow bg-white px-6 py-4">
                                <SingleComCard
                                    toolTip={true} healthTitle="TOTAL REPORT LINKS"
                                    tooltipTitle={this.state.totalReportsCount}
                                    value={this.state.totalReportsCount}
                                    logo={TotalReportsLogo}
                                />
                            </Grid>
                            <Grid item xs={6} sm={4} md={3} lg={3}
                                  className="rounded border healthDashboardCardShadow bg-white px-6 py-4">
                                <SingleComCard
                                    toolTip={true} healthTitle="NEW PROFILE"
                                    tooltipTitle={this.state.newProfileCount}
                                    value={this.state.newProfileCount}
                                    logo={NewProfileLogo}
                                />


                            </Grid>
                            <Grid item xs={6} sm={4} md={3} lg={3}
                                  className="rounded border healthDashboardCardShadow bg-white px-6 py-4">
                                <SingleComCard
                                    toolTip={true} healthTitle="ACTIVE PROFILES"
                                    tooltipTitle={this.state.activeProfilesCount}
                                    value={this.state.activeProfilesCount}
                                    logo={ActiveProfilesLogo}
                                />
                            </Grid>
                            <Grid item xs={6} sm={4} md={3} lg={3}
                                  className="rounded border healthDashboardCardShadow bg-white px-6 py-4">
                                <SingleComCard
                                    toolTip={true}
                                    healthTitle="INACTIVE PROFILES"
                                    tooltipTitle={this.state.inactiveProfileCount}
                                    value={this.state.inactiveProfileCount}
                                    logo={InactiveProfileLogo}
                                />
                            </Grid>
                            <Grid item xs={6} sm={4} md={3} lg={3}
                                  className="rounded border healthDashboardCardShadow bg-white px-4 py-4">
                                <SingleComCard
                                    toolTip={true} healthTitle="PROFILES INCOMPATIBLE WITH SD"
                                    tooltipTitle={this.state.profileIncompatibleCount}
                                    value={this.state.profileIncompatibleCount}
                                    logo={ProfileInCompatibleLogo}
                                />
                            </Grid>
                            <Grid item xs={6} sm={4} md={3} lg={3}
                                  className="rounded border healthDashboardCardShadow bg-white px-6 py-4">
                                <SingleComCard
                                    toolTip={true} healthTitle="AGENCY TYPE"
                                    tooltipTitle={this.state.agencyType}
                                    value={this.state.agencyType}
                                    logo={AgencyTypeLogo}
                                />
                            </Grid>
                        </Grid>
                        {/* </Card> */}
                    </div>
                    <div className={"profileInfo"} style={{display: 'table', tableLayout: 'fixed', width: '100%'}}>

                        <Card className="overflow-hidden healthDashbaordReportIdTables"
                              classes={{root: classes.tableCard}}>
                            <div className="flex p-5">
                                <div className="font-semibold mb-3 w-3/12">Profile Information</div>
                                <div className="searchDataTable w-9/12">
                                    <div
                                        className="border border-gray-300 border-solid flex inputGroup mr-4 px-3 py-1 rounded-full w-7/12 ml-auto">
                                        <input type="text"
                                               className="border-0 flex-1 focus:outline-none font-semibold outline-none px-2 text-xs"
                                               placeholder="Search"
                                               onChange={this.profileValidateDataSearch}
                                        />
                                        <SearchIcon className="text-gray-300"/>
                                    </div>

                                </div>
                            </div>

                            <div>
                                <DataTable
                                    noHeader={true}
                                    wrap={false}
                                    responsive={true}
                                    columns={profileInfo()}
                                    data={profileValidateData}
                                    progressPending={loading}
                                    persistTableHead
                                    pagination
                                    paginationTotalRows={this.state.profileValidateTotalRows}
                                    conditionalRowStyles={conditionalRowStyles}
                                />
                            </div>
                        </Card>
                    </div>
                    <div style={{height : "840px"}}>
                        {
                        this.state.renderGraph &&
                        <>
                            <AreaChart
                                dataChart={this.state.areaChartData}
                                categories={this.state.reportTypeCategories}
                                tooltip={true}
                                dataInformation={reportIdError}
                                data={this.state.reportIdErrorData}
                                dataLoading={this.state.reportIdErrorLoading}
                            />
                            <BarChart
                                categories={this.state.reportTypeCategories}
                                dataChart={this.state.barChartData}
                                tooltip={true}
                                dataInformation={reportLinkError}
                                data={this.state.reportLinkErrorData}
                                dataLoading={this.state.reportLinkErrorLoading}
                            />
                        </>

                    }
                    </div>
                    <div className="pt-5">
                        <Grid container spacing={1}>
                            {
                                this.state.dataDuplicationData.length > 0 ?
                                    <Grid item xs={12} sm={12} md={6} className="flex">
                                        <Card className="overflow-hidden healthDashbaordDataInfoTables" classes={{root: classes.tableCard}}>
                                            <div className="font-semibold mb-3 ml-3 mt-2 w-4/12">Data Information</div>
                                            <div className={clsx("w-full dataTableContainer")}>
                                                <DataTable
                                                    noHeader={true}
                                                    wrap={false}
                                                    responsive={true}
                                                    columns={dataInformation()}
                                                    data={this.state.dataDuplicationData}
                                                    progressPending={this.state.dataDuplicationLoading}
                                                    persistTableHead
                                                    pagination
                                                    paginationTotalRows={this.state.dataDuplicationTotalRows}
                                                />
                                            </div>
                                        </Card>
                                    </Grid>
                                    : ""
                            }
                            {
                                this.state.linkDuplicationData.length > 0 ?
                                    <Grid item xs={12} sm={12} md={6}>
                                        <Card className="overflow-hidden healthDashbaordDataInfoTables" classes={{root: classes.tableCard}}>
                                            <div className="font-semibold mb-3 ml-3 mt-2 w-4/12">Information Links</div>
                                            <div className={clsx("w-full dataTableContainer")}>
                                                <DataTable
                                                    noHeader={true}
                                                    wrap={false}
                                                    responsive={true}
                                                    columns={informationLinks(this.state.healthDate)}
                                                    data={this.state.linkDuplicationData}
                                                    persistTableHead
                                                    pagination
                                                    paginationTotalRows={this.state.linkDuplicationTotalRows}
                                                    progressPending={this.state.linkDuplicationLoading}
                                                />
                                            </div>
                                        </Card>
                                    </Grid>
                                    : ""
                            }

                        </Grid>
                    </div>
                </div>
            </div>

        )
    }
}

export default withStyles(useStyles)(connect(null)(HealthDashboard))