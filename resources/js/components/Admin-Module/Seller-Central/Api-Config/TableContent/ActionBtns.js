import React,{Component} from "react";
import DeleteIcon from '@material-ui/icons/Delete';
import EditIcon from '@material-ui/icons/Edit';
import {createMuiTheme, MuiThemeProvider } from '@material-ui/core/styles';
//import "../../styles.scss";

const InputTheme = createMuiTheme({
    overrides: {
        MuiSvgIcon: {
            root:{
                fontSize: '1.1rem',
            }
        },
    }
});

export default class ActionBtns extends Component{

    onClickDelete=()=>{
        let rowId = this.props.row.mws_config_id;
        this.props.deleteApiConfig(rowId);
    }

    onClickEdit=()=>{
        let row= this.props.row;
        this.props.editApiConfig(row);
    }

    render(){
        return(
            <>
            <MuiThemeProvider theme={InputTheme}>
                {
                    <button 
                        className={this.props.row.isParentBrand == 1?"invisible":"btnHover"}
                        onClick={this.onClickDelete}
                        >
                        <DeleteIcon className="text-base"/>
                    </button>
                }
                <button 
                    className="btnHover"
                    onClick={this.onClickEdit}
                    >
                    <EditIcon className="text-base"/>
                </button>
            </MuiThemeProvider>
        </>
        )
    }
}