import {grayColorLight, primaryColorLight} from "./../../app-resources/theme-overrides/global";
const backgroundColor = "#f9f7f3"
import SvgPulseLogin from "./../../app-resources/svgs/login/login-Export.svg";
export const styles = theme => ({
    root: {
      flexGrow: 1,
    },
    paper: {
      padding: theme.spacing(2),
      textAlign: 'center',
      color: theme.palette.text.secondary,
      height: '100%',
      background: backgroundColor
    },
    paperContainer: {
        minHeight: 625,
        width:"100%",
        position: 'relative',
        display:"flex",
    },
    loginPageLeftSide: {
      backgroundImage:`url(${window.assetUrl}${SvgPulseLogin})`,
      backgroundPosition:"center",
      backgroundSize:"cover",
      backgroundRepeat:"no-repeat",
      width: "100%",
      height: "100%",
      maxWidth: "70%",
    },
    container:{
        position: 'absolute',
        right: 0,
        // margin: 20,
        maxWidth: '33%',
        minWidth: '33.2%',
        padding: "18px 70px",
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        background: backgroundColor,
        minHeight: 625,
    },
    bulbInnerContainer:{
      // backgroundImage:`url(${window.assetUrl}${SpiralBg})`
    },
    typo:{
        fontWeight: 600,
        color: grayColorLight,
        fontSize: '0.75rem'
    },
    formControlLabel:{
      color: '#797878',
      fontSize: '1rem',
      fontWeight: 400,
      margin: 0,
      maxWidth: "30%",
      whiteSpace:"nowrap"
    },
    rootInput:{
      height: "40px !important",
    }
})