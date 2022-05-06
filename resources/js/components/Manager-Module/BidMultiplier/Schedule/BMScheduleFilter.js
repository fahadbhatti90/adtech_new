import React, {Component} from 'react'
import {withStyles} from "@material-ui/core/styles";
import {getBidMultiplierFilterChildBrand} from "../apiCalls";
import { primaryColorLight, primaryColorOrange} from "../../../../app-resources/theme-overrides/global";
import customStyle from '../FilterSingleStyling';
import SingleSelect from "../../../../general-components/Select";
import MultiSelect from "../../../../general-components/MultiSelect";
import TextButton from "../../../../general-components/TextButton";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import TextFieldInput from "../../../../general-components/Textfield";
import BidMultiplierDateRangePicker from "../BidMultiplierDateRangePicker";
import moment from "moment";
import '../bidMultiplier.scss';


const useStyles = theme => ({
    root: {
        '& .MuiInputBase-root': {
            borderRadius: 10,
            border: "1px solid #c3bdbd8c !important",
            height: 30,
            background: '#fff'
        },
        "&:hover .MuiInputBase-root": {
            borderColor: primaryColorOrange,
            borderRadius: "12px",
        },
        '& .MuiInputBase-input': {
            margin: props => props.margin || 15,
            fontSize: '0.72rem',
            padding: '7px 0 7px'
        }
    },
    focused: {
        border: "2px solid !important",
        borderColor: `${primaryColorLight} !important`,
    }
});

const selectedColumnOptions = [
    {label: "Status", value: 5, className: 'awesome-class'},
    {label: "Category", value: 6, className: 'awesome-class'},
    {label: "Strategy", value: 7, className: 'awesome-class'},
];
const columnOptions = [
    {label: "Status", value: 5, className: 'awesome-class'},
    {label: "Category", value: 6, className: 'awesome-class'},
    {label: "Strategy", value: 7, className: 'awesome-class'},
];
const statusOptions = [
    {label: "archived", value: "archived", className: 'awesome-class'},
    {label: "enabled", value: "enabled", className: 'awesome-class'},
    {label: "paused", value: "paused", className: 'awesome-class'},
];

class BMScheduleFilter extends Component {
    constructor(props) {
        super(props);
        this.state = {
            childBrand: null,
            columns: null,
            status: null,
            childBrandOptions: [],
            statusOptions: statusOptions,
            columnsOptions: [],
            showDRP:false,
            dateRangeObj: {
                startDate: new Date(),
                endDate: new Date(),
                key: 'selection',
            },
            selectedDate: "",
            startDate:  "",
            endDate:  "",
            loaders: {
                showChildBrandFilterLoader: true,
                showStatusFilterLoader: true,
                showColumnsFilterLoader: true,
            }
        }
    }

    componentDidMount() {
        this.handleClearFilter()
        getBidMultiplierFilterChildBrand((response) => {
            let childBrandOptions = [];
            response.data.childBrands.forEach((obj, idx) => {
                if (obj.ams) {
                    childBrandOptions.push({
                        label: (obj.ams.type == "seller") ? obj.ams.name+'-SC' : (obj.ams.type == "vendor") ? obj.ams.name+"-VC" : (obj.ams.type == "agency") ? obj.ams.name+"-AG" :obj.ams.type,
                        value: obj.ams.id,
                        key: idx
                    })
                }
            });

            let selectedColumns = selectedColumnOptions;

            if (this.props.filter) {
                selectedColumns = columnOptions.filter((columnOption) => {
                    return this.props.filter.itemsToShow.includes(columnOption.value);
                });
            }

            this.setState({
                childBrandOptions,
                childBrand: this.props.filter ? this.props.filter.childBrand : null,
                category: this.props.filter ? this.props.filter.category : null,
                strategy: this.props.filter ? this.props.filter.strategy : null,
                status: this.props.filter ? this.props.filter.status : null,
                columns: selectedColumns,
                columnsOptions: columnOptions,
                loaders: {
                    showChildBrandFilterLoader: false,
                    showStatusFilterLoader: false,
                    showColumnsFilterLoader: false,
                }
            }, () => {
                $(".childbrands .select__value-container").animate({
                    scrollTop: $('.childbrands .select__value-container').get(0).scrollHeight
                });

                $(".columns .select__value-container").animate({
                    scrollTop: $('.columns .select__value-container').get(0).scrollHeight
                });
            })
        }, (error) =>{
            console.log('history bid multiplier filter error message');
        })
    }

    helperCloseDRP = () => {
        this.setState({
            showDRP: false
        })
    }

    onDateChange = (range) => {

        let startDate = moment(range.startDate).format('l');
        let endDate = moment(range.endDate).format('l');

        this.setState({
            startDate: startDate,
            endDate: endDate,
            selectedDate: startDate + " - " + endDate,
            showDRP: false
        })
    }
    handleOnDateRangeClick = () => {
        this.setState({
            showDRP: true
        })
    }

    handleSingleSelectChange = (value, element) => {

        const name = element.name;
        this.setState({
            [name]: value,
        })
    }

    onColumnSelectorChangeHandler = (value) => {
        this.setState({
            columns: value
        });
        $(".columns .select__value-container").clearQueue().animate({
            scrollTop: $('.columns .select__value-container').get(0).scrollHeight
        });
    }

    handleApplyFilterButtonClick = (e) =>{
        let itemsToShow = this.state.columns ? this.state.columns.map((column) => parseInt(column.value)) : [];
        let startDate = this.state.startDate != "" ? moment(this.state.startDate).format('YYYY-MM-DD') : this.state.startDate;
        let endDate = this.state.endDate != "" ? moment(this.state.endDate).format('YYYY-MM-DD') : this.state.endDate;
        this.props.applyFilterOnTable({
            childBrand: this.state.childBrand,
            category: this.state.category,
            strategy: this.state.strategy,
            status: this.state.status,
            startDate: startDate,
            endDate: endDate,
            itemsToShow
        })
    }

    handleClearFilter = (e) => {
        this.setState({
            childBrand: null,
            category: null,
            strategy: null,
            status: null,
            selectedDate: "",
            startDate: "",
            endDate: "",
            columns: selectedColumnOptions
        }, () => {
            this.props.reloadDatatable()
            this.props.applyFilterOnTable({
                childBrand: null,
                category: null,
                strategy: null,
                status: null,
                itemsToShow: [ 5, 6, 7],
                selectedDate: "",
            })
        })
    }
    render() {
        return (
            <>
                <div className="flex flex-wrap h-56 productTableFilter px-10 py-5">
                    <div className="w-1/3 childbrands">
                        <div>
                            <label className="text-xs font-normal ml-2">
                                Select Child Brand
                            </label>
                            <SingleSelect
                                placeholder="Child Brands"
                                name="childBrand"
                                id="childBrand"
                                value={this.state.childBrand}
                                onChangeHandler={this.handleSingleSelectChange}
                                fullWidth={true}
                                Options={this.state.childBrandOptions}
                                styles={customStyle}
                                customclassname="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showChildBrandFilterLoader}
                                // menuIsOpen
                            />
                        </div>
                    </div>
                    <div className="w-1/3">
                        <div>
                            <label className="text-xs font-normal ml-2">
                                Select Status
                            </label>
                            <SingleSelect
                                placeholder="Status"
                                name="status"
                                id="status"
                                value={this.state.status}
                                onChangeHandler={this.handleSingleSelectChange}
                                fullWidth={true}
                                Options={this.state.statusOptions}
                                styles={customStyle}
                                customclassname="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showStatusFilterLoader}
                                // menuIsOpen
                            />
                        </div>
                    </div>
                    <div className="w-1/3 columns">
                        <div>
                            <label className="text-xs font-normal ml-2">
                                Add/Remove Column
                            </label>
                            <MultiSelect
                                placeholder="Columns"
                                name="columns"
                                id="columns"
                                value={this.state.columns}
                                onChangeHandler={this.onColumnSelectorChangeHandler}
                                fullWidth={true}
                                Options={this.state.columnsOptions}
                                styles={customStyle}
                                customclassname="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showColumnsFilterLoader}
                                // menuIsOpen
                            />
                        </div>

                    </div>
                    <div className="w-1/3 columns">
                        <div>
                            <label className="text-xs font-normal ml-2">
                                Date Range
                            </label>
                            <div onClick={this.handleOnDateRangeClick} className={"mr-5 bidMultiplierDateRange"}>
                                <TextFieldInput
                                    placeholder="Date Range"
                                    type="text"
                                    value={this.state.selectedDate}
                                    fullWidth={true}
                                    customclassname="mr-5 ThemeSelect"
                                />
                            </div>
                            <div className={`absolute z-50`}>
                                {
                                    this.state.showDRP ?
                                        <BidMultiplierDateRangePicker
                                            range={this.state.dateRangeObj}
                                            getValue={this.onDateChange}
                                            helperCloseDRP={this.helperCloseDRP}
                                            direction="horizontal"
                                        />
                                        : null
                                }
                            </div>
                        </div>

                    </div>
                    <div className="w-1/3">
                        <div className="flex flex-col pb-5 w-3/12">
                            <TextButton
                                btntext={"Reset all"}
                                color="primary"
                                styles={{paddingRight: 0, paddingLeft: 0, outline: "none", width: "100%"}}
                                onClick={this.handleClearFilter}
                            />
                            <PrimaryButton
                                btnlabel={"Apply"}
                                variant={"contained"}
                                onClick={this.handleApplyFilterButtonClick}/>
                        </div>
                    </div>

                </div>
            </>
        )
    }
}

export default withStyles(useStyles)(BMScheduleFilter)