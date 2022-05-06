import React from "react";
import Radio from "@material-ui/core/Radio";
import {FormControl, Grid} from "@material-ui/core";
import FormLabel from "@material-ui/core/FormLabel";
import RadioGroup from "@material-ui/core/RadioGroup/RadioGroup";
import FormControlLabel from "@material-ui/core/FormControlLabel/FormControlLabel";
import {withStyles} from "@material-ui/core/styles";
import {primaryColor} from "../../../app-resources/theme-overrides/global";

const GreenRadio = withStyles({
    root: {
        color: primaryColor,
        '&$checked': {
            color: primaryColor,
        },
    },
    checked: {},
})((props) => <Radio color="default" {...props} />);


export function RadioButtonREvent(props){


    const handleChange = () => {
        let row = props.row;
        props.handleClick(row)
    }
    return (
        <Radio
            //checked={selectedValue === 'a'}
            onChange={handleChange}
            value={props.eventId}
            name="radio-buttons"
            inputProps={{ 'aria-label': 'A' }}
        />
    )
}