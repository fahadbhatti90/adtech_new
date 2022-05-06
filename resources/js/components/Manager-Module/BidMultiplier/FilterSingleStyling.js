
import {primaryColor} from "../../../app-resources/theme-overrides/global";
const customStyle = {
    menu: base => ({
        ...base,
        marginTop: 0
    }),
    control: (base, state) => ({
        background: '#fff',
        height: 30,
        borderRadius: 20,
        display: 'flex',
        border: "1px solid #c3bdbd8c", //${primaryColor}
        // This line disable the blue border
        boxShadow: 0,
        '&:hover': {
            border: "1px solid #c3bdbd8c"
        },
        fontSize: '0.72rem'
    }),
    container: (provided, state) => ({
        ...provided,
        marginTop: 8
    }),
    valueContainer: (provided, state) => ({
        ...provided,
        padding: "0px 8px",
        overflowY: "auto",

    }),
    multiValue: (styles, {data}) => {
        return {
            ...styles,
            borderRadius: 25
        };
    },
    multiValueRemove: (styles, {data}) => ({
        ...styles,
        color: data.color,
        ':hover': {
            backgroundColor: primaryColor,
            color: 'white',
            borderRadius: 25
        },
    }),
}

export default customStyle;