import React, { Component } from 'react'
import "./../Tacos.scss";
import MultiSelect from "../../../../general-components/MultiSelect";
import TextButton from "../../../../general-components/TextButton";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import {withStyles} from "@material-ui/core/styles";
import {primaryColor, primaryColorLight,primaryColorOrange} from "../../../../app-resources/theme-overrides/global";
import {getTacosFilterChildBrand} from '../apiCalls';
import SingleSelect from '../../../../general-components/Select';
import TextFieldInput from '../../../../general-components/Textfield';
import CustomDateRangePicker from '../../Events/CustomDateRangePicker';
import moment from 'moment';
import BidMultiplierDateRangePicker from '../../BidMultiplier/BidMultiplierDateRangePicker';
const customStyle ={
    menu: base => ({
    ...base,
    marginTop: 0
    }),
    control: (base, state) => ({
    background: '#fff',
    height: 30,
    border: "1px solid #c3bdbd8c",
    borderRadius: 20,
    display: 'flex', 
    border: "1px solid #c3bdbd8c", //${primaryColor}
    // This line disable the blue border
    boxShadow: 0,
    '&:hover': {
        border:  "1px solid #c3bdbd8c"
    },
    fontSize: '0.72rem'
    }),
    container: (provided, state) => ({
    ...provided,
    marginTop: 8
    }),
    valueContainer: (provided, state) => ({
    ...provided,
    padding: "0px 8px",
    overflowY: "auto",

    }),
    multiValue: (styles, { data }) => {
    return {
        ...styles,
        borderRadius:25
    };
    },
    multiValueRemove: (styles, { data }) => ({
    ...styles,
    color: data.color,
    ':hover': {
        backgroundColor: primaryColor,
        color: 'white',
        borderRadius: 25
    },
    }),
}
const selectedColumnOptions = [
    {label: "Ad Type", value: 6, className: 'awesome-class'},
    {label: "Catergory", value: 7, className: 'awesome-class'},
    {label: "Tag", value: 8, className: 'awesome-class'},
    {label: "Strategy", value: 9, className: 'awesome-class'},
];
const columnOptions = [
    {label: "Ad Type", value: 6, className: 'awesome-class'},
    {label: "Tag", value: 7, className: 'awesome-class'},
    {label: "Catergory", value: 8, className: 'awesome-class'},
    {label: "Strategy", value: 9, className: 'awesome-class'},
    // {label: "Tacos", value: 10, className: 'awesome-class'},
    // {label: "Min Bid", value: 11, className: 'awesome-class'},
    // {label: "Max Bid", value: 9, className: 'awesome-class'},
    {label: "Start Date", value: 10, className: 'awesome-class'},
];
const adTypeOptions = [
    {label: "ACOS", value: "acos", className: 'awesome-class'},
    {label: "ROAS", value: "roas", className: 'awesome-class'},
];

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
class HistoryFilter extends Component {
    constructor(props){
        super(props);
        this.state = {
            childBrand:null,
            columns:null,
            category:null,
            strategy:null,
            adType:null,
            tags:null,
            showDRP:false,
            dateRangeObj: {
                startDate: new Date(),
                endDate: new Date(),
                key: 'selection',
            },
            selectedDate: "",
            startDrpDate:  "",
            endDrpDate:  "",
            startDate:"",
            showDatePicker:false,
            childBrandOptions:[],
            strategyOptions:[],
            categoryOptions:[],
            strategyOptions:[],
            tagsOptions:[],
            adTypeOptions:adTypeOptions,
            columnsOptions:[],
            loaders:{
                showChildBrandFilterLoader:true,
                showCategoryFilterLoader:true,
                showStrategyFilterLoader:true,
                showAdTypeFilterLoader:true,
                showColumnsFilterLoader:true,
            },
            toggleGroup:false,
        }
    }
    componentDidMount(){
        getTacosFilterChildBrand((response)=>{
            let childBrandOptions = [];
            response.data.childBrands.forEach((obj, idx) => {
                if(obj.ams){
                    childBrandOptions.push({
                        label: (obj.ams.type == "seller") ? obj.ams.name+'-SC' : (obj.ams.type == "vendor") ? obj.ams.name+"-VC" : (obj.ams.type == "agency") ? obj.ams.name+"-AG" :obj.ams.type,
                        value: obj.ams.id,
                        key: idx
                    })
                }
            });
            let tagsOptions = [];
            response.data.tags.forEach((obj, idx) => {
                tagsOptions.push({
                    label:  (obj.tag.length > 50 ? obj.tag.substr(0, 50) + "..." : obj.tag),
                    value: obj.id,
                    key: idx
                })
            });
            let selectedColumns = this.props.columnOptions ?? selectedColumnOptions;
            if(this.props.filter){
                    selectedColumns = [...(this.props.columnOptions ?? columnOptions)].filter((columnOption)=>{
                        return this.props.filter.itemsToShow.includes(columnOption.value);
                    });
            }
            this.setState({
                childBrandOptions,
                childBrand:this.props.filter ? this.props.filter.childBrand : null,
                category:this.props.filter ? this.props.filter.category : null,
                strategy:this.props.filter ? this.props.filter.strategy : null,
                adType:this.props.filter ? this.props.filter.adType : null,
                columns: selectedColumns,
                columnsOptions:this.props.columnOptions ?? columnOptions,
                tagsOptions,
                loaders:{
                    showChildBrandFilterLoader:false,
                    showCategoryFilterLoader:false,
                    showStrategyFilterLoader:false,
                    showAdTypeFilterLoader:false,
                    showColumnsFilterLoader:false,
                }
            },()=>{
                $(".childbrands .select__value-container").animate({
                    scrollTop: $('.childbrands .select__value-container').get(0).scrollHeight
                });
                // $(".tags .select__value-container").animate({
                //     scrollTop: $('.tags .select__value-container').get(0).scrollHeight
                // });
                $(".columns .select__value-container").animate({
                    scrollTop: $('.columns .select__value-container').get(0).scrollHeight
                });
            })
        }, (error)=>{
            console.log(error)
        });
    }
    onColumnSelectorChangeHandler = (value) => {
        this.setState({
            columns: value
        });
        $(".columns .select__value-container").clearQueue().animate({
            scrollTop: $('.columns .select__value-container').get(0).scrollHeight
        });
    }
    handleSingleSelectChange = (value, element) => {
        console.log("element.name", value, element.name)
        const name = element.name;
        this.setState({
            [name]: value,
        }, ()=>{
            // this.props.applyFilterOnTable({
            //     [name]: value,
            // })
        })
    }
    handleApplyFilterButtonClick = (e) =>{
        let itemsToShow = this.state.columns ? this.state.columns.map((column)=>parseInt(column.value)): [];
        if(this.props.hasDateRangePicker){
            let startDrpDate = this.state.startDrpDate != "" ? moment(this.state.startDrpDate).format('YYYY-MM-DD') : this.state.startDrpDate;
            let endDrpDate = this.state.endDrpDate != "" ? moment(this.state.endDrpDate).format('YYYY-MM-DD') : this.state.endDrpDate;
             this.props.applyFilterOnTable({
                childBrand: this.state.childBrand,
                category: this.state.category,
                strategy: this.state.strategy,
                adType: this.state.adType,
                tag: this.state.tags,
                startDrpDate,
                endDrpDate,
                itemsToShow
            })
            return;
        }
        this.props.applyFilterOnTable({
            childBrand: this.state.childBrand,
            category: this.state.category,
            strategy: this.state.strategy,
            adType: this.state.adType,
            tag: this.state.tags,
            startDate: this.state.startDate === "" ? this.state.startDate : moment(this.state.startDate).format('YYYY-MM-DD'),
            itemsToShow
        })
    }
    handleClearFilter = (e) =>{
        this.setState({
            childBrand:null,
            category:null,
            strategy:null,
            adType:null,
            tags:null,
            startDrpDate:"",
            endDrpDate:"",
            selectedDate:"",
            dateRangeObj: {
                startDate: new Date(),
                endDate: new Date(),
                key: 'selection',
            },
            columns:this.props.columnOptions ?? selectedColumnOptions
        },()=>{
            this.props.relaodDatatable()
            this.props.applyFilterOnTable({
                childBrand:null,
                category:null,
                strategy:null,
                adType:null,
                tags:null,
                startDrpDate:"",
                endDrpDate:"",
                selectedDate:"",
                dateRangeObj: {
                    startDate: new Date(),
                    endDate: new Date(),
                    key: 'selection',
                },
                itemsToShow:this.state.columns ? this.state.columns.map((column)=>parseInt(column.value)): []
            })
        })  
    }
    handleOnDateClick = (e) => {
        this.setState({
            showDatePicker:true
        })
    }
    handleSingleDateChange = (startDate) => {
        this.setState({
            startDate,
            showDatePicker: false
        })
    }
    helperCloseDP = (event) => {
        this.setState({
            showDatePicker: false
        })
    }
    
    helperCloseDRP = () => {
        this.setState({
            showDRP: false
        })
    }
    onDateChange = (range) => {

        let startDrpDate = moment(range.startDate).format('l');
        let endDrpDate = moment(range.endDate).format('l');

        this.setState({
            startDrpDate,
            endDrpDate,
            selectedDate: startDrpDate + " - " + endDrpDate,
            showDRP: false
        })
    }
    handleOnDateRangeClick = () => {
        this.setState({
            showDRP: true
        })
    }

    render() {
        return (
            <div className="flex flex-wrap h-56 productTableFilter HistoryTableFilter px-10 py-5">
                <div className="w-4/12 md:pr-10 pr-5 childbrands">
                    <div>
                        <label className="text-xs font-normal ml-2">
                        Select Child Brand
                        </label>
                        <SingleSelect
                            placeholder="Child Brands"
                            name="childBrand"
                            id="childBrand"
                            value={this.state.childBrand}
                            onChangeHandler = {this.handleSingleSelectChange}
                            fullWidth={true}
                            Options={this.state.childBrandOptions}
                            styles={customStyle}
                            customClassName="mr-5 ThemeSelect"
                            isLoading={this.state.loaders.showChildBrandFilterLoader}
                            // menuIsOpen
                        />
                    </div>
                </div>
                <div className="w-4/12 md:pr-10 category">
                    <div>
                        <label className="text-xs font-normal ml-2">
                        Select Category
                        </label>
                        <SingleSelect
                            placeholder="Category"
                            name="category"
                            id="category"
                            value={this.state.category}
                            onChangeHandler = {this.handleSingleSelectChange}
                            fullWidth={true}
                            Options={this.state.categoryOptions}
                            styles={customStyle}
                            customClassName="mr-5 ThemeSelect"
                            isLoading={this.state.loaders.showCategoryFilterLoader}
                            // menuIsOpen
                        />
                    </div>
                </div>
                
                <div className="w-4/12 md:pr-10 strategy">
                    <div>
                        <label className="text-xs font-normal ml-2">
                        Select Strategy
                        </label>
                        <SingleSelect
                            placeholder="Strategy"
                            name="strategy"
                            id="strategy"
                            value={this.state.strategy}
                            onChangeHandler = {this.handleSingleSelectChange}
                            fullWidth={true}
                            Options={this.state.strategyOptions}
                            styles={customStyle}
                            customClassName="mr-5 ThemeSelect"
                            isLoading={this.state.loaders.showStrategyFilterLoader}
                            // menuIsOpen
                        />
                    </div>
                </div>
                
                <div className="w-4/12 md:pr-10 category">
                    <div>
                        <label className="text-xs font-normal ml-2">
                        Select Metric Type
                        </label>
                        <SingleSelect
                            placeholder="Metric Type"
                            name="adType"
                            id="adType"
                            value={this.state.adType}
                            onChangeHandler = {this.handleSingleSelectChange}
                            fullWidth={true}
                            Options={this.state.adTypeOptions}
                            styles={customStyle}
                            customClassName="mr-5 ThemeSelect"
                            isLoading={this.state.loaders.showAdTypeFilterLoader}
                            // menuIsOpen
                        />
                    </div>
                </div>
                <div className="w-4/12 columns">
                    <div>
                        <label className="text-xs font-normal ml-2">
                            Add/Remove Column
                        </label>
                        <MultiSelect
                            placeholder="Columns"
                            name="columns"
                            id="columns"
                            value={this.state.columns}
                            onChangeHandler = {this.onColumnSelectorChangeHandler}
                            fullWidth={true}
                            Options={this.state.columnsOptions}
                            styles={customStyle}
                            customClassName="mr-5 ThemeSelect"
                            isLoading={this.state.loaders.showColumnsFilterLoader}
                            // menuIsOpen
                        />
                    </div>
                    
                </div>
                <div className="w-4/12 md:pr-10 category">
                    <div>
                        <label className="text-xs font-normal ml-2">
                        Select Tag
                        </label>
                        <SingleSelect
                            placeholder="Tags"
                            name="tags"
                            id="tags"
                            value={this.state.tags}
                            onChangeHandler = {this.handleSingleSelectChange}
                            fullWidth={true}
                            Options={this.state.tagsOptions}
                            styles={customStyle}
                            customClassName="mr-5 ThemeSelect"
                            isLoading={this.state.loaders.showTagsFilterLoader}
                            // menuIsOpen
                        />
                    </div>
                </div>
                
                <div className="w-4/12 md:pr-10 category ">
                    {
                        this.props.hasDateRangePicker ?
                        <DateRangePickerHistoryFilter 
                        showDRP={this.state.showDRP}
                        selectedDate={this.state.selectedDate}
                        dateRangeObj={this.state.dateRangeObj}
                        handleOnDateRangeClick={this.handleOnDateRangeClick}
                        onDateChange={this.onDateChange}
                        helperCloseDRP={this.helperCloseDRP}
                        />
                        :
                        <SingleDatePicker 
                            handleOnDateClick={this.handleOnDateClick}
                            selectedDate={this.state.startDate === "" ? new Date() : this.state.startDate}
                            inputValue={this.state.startDate === "" ? this.state.startDate : moment(this.state.startDate).format('YYYY-MM-DD')}
                            classes={this.props.classes}
                            showDatePicker={this.state.showDatePicker}
                            helperCloseDP={this.helperCloseDP}
                            handleSingleDateChange={this.handleSingleDateChange}
                        />
                    }
                </div>
                <div className="w-4/12 md:pr-10 category">
                    <div className="flex flex-col pb-5 w-3/12">
                            <TextButton
                            BtnLabel={"Reset all"}
                            color="primary"
                            styles={{paddingRight:0, paddingLeft:0, outline:"none", width:"100%"}}
                            onClick={this.handleClearFilter}
                            ></TextButton>
                            <PrimaryButton
                                btnlabel={"Apply"}
                                variant={"contained"}
                                onClick={this.handleApplyFilterButtonClick}/> 
                    </div>
                </div>
            </div>
        )
    }
}

export default withStyles(useStyles)(HistoryFilter)

function SingleDatePicker({
    handleOnDateClick,
    selectedDate="",
    inputValue,
    classes,
    showDatePicker,
    helperCloseDP,
    handleSingleDateChange,
}) {
    return (
        <>
            <label className="inline-block ml-2 text-sm">
                Start Date
            </label>
            <div className="relative">
                <div onClick={handleOnDateClick}>
                    <TextFieldInput
                        placeholder="Start Date"
                        type="text"
                        name={"startDate"}
                        value={inputValue}
                        fullWidth={true}
                        classesstyle={classes}
                    />
                </div>
                {
                    showDatePicker ?
                        <CustomDateRangePicker 
                            helperCloseDRP={helperCloseDP}
                            setSingleDate={handleSingleDateChange}
                            date={selectedDate}
                            direction="vertical"
                            isDateRange={false} 
                        />
                        : 
                        null
                }

            </div>
            
        </>

    )
}

function DateRangePickerHistoryFilter({
    showDRP,
    handleOnDateRangeClick,
    selectedDate,
    dateRangeObj,
    onDateChange,
    helperCloseDRP,
}) {
    return (
        <div>
            <label className="text-xs font-normal ml-2">
                Date Range
            </label>
            <div onClick={handleOnDateRangeClick} className={"mr-5 bidMultiplierDateRange"}>
                <TextFieldInput
                    placeholder="Date Range"
                    type="text"
                    value={selectedDate}
                    fullWidth={true}
                    customclassname="mr-5 ThemeSelect"
                />
            </div>
            <div className={`absolute z-50`}>
                {
                    showDRP ?
                        <BidMultiplierDateRangePicker
                            range={dateRangeObj}
                            getValue={onDateChange}
                            helperCloseDRP={helperCloseDRP}
                            direction="horizontal"
                        />
                        : null
                }
            </div>
        </div>
    )
}
