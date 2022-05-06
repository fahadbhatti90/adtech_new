import React, { Component } from 'react'
import './DeleteTacos.scss';
import {connect} from "react-redux"
import TextButton from "./../../../../../general-components/TextButton";
import PrimaryButton from "./../../../../../general-components/PrimaryButton";
import {ShowSuccessMsg} from "./../../../../../general-components/successDailog/actions";
import {ShowFailureMsg} from "./../../../../../general-components/failureDailog/actions";
import LinearProgress from '@material-ui/core/LinearProgress';
import { deleteItem } from './../../../../../service/service';
import {
    TACOS_CAMPAIGN_HISTORY_DELETE_DATA_URL
} from './../../apiCalls';
class DeleteTacos extends Component {
    constructor(props){
        super(props);
        this.state = {
            isProcessing:false
        }
    }
    handleDeleteTacosFormSubmit = () => {
        this.setState({isProcessing:true})
        deleteItem(TACOS_CAMPAIGN_HISTORY_DELETE_DATA_URL+this.props.id, ()=>{
            this.props.handleModalClose();
            this.props.dispatch(ShowSuccessMsg("Successfull", "Item deleted successfully", true, "",this.props.helperReloadDataTable()));
        }, (error)=>{
            this.setState({isProcessing:false})
            this.props.dispatch(ShowFailureMsg(error, "", true, ""));
        });
    }
    render() {
        return (
            <>
            <div className="graphLoader bg-white absolute h-full overflow-hidden w-full top-0 left-0 z-10" style={this.state.isProcessing?{display:"block"}:{display:"none"}} >
                <LinearProgress />
                <div className="absolute flex font-bold font-mono h-full items-center justify-center overflow-hidden text-1rem text-sm w-full z-10">
                    Processing...
                </div>
            </div>
            <div className="px-12 relative">
                
                <div className="flex float-right items-center justify-center my-5 w-full">
                    Do you really want to delete this record?
                </div>
                <div className="flex float-right items-center justify-center my-5 w-full">
                        <div className="mr-3">
                            <TextButton
                            BtnLabel={"Cancel"}
                            color="primary"
                            onClick={this.props.handleModalClose}/>
                        </div>
                        <PrimaryButton
                            btnlabel={"Confirm"}
                            variant={"contained"}
                            onClick={this.handleDeleteTacosFormSubmit}
                        />     
                </div>
            </div>
            </>
        )
    }
}

export default connect(null)(DeleteTacos)
