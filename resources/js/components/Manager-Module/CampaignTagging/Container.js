import React, { Component } from 'react'
import CampaignTaggingDataTable from './CampaignTaggingDataTable'
import {Helmet} from "react-helmet";

export default class Container extends Component {
    render() {
        return (
            <div>
                <Helmet>
                    <title>Pulse Advertising | Campaign Tagging</title>
                </Helmet>
                <CampaignTaggingDataTable />
            </div>
        )
    }
}
