import React from 'react';

const HoursRows = (props) => {

    return (
        <div parentname={props.parentName} itemindex={props.itemIndex} ischecked={`${+props.isChecked}`}
             className={`hourRows flex justify-center items-center font-bold border h-12 w-12 ${+props.isChecked ? "bg-yellow-600" : ""}`}>
        </div>
    );
}

export default HoursRows;