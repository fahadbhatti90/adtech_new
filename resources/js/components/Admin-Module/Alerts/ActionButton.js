import React from 'react'
import DeleteIcon from '@material-ui/icons/Delete';
import EditIcon from '@material-ui/icons/Edit';
import {createMuiTheme, MuiThemeProvider } from '@material-ui/core/styles';

const InputTheme = createMuiTheme({
    overrides: {
        MuiSvgIcon: {
            root:{
                fontSize: '1.1rem',
            }
        },
    }
});

const ActionButton = (props) => {

    const onClickDelete=()=>{
        let rowId = props.row.id;
        props.deleteAlert(rowId);
    }

    const onClickEdit=()=>{
        let row= props.row;
        props.editAlert(row);
    }
        return(
            <>
                <MuiThemeProvider theme={InputTheme}>
                    {
                        <button
                            className={"btnHover"}
                            onClick={onClickDelete}
                        >
                            <DeleteIcon className="text-base"/>
                        </button>
                    }
                    <button
                        className="btnHover"
                        onClick={onClickEdit}
                    >
                        <EditIcon className="text-base"/>
                    </button>
                </MuiThemeProvider>
            </>
        )
}

export default ActionButton