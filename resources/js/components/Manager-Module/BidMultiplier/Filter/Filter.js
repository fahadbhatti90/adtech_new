import React, {Component} from "react";
import SingleSelect from "../../../../general-components/Select";
import MultiSelect from "../../../../general-components/MultiSelect";
import TextButton from "../../../../general-components/TextButton";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import {getBidMultiplierFilterChildBrand} from "../apiCalls";
import customStyle from '../FilterSingleStyling';

const selectedColumnOptions = [
    {label: "Status", value: 3, className: 'awesome-class'},
    {label: "Category", value: 4, className: 'awesome-class'},
    {label: "Strategy", value: 5, className: 'awesome-class'},
];
const columnOptions = [
    {label: "Status", value: 3, className: 'awesome-class'},
    {label: "Category", value: 4, className: 'awesome-class'},
    {label: "Strategy", value: 5, className: 'awesome-class'},
];
const statusOptions = [
    {label: "archived", value: "archived", className: 'awesome-class'},
    {label: "enabled", value: "enabled", className: 'awesome-class'},
    {label: "paused", value: "paused", className: 'awesome-class'},
];

export class Filter extends Component {
    constructor(props) {
        super(props);
        this.state = {
            childBrand: null,
            columns: null,
            status: null,
            childBrandOptions: [],
            statusOptions: statusOptions,
            columnsOptions: [],
            loaders: {
                showChildBrandFilterLoader: true,
                showStatusFilterLoader: true,
                showColumnsFilterLoader: true,
            },
            toggleGroup: false,
        }
    }

    componentDidMount() {
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
                    showCategoryFilterLoader: false,
                    showStrategyFilterLoader: false,
                    showStatusFilterLoader: false,
                    showColumnsFilterLoader: false,
                }
            }, () => {
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
        }, (error) => {
            console.log(error)
        });
    }

    handleApplyFilterButtonClick = (e) => {
        let itemsToShow = this.state.columns ? this.state.columns.map((column) => parseInt(column.value)) : [];

        this.props.applyFilterOnTable({
            childBrand: this.state.childBrand,
            category: this.state.category,
            strategy: this.state.strategy,
            status: this.state.status,
            itemsToShow
        })
    }

    handleClearFilter = (e) => {
        this.setState({
            childBrand: null,
            category: null,
            strategy: null,
            status: null,
            columns: selectedColumnOptions
        }, () => {
            this.props.reloadDatatable()
            this.props.applyFilterOnTable({
                childBrand: null,
                category: null,
                strategy: null,
                status: null,
                itemsToShow: [3, 4, 5, 6]
            })
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
    render() {
        return (
            <>
                <div className="flex flex-wrap h-56 productTableFilter px-10 py-5">
                    <div className="w-1/2 childbrands">
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
                                customClassName="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showChildBrandFilterLoader}
                                // menuIsOpen
                            />
                        </div>
                    </div>
                    <div className="w-1/2">
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
                                customClassName="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showStatusFilterLoader}
                                // menuIsOpen
                            />
                        </div>
                    </div>
                    <div className="w-1/2 columns">
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
                                customClassName="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showColumnsFilterLoader}
                                // menuIsOpen
                            />
                        </div>

                    </div>
                    <div className="w-1/3">
                        <div className="flex flex-col pb-5 w-3/12">
                            <TextButton
                                btntext={"Reset all"}
                                color="primary"
                                styles={{paddingRight: 0, paddingLeft: 0, outline: "none", width: "100%"}}
                                onClick={this.handleClearFilter}
                            ></TextButton>
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