import {primaryColor, primaryColorLight} from "../../../app-resources/theme-overrides/global";
import {makeStyles} from '@material-ui/core/styles';
export const useStylesTooltip = makeStyles(theme => ({
    ptTooltip:{
        color: "#000",
        maxWidth: 500,
        backgroundColor: "rgb(255 255 255 / 90%)",
        boxShadow: "1px 1px 10px #0000003b",
        padding: 0,
        maxHeight:200
    },
    ptArrow:{
        color: "#fff"
    },
}));

export const useStyles = theme => ({
    root: {
        '& .MuiInputBase-root': {
            marginTop: 8,
            borderRadius: 12,
            border: "1px solid #c3bdbd8c",
            height: 35,
            background: '#fff'
        },
        "&:hover .MuiInputBase-root": {
            borderColor: primaryColorLight,
            borderRadius: "12px",
        },
        '& .MuiInputBase-input': {
            margin: props => props.margin || 15,
            fontSize: '0.72rem',
            padding: '7px 0 7px'
        }
    },
    focused: {
        border: "2px solid !important",
        borderColor: `${primaryColor} !important`,
    },
    card: {
        borderRadius: 15,
        border: '1px solid #e1e1e3',
        padding: '20px 25px 0px',
        boxShadow: "none",
        paddingBottom: 25,
        marginTop: 10,
        minHeight: 200,
        overflow: 'visible'
    },
    tableCard: {
        borderRadius: 15,
        border: '1px solid #e1e1e3',
        backgroundColor: '#fffff',
        boxShadow: "none",
        postion: 'absolute'
    },
    datepickerClass: {
        zIndex: 1101
    },
    pageTitle: {
        fontSize: '1rem',
        fontWeight: 600,
    }
});