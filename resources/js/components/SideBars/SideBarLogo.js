import React from 'react'
import LogoOriginal from "./../../app-resources/svgs/LogoOriginal.svg";
import LogoWhiteIdeo from "./../../app-resources/svgs/Ideo-White.svg";
import SvgLoader from './../../general-components/SvgLoader';

export default function SideBarLogo(props) {
    return (
        <div className={props.classes.drawerHeader}>
            <SvgLoader 
            onClick={()=>{ htk.history.replace("/superAdmin") }} 
            customClasses="SidBarLogo" 
            src={LogoWhiteIdeo}
            height={"100px !important"}
            />
        </div>
    )
}
