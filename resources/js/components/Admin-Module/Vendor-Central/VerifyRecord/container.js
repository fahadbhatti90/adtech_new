import React, { Component } from 'react';
import VerifyRecordData from "../VerifyRecord/Verify/Verify";
import {withStyles} from "@material-ui/core";
import {styles} from "../styles";
import {Helmet} from "react-helmet";

class VerifyRecord extends Component{
    constructor(props) {
        super(props);
    }
    render() {
        const {classes} = this.props;
        return (
            <>
                <Helmet>
                    <title>Pulse Advertising | VC</title>
                </Helmet>
                <div className="vendorCentral">
                        <VerifyRecordData/>
                </div>
            </>
        );
    }
}

export default withStyles(styles)(VerifyRecord);