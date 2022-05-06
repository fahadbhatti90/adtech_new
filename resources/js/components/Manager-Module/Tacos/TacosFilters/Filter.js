import React, { Component } from 'react'
import "./../Tacos.scss";
import MultiSelect from "../../../../general-components/MultiSelect";
import TextButton from "../../../../general-components/TextButton";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import {primaryColor} from "../../../../app-resources/theme-overrides/global";
import {getTacosFilterChildBrand} from '../apiCalls';
import SingleSelect from '../../../../general-components/Select';
const customStyle ={
    menu: base => ({
    ...base,
    marginTop: 0
    }),
    control: (base, state) => ({
    background: '#fff',
    height: 30,
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
    {label: "Status", value: 3, className: 'awesome-class'},
    {label: "Catergory", value: 4, className: 'awesome-class'},
    {label: "Strategy", value: 5, className: 'awesome-class'},
];
const columnOptions = [
    {label: "Status", value: 3, className: 'awesome-class'},
    {label: "Catergory", value: 4, className: 'awesome-class'},
    {label: "Strategy", value: 5, className: 'awesome-class'},
];
const statusOptions = [
    {label: "archived", value: "archived", className: 'awesome-class'},
    {label: "enabled", value: "enabled", className: 'awesome-class'},
    {label: "paused", value: "paused", className: 'awesome-class'},
];
export default class Filter extends Component {
    constructor(props){
        super(props);
        this.state = {
            childBrand:null,
            columns:null,
            category:null,
            strategy:null,
            status:null,
            childBrandOptions:[],
            strategyOptions:[],
            categoryOptions:[],
            statusOptions:statusOptions,
            columnsOptions:[],
            loaders:{
                showChildBrandFilterLoader:true,
                showCategoryFilterLoader:true,
                showStrategyFilterLoader:true,
                showStatusFilterLoader:true,
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
            let selectedColumns = selectedColumnOptions;
            if(this.props.filter){
                    selectedColumns = columnOptions.filter((columnOption)=>{
                        return this.props.filter.itemsToShow.includes(columnOption.value);
                    });
            }
            this.setState({
                childBrandOptions,
                childBrand:this.props.filter ? this.props.filter.childBrand : null,
                category:this.props.filter ? this.props.filter.category : null,
                strategy:this.props.filter ? this.props.filter.strategy : null,
                status:this.props.filter ? this.props.filter.status : null,
                columns: selectedColumns,
                columnsOptions:columnOptions,
                loaders:{
                    showChildBrandFilterLoader:false,
                    showCategoryFilterLoader:false,
                    showStrategyFilterLoader:false,
                    showStatusFilterLoader:false,
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
        this.props.applyFilterOnTable({
            childBrand: this.state.childBrand,
            category: this.state.category,
            strategy: this.state.strategy,
            status: this.state.status,
            itemsToShow
        })
    }
    handleClearFilter = (e) =>{
        this.setState({
            childBrand:null,
            category:null,
            strategy:null,
            status:null,
            columns:selectedColumnOptions
        },()=>{
            this.props.relaodDatatable()
            this.props.applyFilterOnTable({
                childBrand:null,
                category:null,
                strategy:null,
                status:null,
                itemsToShow:[3, 4, 5, 6]
            })
        })  
    }
    render() {
        return (
            <div className="flex flex-wrap h-56 productTableFilter px-10 py-5">
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
                        Select Status
                        </label>
                        <SingleSelect
                            placeholder="Status"
                            name="status"
                            id="status"
                            value={this.state.status}
                            onChangeHandler = {this.handleSingleSelectChange}
                            fullWidth={true}
                            Options={this.state.statusOptions}
                            styles={customStyle}
                            customClassName="mr-5 ThemeSelect"
                            isLoading={this.state.loaders.showStatusFilterLoader}
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
