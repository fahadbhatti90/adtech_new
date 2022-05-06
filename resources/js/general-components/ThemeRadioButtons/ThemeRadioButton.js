
import React from 'react'
import {primaryColor, primaryColorOrange} from "./../../app-resources/theme-overrides/global";
import Radio from '@material-ui/core/Radio';
import { withStyles } from '@material-ui/core/styles';


const ThemeRadioButton = withStyles({
    root: {
      color: primaryColor,
      '&$checked': {
        color: primaryColorOrange,
      },
    },
    checked: {},
})((props) => <Radio {...props} size={"small"}/>);  


export default ThemeRadioButton;