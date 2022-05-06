import React from 'react';
import clsx from 'clsx';
import {connect} from 'react-redux';
import {useStyles} from './styles';
import SideBar from '../../../SideBars/SideBar';
import Header from './Header';

function NavigationBars(props) {
    const classes = useStyles();
    const [openNotificationPopup, setOpenNotificationPopup] = React.useState(false);
    const [mobileOpen, setMobileOpen] = React.useState(false);
 
    const handleDrawerToggle = () => {
        setMobileOpen(!mobileOpen);
    };
    return (
            <>
                <Header 
                    classes = {classes}
                    openNotificationPopup={openNotificationPopup}
                    mobileOpen = {mobileOpen}   
                    handleDrawerToggle = {handleDrawerToggle}
                    setOpenNotificationPopup={setOpenNotificationPopup} 
                 />
                 <SideBar 
                    isAdmin = {props.isAdmin}
                    handleDrawerToggle = {handleDrawerToggle}
                />  
                <div className={clsx("fixed top-0 left-0 w-full h-full", props.openNotificationPopup ? "display":"hidden")}>
                </div>
           </>            
           
    )
}



const mapStateToProps = state => {
    return {
        isAdmin : state.SIDE_BAR_STATUS.isAdmin 
    }
  }
export default connect(mapStateToProps)(NavigationBars);
