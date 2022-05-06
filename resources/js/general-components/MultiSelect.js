import React, { Component } from "react";
import Select, { components } from "react-select";
import clsx from 'clsx';
import {primaryColor,primaryColorLight} from "../app-resources/theme-overrides/global"
import { styles } from "./styles";
import { withStyles } from "@material-ui/core/styles";

class MultiSelect extends Component {
  state = {};
  onInputChange = (
    inputValue,
    options
  ) => {
    
    switch (options.action) {
      case 'input-change':
        {
          this.setState({
            inputValue,
          });
        return inputValue;
        }
      case 'menu-close':
        let menuIsOpen = undefined;
        if (options.prevInputValue) {
          menuIsOpen = true;
        }
        this.setState({
          menuIsOpen,
          
        });
        return options.prevInputValue;
      default:
        return options.prevInputValue;
    }
  };
  render() {
		const { classes } = this.props;    
    return (
      <Select
        className={clsx("basic-multi-select",this.props.customClassName ? this.props.customClassName : this.props.customclassname )}
        classNamePrefix="select"
        name={this.props.name}
        options={this.props.Options}
        closeMenuOnSelect={this.props.closeMenuOnSelect?true:false}
        isDisabled={this.props.isDisabled || false}
        isMulti
        isClearable={true}
        isSearchable
        inputValue={this.state.inputValue}
        onInputChange={this.onInputChange}
        components={{
          IndicatorSeparator: () => null,
        }}
        onMenuClose={()=>{ this.setState({
          inputValue:""
        })}}
        onChange={(e)=>this.props.onChangeHandler(e,this.props.name)}
        value={this.props.value}
        placeholder={this.props.placeholder}
        menuIsOpen={this.state.menuIsOpen}
        styles={
          this.props.styles?this.props.styles:
          {
          menu: base => ({
            ...base,
            marginTop: 0
          }),
          control: (base, state) => ({
            background: '#fafafa',
            height: 30,
           // border: "1px solid #ccc2c2",
            borderRadius: 25,
            display: 'flex', 
            border: state.isFocused ? "2px solid "+primaryColor : "1px solid #ccc2c2", //${primaryColor}
            // This line disable the blue border
            boxShadow: state.isFocused ? 0 : 0,
            '&:hover': {
               border:  state.isFocused ?"2px solid "+primaryColor:"1px solid "+primaryColorLight
            },
            // fontSize: '1rem'
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
          placeholder: (defaultStyles) => {
            return {
                ...defaultStyles,
               fontSize: '0.73rem' 
            }
        }
        }}
        {...this.props}
      />
    );
  }
}
export default withStyles(styles)(MultiSelect);