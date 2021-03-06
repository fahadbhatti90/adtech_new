import React from 'react'
import DataTableLoadingCheck from './DataTableLoadingCheck';
import LinearProgress from '@material-ui/core/LinearProgress';
import { makeStyles } from '@material-ui/core/styles';
const useStyles = makeStyles(theme => ({
    root: {
      width: '100%',
      '& > * + *': {
        marginTop: theme.spacing(2),
      },
    }
}));

export default function ProductTableProgressBar(props) {
    const classes = useStyles();
    return (
        <div className={classes.root}>
            <LinearProgress />
            <DataTableLoadingCheck setDatatableLoaded ={props.setDatatableLoaded} />
        </div>
    );
}
