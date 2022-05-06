import React from 'react';
import { FormControlLabel, FormGroup } from '@material-ui/core';
import { withStyles } from '@material-ui/core/styles';
import { primaryColor } from '../../app-resources/theme-overrides/global';
import {default as MCheckBox} from '@material-ui/core/Checkbox';

const ThemeCheckbox = withStyles({
    root: {
      color: primaryColor,
      '&$checked': {
        color: primaryColor,
      },
    },
    checked: {},
  })((props) => <MCheckBox {...props} size={"small"}/>);   
export default function Checkbox({
    label,
    name,
    value,
    handleChange,
    disabled,
    checked,
    containerClassname,
    className,
}) {
    return (
        <FormGroup className={`eventsCheckBoxGroup justify-between ${containerClassname}`}>
            <FormControlLabel
                disabled = {disabled}
                control={
                    <ThemeCheckbox 
                    className={`eventCheckBox ${className}`} 
                    checked={checked} 
                    value={value}
                    onChange={handleChange} 
                    name={name}
                    />
                }
                label={label}
            />
      </FormGroup>
    )
}
