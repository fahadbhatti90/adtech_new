import React from 'react';

const EditHoursRows = (props) => {

    return (
        <div parenteditname={props.parentName} itemindex={props.itemIndex} ischecked={`${+props.isChecked}`}
             className={`editHourRows flex justify-center items-center font-bold border h-12 w-12 ${+props.isChecked ? "bg-yellow-600" : ""}`}>
        </div>
    );
}

export default EditHoursRows;