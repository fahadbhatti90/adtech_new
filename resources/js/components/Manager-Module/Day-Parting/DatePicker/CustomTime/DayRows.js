import React, {useState, useEffect} from 'react';
import CheckBox from "../../../../../general-components/CheckBox";

const DayRows = (props) => {

    const [clicked, setClicked] = useState(false);

    useEffect(()=>{
        setClicked(props.isChecked)
    }, [props.isChecked])
    const onCheckHandler = () => {
        props.checkAll(props.day, !props.isChecked)
    }
    return (
        <>

            <div className="flex flex-row">
                <div className="border h-12 w-1/4 dayPartingCheckBoxes dayPartingDayCheckBoxes">
                    {props.check ?
                        <CheckBox
                            checked={clicked}
                            onChange={onCheckHandler}
                        />
                        :
                        ""}
                </div>

                <div className="border h-12 p-2 w-2/4 text-left font-bold flex items-center dayPartingDays">
                    {props.day}
                </div>
            </div>
        </>
    );
};

export default DayRows;