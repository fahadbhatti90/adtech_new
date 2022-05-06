import React, {useEffect, useState} from "react";
import TextFieldInput from "../../../../general-components/Textfield";
import AddIcon from '@material-ui/icons/Add';
import RemoveIcon from '@material-ui/icons/Remove';
import CustomDatePicker from "../../Day-Parting/DatePicker/CustomDatePicker";

export function PopUpContainer(props) {
    return (
        <div className="flex">
            <div className="ml-3 w-6/12">
                <BidRange
                    getInput={props.getInput}
                    increaseBid={props.bidMultiplierData.increaseBid}
                    decreaseBid={props.bidMultiplierData.decreaseBid}
                    handleAddOnKeyUp={props.handleAddOnKeyUp}
                />
            </div>
            <div className="ml-3 popupSide w-8/12">
                <BidDateRange
                    setStartDateValue={props.setStartDateValue}
                    setEndDateValue={props.setEndDateValue}
                    startDate={props.startDateDP}
                    endDate={props.endDateDP}
                    isStartDateDisable={props.isStartDateDisable}
                    isEndDateDisable={props.isEndDateDisable}
                />
            </div>
        </div>
    )
}

function BidDateRange(props) {

    const [showDP, setShowDP] = useState(false);
    const [showEndDP, setShowEndDP] = useState(false);
    const getStartDateValue = (range) => {
        setShowDP(false);
        props.setStartDateValue(range);
    }
    const getEndDateValue = (range) => {
        setShowEndDP(false);
        props.setEndDateValue(range);
    }
    return (
        <div className="">
            <div className="flex bidDatePicker">
                <div className="ml-2 mr-4" onClick={
                    () => {
                        (props.isStartDateDisable ? setShowDP(!showDP) : '')
                    }
                }>
                    <TextFieldInput
                        disabled={!props.isStartDateDisable}
                        placeholder="Start Date"
                        id="dr"
                        type="text"
                        value={props.startDate}
                        fullWidth={true}
                        className={"datePickerText"}
                    />
                    <label className="whitespace-no-wrap flex">Start Date </label>
                </div>

                <div className={`absolute right-10 ${props.datepickerClass}`}>
                    {showDP ?
                        <CustomDatePicker
                            helperCloseDRP={setShowDP}
                            setSingleDate={getStartDateValue}
                            startDate={props.startDate}
                            direction="vertical"
                            isEndDate={false}
                    /> : null
                    }
                </div>
                <div onClick={
                    () => {
                        (props.isEndDateDisable ? setShowEndDP(!showEndDP) : '')
                    }
                }>

                    <TextFieldInput
                        disabled={!props.isEndDateDisable}
                        placeholder="End Date"
                        id="dr"
                        type="text"
                        value={props.endDate}
                        fullWidth={true}
                        className={"datePickerText"}
                    />
                    <label className="whitespace-no-wrap flex">End Date</label>
                </div>
                <div className={`absolute right-10 ${props.datepickerClass}`}>
                    {showEndDP ?
                        <CustomDatePicker
                            helperCloseDRP={setShowEndDP}
                            setSingleDate={getEndDateValue}
                            startDate={props.endDate}
                            direction="vertical"
                            isEndDate={false}
                        /> : null
                    }
                </div>
            </div>
        </div>
    )
}

function BidRange(props) {

    let decreaseBidStatus = props.decreaseBid.length > 0;
    let increaseBidStatus = props.increaseBid.length > 0;
    return (
        <div className="centerChild flex">
            <label className="whitespace-no-wrap">Bid Value (%): </label>

            <div className="bidRangeInput centerChild flex flex-col ml-4">
                <BidInput
                    isEnable={decreaseBidStatus}
                    name="increaseBid"
                    value={props.increaseBid}
                    {...props}
                />
                <AddIcon/>

            </div>

            <div className="ml-2">OR</div>

            <div className="ml-2 flex flex-col items-center justify-center bidRangeInput">
                <BidInput
                    isEnable={increaseBidStatus}
                    name="decreaseBid"
                    value={props.decreaseBid}
                    {...props}
                />
                <RemoveIcon/>
            </div>


        </div>
    )
}

function BidInput(props) {
    const [state, setstate] = useState(props.value);

    useEffect(() => {
        setstate(props.value);
    }, [props.value])
    const onChange = (e) => {

        let value = e.target.value;

        setstate(value);
        props.getInput && props.getInput(e.target.name, value);
    }

    const handleKeyPress = (e) => {
        if (!isNumber(e.key, e)) e.preventDefault();
    }
    const validatePastEvent = (e) => {

        let reg = /[^0-9]/g;
        const pastObj = e.target;
        // access the clipboard using the api
        let pastedData = e.clipboardData.getData("text");
        let result = pastedData.match(reg);
        if (result != null) {
            e.preventDefault();
        }
    }

    const isNumber = (value, e) => {

        let letters = /^[0-9]/gi;
        if (value.match(letters) == null && value !== ".") {
            return false;
        }

        if (["increaseBid"].includes(e.target.name) && e.target.value.length > 1) return false;
        if (["decreaseBid"].includes(e.target.name) && e.target.value.length > 1) return false;

        return true;
    }//end function
    return (
        <input
            disabled={props.isEnable}
            onChange={onChange}
            onKeyPress={handleKeyPress}
            onPaste={validatePastEvent}
            onKeyUp={props.handleAddTacosOnKeyUp}
            value={state}
            name={props.name}
            className={`tacosInput text-center ${props.className ?? ""}`}
        />
    )
}