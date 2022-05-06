import React, {useState} from 'react'
import RadioGroup from '@material-ui/core/RadioGroup';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import ThemeRadioButton from '../../../../../general-components/ThemeRadioButtons/ThemeRadioButton';
import PrimaryButton from '../../../../../general-components/PrimaryButton';
import DataTable from "react-data-table-component";
import Typography from "@material-ui/core/Typography";

const columns = [
    {
        name: 'Campaign Type',
        sortable: false,
        selector: 'campaignType',
        maxWidth:"180px"
    },
    {
        name: 'Count',
        sortable: false,
        selector: 'count',
        maxWidth:"80px"
    },
    {
        name: 'Alert',
        sortable: false,
        selector: 'year'
    },
];

const data = (props) => {
    return [
        {
            campaignType: "Sponsored Products",
            count: props.SPCount,
            year: 'Minimum bid cannot be less than 0.02',
        },
        {
            campaignType: "Sponsored Brands",
            count: props.SBCount,
            year: 'Minimum bid cannot be less than 0.10',
        },
        {
            campaignType: "Sponsored Display",
            count: props.SDCount,
            year: 'Minimum bid cannot be less than 0.02',
        },
    ]
}
const TacosConfirmationPopup = (props) => {

    return (
        <>
            <div className="relative tacosConfirmPopUp">
                <Typography className="m-0 pb-3 text-center textContentTacos" variant="h6" >Please update your bid according to given details</Typography>
                <DataTable
                    columns={columns}
                    data={data(props.data)}
                />

                <div className="flex float-right items-center justify-center my-5 w-full">
                    <PrimaryButton
                        btnlabel={"Ok"}
                        variant={"contained"}
                        onClick={props.closeTacosConfirmationPopup}
                    />
                </div>
            </div>
        </>
    )
}

export default TacosConfirmationPopup


function TacosOptions(props) {
    const [selectedOption, setSelectedOption] = useState(null);

    const handleChange = (e) => {
        setSelectedOption(e.target.value);
        props.getInput && props.getInput(props.name, e.target.value);
    }

    return (
        <RadioGroup row aria-label="position" name="position" defaultValue="top" className="inline-flex">
            <FormControlLabel
                value="top"
                className="ml-0"
                control={<ThemeRadioButton
                    checked={selectedOption === ""}
                    onChange={(handleChange)}
                    value={""}
                    name="empty"
                    size="small"/>
                }
                label="Empty"
            />

            <div className="ml-5">
                <FormControlLabel
                    value="top"
                    control={<ThemeRadioButton
                        checked={selectedOption === 0.02}
                        onChange={handleChange}
                        value={0.02}
                        name="minValue"/>
                    }
                    label="0.02"
                />
            </div>
        </RadioGroup>
    )
}