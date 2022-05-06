import React, {Component} from 'react';
import {Helmet} from "react-helmet";
import HealthDashboard from "./HealthDashboard/HealthDashboard";
class SuperAdminDashboard extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <>
                <Helmet>
                    <title>Pulse Advertising | Dashboard</title>
                </Helmet>
                <div>
                    <HealthDashboard/>
                </div>
            </>

        );
    }
}

export default (SuperAdminDashboard);