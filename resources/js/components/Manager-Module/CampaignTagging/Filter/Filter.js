import React, {Component} from "react";
import SingleSelect from "../../../../general-components/Select";
import MultiSelect from "../../../../general-components/MultiSelect";
import TextButton from "../../../../general-components/TextButton";
import PrimaryButton from "../../../../general-components/PrimaryButton";
import {primaryColor} from "../../../../app-resources/theme-overrides/global";
import {getCampaignTagFilterData, getCampaignNamesTagging} from "../apiCalls";

const customStyle = {
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
            border: "1px solid #c3bdbd8c"
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
    multiValue: (styles, {data}) => {
        return {
            ...styles,
            borderRadius: 25
        };
    },
    multiValueRemove: (styles, {data}) => ({
        ...styles,
        color: data.color,
        ':hover': {
            backgroundColor: primaryColor,
            color: 'white',
            borderRadius: 25
        },
    }),
}

export class Filter extends Component {
    constructor(props) {
        super(props);
        this.state = {
            childBrand: null,
            childBrandOptions: [],
            campaignName: null,
            campaignNamesOptions: [],
            tag: null,
            tagOptions: [],
            columns: "",
            loaders: {
                showChildBrandFilterLoader: true,
                showCampaignNameFilterLoader: false,
                showTagFilterLoader: true,
            },
        }
    }

    componentDidMount() {
        getCampaignTagFilterData((response) => {

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

            let tagOptions = []
            if (response.data.getAllTags.data.length > 0) {
                response.data.getAllTags.data.forEach((obj2, index2) => {
                    tagOptions.push({
                        label: (obj2.tag.length > 50 ? obj2.tag.substr(0, 50) + "..." : obj2.tag),
                        value: obj2.id,
                        key: index2
                    })
                })
            }

            this.setState({
                childBrandOptions,
                //campaignNamesOptions,
                tagOptions,
                childBrand: this.props.filter ? this.props.filter.childBrand : null,
                loaders: {
                    showChildBrandFilterLoader: false,
                    showCampaignNameFilterLoader: false,
                    showTagFilterLoader: false,
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
        let tag = this.state.tag ? this.state.tag.map((tag) => parseInt(tag.value)) : [];
        let campaignName = this.state.campaignName ? this.state.campaignName.map((campaignName) => parseInt(campaignName.value)) : [];

        this.props.applyFilterOnTable({
            childBrand: this.state.childBrand,
            tag,
            campaignName,
            itemsToShow
        })
    }

    handleClearFilter = (e) => {
        this.setState({
            childBrand: null,
            tag: null,
            campaignName: null,
        }, () => {
            this.props.reloadDataTableCampaignTag()
            this.props.applyFilterOnTable({
                childBrand: null,
                tag: null,
                campaignName: null
            })
        })
    }

    handleSingleSelectChange = (value, element) => {
        if (value && value.length == 0) {
            value = null
        }
        const name = element.name;
        this.setState({
            [name]: value,
            campaignNamesOptions:[],
            campaignName: null,
            loaders: {
                showCampaignNameFilterLoader: true,
            }
        }, () => {
            this.getCampaignNames();
        })
    }

    getCampaignNames = () => {

        let fkProfileId = this.state.childBrand

        if (fkProfileId != null){
            getCampaignNamesTagging(fkProfileId, (response) => {
                let campaignNamesOptions = []
                if (response.getCampaignNames.length > 0) {
                    response.getCampaignNames.forEach((obj1, index1) => {
                        campaignNamesOptions.push({
                            label: (obj1.name.length > 50 ? obj1.name.substr(0, 50) + "..." : obj1.name),
                            value: obj1.id,
                            key: index1
                        })
                    })
                }
                this.setState({
                    campaignNamesOptions,
                    loaders: {
                        showCampaignNameFilterLoader: false,
                    }
                })
            })
        }
    }
    onColumnSelectorChangeHandler = (value) => {
        this.setState({
            campaignName: value
        })
        $(".columns .select__value-container").clearQueue().animate({
            scrollTop: $('.columns .select__value-container').get(0).scrollHeight
        });
    }
    onTagSelectorChangeHandler = (value) => {
        this.setState({
            tag: value
        });
        $(".tags .select__value-container").clearQueue().animate({
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

                    <div className="w-1/2 columns">
                        <div>
                            <label className="text-xs font-normal ml-2">
                                Campaign Names
                            </label>
                            <MultiSelect
                                placeholder="Campaign Names"
                                name="campaignName"
                                id="campaignName"
                                value={this.state.campaignName}
                                onChangeHandler={this.onColumnSelectorChangeHandler}
                                fullWidth={true}
                                Options={this.state.campaignNamesOptions}
                                styles={customStyle}
                                customClassName="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showCampaignNameFilterLoader}
                                // menuIsOpen
                            />
                        </div>

                    </div>
                    <div className="w-1/2 tags">
                        <div>
                            <label className="text-xs font-normal ml-2">
                                Tags
                            </label>
                            <MultiSelect
                                placeholder="Tags"
                                name="tags"
                                id="tags"
                                value={this.state.tag}
                                onChangeHandler={this.onTagSelectorChangeHandler}
                                fullWidth={true}
                                Options={this.state.tagOptions}
                                styles={customStyle}
                                customClassName="mr-5 ThemeSelect"
                                isLoading={this.state.loaders.showTagFilterLoader}
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