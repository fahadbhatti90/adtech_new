import * as Yup from "yup";
import {requiredValidationHelper} from "./../../../../helper/helper";
import Tooltip from "@material-ui/core/Tooltip";
import React from "react";
import Button from "@material-ui/core/Button";

export function scheduleNameValidate() {
    Yup.object().shape({
        scheduleName: requiredValidationHelper()
            .matches(
                /^[a-zA-Z0-9:_-]+$/,
                "The name can only consist of alphabetical,number,underscore,hyphen and colon"
            )
    });
}

export function getActivatedSchedules(scheduleName) {
    console.log('schedule Name', scheduleName)
    const name = scheduleName;
    if (name && name.length > 0) {
        if (name.length > 10) {
            const shortName = name.slice(0, 10) + "...";
            return <Tooltip title={name} placement="top" arrow>
                <span>{shortName}</span>
            </Tooltip>
        } else {
            return name;
        }
    } else {
        return "NA";
    }
}

export function newDpFunction(day, timeCampaigns) {
    switch (day) {
        case 'monday': {
            return TimeCampaign(timeCampaigns, day);
            break;
        }
        case 'tuesday': {
            return TimeCampaign(timeCampaigns, day);
            break;
        }
        case 'wednesday': {
            return TimeCampaign(timeCampaigns, day);
            break;
        }
        case 'thursday': {
            return TimeCampaign(timeCampaigns, day);
            break;
        }
        case 'friday': {
            return TimeCampaign(timeCampaigns, day);
            break;
        }
        case 'saturday': {
            return TimeCampaign(timeCampaigns, day);
            break;
        }
        case 'sunday': {
            return TimeCampaign(timeCampaigns, day);
            break;
        }
        default: {
            return 'no days selected';
            break;
        }

    }
}

export function TimeCampaign(timeCampaigns, day) {

    if (timeCampaigns && timeCampaigns.length > 0) {
        let listToShow = timeCampaigns.filter(value => value.day === day).map((value, index) => {
            let startTime = value.startTime;
            let endTime = value.endTime;
            if (startTime.length > 8 && endTime.length > 8) {
                let startTimeArray = startTime.split(",");
                let endTimeArray = endTime.split(",");
                let setTimeArray = startTimeArray.map((value1, idx1) => {
                    let finalStartEndTime = value1 + ' / ' + endTimeArray[idx1];
                    return <li className='list-disc' key={idx1.toString()}>
                        {finalStartEndTime}
                    </li>
                })
                return <>
                    {setTimeArray}
                </>

            } else {
                let timeOfCampaigns = value.startTime + " / " + value.endTime;
                return <li className='list-disc' key={index.toString()}>
                    {timeOfCampaigns}
                </li>
            }
        });

        if (listToShow.length > 0) {
            if (listToShow[0].key == 0) {
                return listToShow[0].props.children;
            }
            let ulList = <ul className='m-1 mr-5 pl-5 pr-3'>{listToShow}</ul>
            let allData = <div className={ulList.length > 0 ? "h-32 overflow-auto" : ""}>
                {ulList}
            </div>

            return <>
                <Tooltip title={allData} placement="top" arrow
                         interactive>
                    <Button>Show Timings</Button>
                </Tooltip>
            </>
        }
    }
    return;
}