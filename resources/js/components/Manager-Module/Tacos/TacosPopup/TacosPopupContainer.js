import React, { useState, useEffect } from 'react';

import RadioGroup from '@material-ui/core/RadioGroup';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import ThemeRadioButton from '../../../../general-components/ThemeRadioButtons/ThemeRadioButton';
import {IsTextSelected} from './../TacosHelper'
export default function TacosPopupContainer(props) {
    return (
        <div className="flex">
            <div className="w-5/12 flex centerChild">
                <TacosMatrix 
                    getInput={props.getInput}
                    metric={props.tacosData.metric}
                />
            </div>
            <div className="centerChild flex w-2/12">
                <Tacos 
                    className="tacosPercentageInput"
                    getInput={props.getInput}
                    tacos={props.tacosData.tacos}
                    handleAddTacosOnKeyUp={props.handleAddTacosOnKeyUp}
                />
            </div>
            <div className="w-5/12  ml-3">
                <TacosBidRange 
                    getInput={props.getInput}
                    min={props.tacosData.min}
                    max={props.tacosData.max}
                    handleAddTacosOnKeyUp={props.handleAddTacosOnKeyUp}
                />
            </div>
        </div>
    )
}


function TacosMatrix(props) {
    const [selectedMatrix, setSelectedMatrix] = useState("acos");

    useEffect(() => {
        props.metric.length > 0 && setSelectedMatrix(props.metric);
    }, [props.metric])
    const handleMatixChange = (e) => {
        setSelectedMatrix(e.target.value);
        props.getInput && props.getInput("metric",e.target.value);
    }

    return (
            <RadioGroup row aria-label="position" name="position" defaultValue="top" className="taxosMatrix inline-flex">
                    <FormControlLabel
                        value="top"
                        className="ml-0"
                        control={<ThemeRadioButton 
                                    checked={selectedMatrix === 'acos'}
                                    onChange={(handleMatixChange)}
                                    value={"acos"}
                                    name="ACOS"
                                    size="small" />
                                }
                        label="ACOS"
                    />

                <div className="ml-5">
                    <FormControlLabel
                        value="top"
                        control={<ThemeRadioButton 
                                    checked={selectedMatrix === 'roas'}
                                    onChange={handleMatixChange}
                                    value={"roas"}
                                    name="ROAS" />
                                }
                        label="ROAS"
                    />
                </div>
            </RadioGroup>
    )
}

function Tacos(props){
    return (
        <div className={`centerChild flex mr-2 ${props.className}`}>
            <label className="mr-2">
                TACOS
            </label>
            <TacosInput 
                name="tacos"
                value = {props.tacos}
                className="tacosInput"
                {...props}
            />
        </div>
    )
}

function TacosBidRange(props){
    return (
        <div className="centerChild flex">
            <label className="w-4/12 whitespace-no-wrap">Bid Range: </label>
            <div className="bidRangeInput centerChild flex flex-col lg:mr-8 ml-2 ml-4 mr-5">
                <TacosInput 
                    name="min"
                    value = {props.min}
                    
                    {...props}
                />
                <label className="block w-full text-center">
                    Min
                </label>
            </div>
            <div className="flex flex-col items-center justify-center bidRangeInput">
                <TacosInput 
                    name="max"
                    value = {props.max}
                    {...props}
                />
                <label className="block w-full text-center">
                    Max
                </label>
            </div>
        </div>
    )
}

function TacosInput (props) {
    const [state, setstate] = useState(props.value);
    useEffect(() => {
        setstate(props.value);
    }, [props.value])
    const onChange = (e) => {
        let value = e.target.value;
        if(((e.target.value.length === 3 && ["min","tacos"].includes(e.target.name)) || 
            (e.target.value.length === 4 && e.target.name === "max")) && 
            props.value.indexOf('.') === -1 ){
            value = value + ".";
        }
        
        if(value.split('.').length > 2){
            value = value.slice(0,-1);
        }
        setstate(value);
        props.getInput && props.getInput(e.target.name, value);
    }
    
    const handleTacosKeyPress = (e) =>{
        // console.log("isTextSelected::",JSON.stringify(IsTextSelected()))
        if (!isNumber(e.key,e) ) e.preventDefault();
    }
    const validatePastEvent = (e) => {
        var reg = /[^0-9.]/g;
        const pastObj = e.target;
        // access the clipboard using the api
        var pastedData = e.clipboardData.getData("text");
        var result = pastedData.match(reg);
        if (result != null) {
            e.preventDefault();
        }
    }
    
    const isNumber = (value, e) => {
        var letters = /^[0-9]/gi;
        if (value.match(letters) == null && value !== ".") {
            return false;
        }
        if(e.target.value.split('.').length >= 2 && e.target.value.split('.')[1].length > 1) {
            if(["tacos", "min"].includes(e.target.name) && e.target.value.length > 5 ) return false;
            if(["max"].includes(e.target.name) && e.target.value.length > 6 ) return false;
        }
        return true;
    }//end function
    return (
        <input 
            onChange={onChange}
            onKeyPress={handleTacosKeyPress} 
            onPaste={validatePastEvent} 
            onKeyUp={props.handleAddTacosOnKeyUp} 
            value={state}
            name={props.name}
            className={`tacosInput text-center ${props.className ?? ""}`}
        />
    )
}
