import React from 'react'
import { withStyles } from '@material-ui/core/styles';
import Switch from '@material-ui/core/Switch';
import {primaryColorOrange} from "./../../app-resources/theme-overrides/global";
import './ThemeSwitchBtn.scss';

const ThemeSwitchBtn = withStyles({
    switchBase: {
      color: primaryColorOrange,
      '&$checked': {
        color: primaryColorOrange,
      },
      '&$checked + $track': {
        backgroundColor: primaryColorOrange,
      },
    },
    checked: {},
    track: {},
  })(Switch);


export default React.memo(ThemeSwitchBtn)