import React from 'react';
import Graph from '../BarGraphs/BarGraph';
import { Grid ,Card} from '@material-ui/core';
import {useStyles} from "../cardStyles";
import CardHeader from "./../../../../general-components/cardHeader";
import ContainerLoader from "../../../../general-components/ProgressLoader/ContainerLoader";
import HealthDashboardTooltip from "../HealthDashboard/HealthDashboardTooltip";
import DataTable from "react-data-table-component";
import NotificationsNoneIcon from '@material-ui/icons/NotificationsNone';

function GraphChart(props) {
    const classes = useStyles();
    return (
        <>
            <Card classes={{ root: classes.card }} className={`mt-6 relative ${props.customClass?props.customClass:""}`}>  
                {props.showLoader?
                    <ContainerLoader height={30}/>
                :""}
                <CardHeader
                    reloadBtn={false}
                    heading={
                        <div>
                            Report ID vs Report Links
                            { props.data.length > 0 ?
                                <HealthDashboardTooltip
                                    tooltipContent={
                                        <Card className="overflow-hidden healthDashbaordToolTipTables" classes={{root: classes.tableCard}}>
                                            <div className="font-semibold mb-3 ml-3 mt-2 w-4/12">Report Link Errors</div>
                                            <div className={"w-full dataTableContainer"}>
                                                <DataTable
                                                    noHeader={true}
                                                    wrap={false}
                                                    responsive={true}
                                                    columns={props.dataInformation()}
                                                    data={props.data}
                                                    progressPending={props.dataLoading}
                                                    persistTableHead
                                                />
                                            </div>
                                        </Card>
                                    }
                                    tooltipTarget={<NotificationsNoneIcon className="notificationBell"/>}/>
                                : ""

                            }
                        </div>
                    }
                />
                <Grid container>
                    <Grid item xs={12}>
                        <Graph
                            dataChart={props.dataChart}
                            categories={props.categories}
                        />
                    </Grid>
                </Grid>
            </Card>
           
        </>
    );
}

export default GraphChart;