import React, {Component} from "react";
import DeleteIcon from '@material-ui/icons/Delete';
import EditIcon from '@material-ui/icons/Edit';
import {createMuiTheme, MuiThemeProvider} from '@material-ui/core/styles';
import moment from "moment";

const InputTheme = createMuiTheme({
    overrides: {
        MuiSvgIcon: {
            root: {
                fontSize: '1.1rem',
            }
        },
    }
});

export default class ActionButtons extends Component {

    onClickDelete = () => {
        let rowId = this.props.row.id;
        this.props.deleteBudgetRule(rowId);
    }

    onClickEdit = () => {
        let row = this.props.row;
        this.props.editBudgetRule(row);
    }
    render() {
        let currentD = moment(new Date()).format("MM-DD-YYYY");
        let endD = moment(this.props.row.endDate).format("MM-DD-YYYY");

        return (
            <>
                <MuiThemeProvider theme={InputTheme}>
                    <button className="btnHover" onClick={this.onClickDelete}><DeleteIcon/></button>
                    {
                        endD != null && endD >= currentD ?
                        <div>

                            <button className="btnHover" onClick={this.onClickEdit}><EditIcon/></button>
                        </div>

                            :
                            <div>
                                <button disabled={true}><EditIcon/></button>
                            </div>
                    }
                </MuiThemeProvider>
            </>
        )
    }
}
