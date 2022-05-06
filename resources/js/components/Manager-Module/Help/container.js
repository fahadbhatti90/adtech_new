import React from 'react';
import Card from '@material-ui/core/Card';
import {makeStyles} from '@material-ui/core/styles';
import {Helmet} from "react-helmet";
import Accordion from '@material-ui/core/Accordion';
import AccordionDetails from '@material-ui/core/AccordionDetails';
import AccordionSummary from '@material-ui/core/AccordionSummary';
import Typography from '@material-ui/core/Typography';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';


const useStyles = makeStyles((theme) => ({
    card: {
        borderRadius: 15,
        border: '1px solid #e1e1e3',
        backgroundColor: '#fffff',
        padding: '20px 25px 0px',
        boxShadow: "none",
        postion: 'absolute'
    },
    heading: {
        fontSize: theme.typography.pxToRem(15),
    },
    backgroundAccordion:{
        background:'#f1f1f1'
    },
    pageTitle: {
        fontSize: '1rem',
        fontWeight: 600,
    },
}));

export default function Help() {
    const classes = useStyles();
    const [expanded, setExpanded] = React.useState('panel1');

    const handleChange = (panel) => (event, isExpanded) => {
        setExpanded(isExpanded ? panel : false);
    };
    return (
        <>
            <Helmet>
                <title>Pulse Advertising Help</title>
            </Helmet>
            <Card classes={{root: classes.card}}>
                <Accordion className={`mb-2`} expanded={expanded === 'panel1'} onChange={handleChange('panel1')}>
                    <AccordionSummary
                        expandIcon={<ExpandMoreIcon/>}
                        aria-controls="panel1bh-content"
                        id="panel1bh-header"
                        className={`${classes.backgroundAccordion}`}
                    >
                        <Typography className={classes.heading}>BASICS</Typography>
                    </AccordionSummary>
                    <AccordionDetails>
                        <Typography>
                            Change between brands by clicking brand name underneath login, and a popup will appear to
                            select the correct brand
                        </Typography>
                    </AccordionDetails>
                </Accordion>
                <Accordion className={`mb-2`} expanded={expanded === 'panel2'} onChange={handleChange('panel2')}>
                    <AccordionSummary
                        expandIcon={<ExpandMoreIcon/>}
                        aria-controls="panel1bh-content"
                        id="panel1bh-header"
                        className={`${classes.backgroundAccordion}`}
                    >
                        <Typography className={classes.heading}>TACOS BIDDING </Typography>
                    </AccordionSummary>
                    <AccordionDetails>
                        <Typography>
                            Click TACOS Bidding Tab
                            <h5>Features</h5>

                                    <ul>
                                        <li className='list-disc'>Search Tool</li>
                                        <li className='list-disc'>Filter by:
                                            <ul>
                                                <li className='list-disc'>Child Brand</li>
                                                <li className='list-disc'>Category</li>
                                                <li className='list-disc'>Strategy – Auto, Defensive, Head, Legacy, Offensive, Tail</li>
                                                <li className='list-disc'>Status – Archived, Enabled, Paused</li>
                                                <li className='list-disc'>Add/Remove Columns you want to see in the generated table below</li>
                                                <li className='list-disc'>Click Apply to see filtered table</li>
                                            </ul>
                                        </li>
                                        <li className='list-disc'>Bulk/Individual Select
                                            <ul>
                                                <li className='list-disc'>Click black box by Sr.# to select all items in the table</li>
                                                <li className='list-disc'>Click black box by particular Sr.# to select that individual item</li>
                                            </ul>
                                        </li>
                                        <li className='list-disc'>Setting the Bid
                                            <ul>
                                                <li className='list-disc'>Select ACOS or ROAS</li>
                                                <li className='list-disc'>Input TACOS, Min, Max</li>
                                                <li className='list-disc'>Submit</li>
                                            </ul>
                                        </li>
                                    </ul>

                        </Typography>
                    </AccordionDetails>
                </Accordion>
                <Accordion className={`mb-2`} expanded={expanded === 'panel3'} onChange={handleChange('panel3')}>
                    <AccordionSummary
                        expandIcon={<ExpandMoreIcon/>}
                        aria-controls="panel1bh-content"
                        id="panel1bh-header"
                        className={`${classes.backgroundAccordion}`}
                    >
                        <Typography className={classes.heading}>DAY PARTING – Schedules are defaulted to PST
                            Timezone </Typography>
                    </AccordionSummary>
                    <AccordionDetails>
                        <Typography>
                            Click Advertising, and select Day Parting
                            <h5>Features</h5>
                            <ul>
                                <li className='list-disc'>Input Schedule Name</li>
                                <li className='list-disc'>Select Child Brand</li>
                                <li className='list-disc'>Select Portfolio/Campaign</li>
                                <li className='list-disc'>Select Specific Campaigns</li>
                                <li className='list-disc'> Input a start date so it defaults to recurring schedule</li>
                                <li className='list-disc'> Only put end date if you don’t want schedule to be
                                    recurring
                                </li>
                                <li className='list-disc'>Scheduler
                                    <ul>
                                        <li className='list-disc'>Select box next to Day of the week if you want
                                            campaign to run every hour of the day
                                        </li>
                                        <li className='list-disc'>Select each box for which hours you want the campaign
                                            to run
                                            <ul>
                                                <li className='list-disc'>Each box represents one hour
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li className='list-disc'> Input email addresses to receive emails about campaign
                                    starting and ending
                                </li>
                                <li className='list-disc'>Active Schedules
                                    <ul>
                                        <li className='list-disc'>See active schedules in the module below, by name,
                                            campaign, day, start and end time
                                        </li>
                                        <li className='list-disc'>Pause campaigns by clicking the square
                                        </li>
                                        <li className='list-disc'>Continue campaigns by clicking the triangle
                                        </li>
                                        <li className='list-disc'>Delete campaigns by clicking the trash can
                                        </li>
                                        <li className='list-disc'>Edit campaigns by clicking the pencil
                                        </li>

                                    </ul>
                                </li>
                            </ul>
                        </Typography>
                    </AccordionDetails>
                </Accordion>
            </Card>
        </>
    );
}