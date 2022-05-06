import React, { useState } from 'react';
import ExpandMore from '@material-ui/icons/ExpandMore';
import Menu from '@material-ui/core/Menu';
import {connect} from "react-redux"
import MenuItem from '@material-ui/core/MenuItem';
import {logout} from '../../../Login/actions';
import {getUserName} from "../../../../helper/helper";
import SvgLoader from '../../../../general-components/SvgLoader';
import userIcon from "../../../../app-resources/svgs/manager/user.svg";
import {SetLoginStatus} from '../../../../general-components/HeaderRedux/actions';

const AppBarUserElement = props => {
    const [anchorEl, setAnchorEl] = useState(null);
    
    const handleSetAnchorEl = (event) => {
        setAnchorEl(event ? event.currentTarget : event);
      };
    const handleClose = (name) => {
        handleSetAnchorEl(null);
        if(name == "logout"){
            localStorage.removeItem(htk.constants.IS_ADMIN);
            props.logoutFromBackend(htk.history,(response)=>{
                props.dispatch(logout());
                props.dispatch(SetLoginStatus(false));
            },(error)=>{
                console.log(error)
            }); 
        }  else if(name == "portal") {
            if(htk.activeRole == 2){
                htk.activeRole = 3;
                localStorage.setItem(htk.constants.ACTIVE_ROLE,3);
            } else if(htk.activeRole == 3){
                htk.activeRole = 2;
                localStorage.setItem(htk.constants.ACTIVE_ROLE,2);
            }
            props.getLatestNavigationInfo(parseInt(htk.activeRole), true);
            
        }
    }
    
    let isAdmin = htk.getLocalStorageObjectDataById(htk.constants.IS_ADMIN);
    return (
        <div className="flex flex-1 items-center justify-end userInfoSection">
            <span className="bg-indigo-800 border-0 h-10 overflow-hidden pt-1 rounded-full userIconContainer w-10">
                <SvgLoader customClasses="userIcon" src={userIcon} alt="User Icon"/>
            </span>
            <span className="flex flex-col ml-4 mr-8 userDetails">
                <span className="text-xs userName themeNormalFontFamily whitespace-no-wrap">
                    <strong >Ideoclick Pulse Advertising<sup>TM</sup></strong>
                </span>
                <span className="font-semibold text-gray-500 text-xs userRole whitespace-no-wrap">
                    { getUserName() }
                </span>
            </span>
            <span className="border-2 border-gray-500 border-gray-700 border-solid dropDownIcon flex flex-col h-5 items-center justify-center p-1 rounded-full text-gray-700 w-5 cursor-pointer"  aria-controls="simple-menu" aria-haspopup="true" onClick={handleSetAnchorEl}>
                <ExpandMore />
            </span>
            <Menu
                id="simple-menu"
                anchorEl={anchorEl}
                keepMounted
                open={Boolean(anchorEl)}
                onClose={handleClose}
                
                anchorOrigin={{
                    vertical: "bottom",
                    horizontal: "left"
                }}
                transformOrigin={{
                    vertical: "top",
                    horizontal: "left"
                }}
                getContentAnchorEl={null}
                
            >
                {/* <MenuItem onClick={handleClose}>Profile</MenuItem>
                <MenuItem onClick={handleClose}>My account</MenuItem> */}
                {isAdmin?
                <MenuItem onClick={()=>handleClose("portal")}>{htk.activeRole == 2?"Brand Portal":"Admin Portal"}</MenuItem>
                :""}
                <MenuItem onClick={()=>handleClose("logout")}>Logout</MenuItem>
            </Menu>
        </div>
    )
};
export default connect(null)(AppBarUserElement);