import React from 'react';
import PropTypes from 'prop-types';
import "./styles.scss";
import Tooltip from '@material-ui/core/Tooltip';
import { Grid } from '@material-ui/core';
    
function SingleComCard(props) {

    var component = (
            <p className="">
                {
                    props.value
                }
            </p>
    );
       
    return (
            <Grid container>
                <Grid item xs={8} sm={8} md={8} lg={8}>
                    <div className="text-xs font-semibold">{props.healthTitle}</div>
                    <div className="text-sm font-semibold">
                        {
                            props.tooltipTitle ?
                            <Tooltip placement="top-start" title={props.tooltipTitle} arrow>
                                {component}
                            </Tooltip>
                            :
                            component
                        }
                    </div>
                </Grid>
                <Grid item xs={4} sm={4} md={4} lg={4}>
                    <img className="float-right svgsHealthDash" src={window.assetUrl+props.logo} />
                </Grid>
            </Grid>
           
    );
   
}

SingleComCard.propTypes = {
    healthTitle: PropTypes.string.isRequired,
    value: PropTypes.number
};

export default SingleComCard;