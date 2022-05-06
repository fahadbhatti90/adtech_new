import React from 'react'
import Tooltip from '@material-ui/core/Tooltip';
import {useStylesTooltip} from "../styles";


export default function HealthDashboardTooltip(props) {
    const classes = useStylesTooltip();
    return (
        <Tooltip classes={{
            tooltip: classes.ptTooltip,
            arrow: classes.ptArrow,
        }}
                 className="newClass"
                 placement="top"
                 title={props.tooltipContent}
                 arrow
                 interactive
        >
            {props.tooltipTarget}
        </Tooltip>
    )
}
