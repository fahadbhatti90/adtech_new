import React, {Component} from 'react';
import ModalDialog from "./../../../general-components/ModalDialog";


export default class DayPartingDeleteModal extends Component {

    constructor(props){
        super(props)
    }

    render() {
        return (
            <div>
                <ModalDialog
                    open={this.props.openModal}
                    title={this.props.modalTitle}
                    id={this.props.id}
                    handleClose={this.props.handleClose}
                    component={this.props.modalBody}
                    maxWidth={this.props.maxWidth}
                    fullWidth={true}
                    cancelEvent={this.props.handleClose}
                    disable
                />
            </div>
        );
    }

}