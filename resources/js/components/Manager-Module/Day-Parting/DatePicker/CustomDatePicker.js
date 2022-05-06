import React, {Component} from 'react'
import 'react-date-range/dist/styles.css'; // main style file
import 'react-date-range/dist/theme/default.css';
import {Calendar} from 'react-date-range';
import clsx from 'clsx';

export default class CustomDatePicker extends Component {
    isMounted = false;

    constructor(props) {
        super(props)
        this.state = {
            isVisible: false,
            minDate: new Date(),
            startDate: new Date(),
            endDate: new Date()
        }

        this.wrapperRef = React.createRef();
    }

    componentDidMount() {
        this.isMounted = true;
        this.setState({
            isVisible: true,
            startDate: new Date(this.props.startDate),
            endDate: new Date(this.props.endDate)
        })
        document.addEventListener('click', this.handleClickOutside);
    }

    componentWillUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    }

    handleClickOutside = (event) => {
        if (this.wrapperRef && !this.wrapperRef.current.contains(event.target) && this.isMounted) {
            this.setState({
                isVisible: false,
            })
            this.props.helperCloseDRP();
        }
    }

    resetAndClose = (ranges) => {
        this.setState({
            dateRangeCounter: 0
        })
        this.props.getValue(ranges)
    }

    handleStartDateChange = (startDate) => {
        this.setState({
            startDate
        }, () => {
            this.props.setSingleDate(startDate);
        })
    }

    handleEndDateChange = (endDate) => {
        this.setState({
            endDate
        }, () => {
            this.props.setSingleDate(endDate);
        })
    }

    render() {
        return (
            <div ref={this.wrapperRef}
                 className={clsx("CustomDateRangePickerParent absolute z-10 customSingleCalender")}
                 style={this.state.isVisible ? {transform: "translateX(0)"} : {transform: "translateX(200%)"}}>
                <Calendar
                    className="CustomDateRangePicker"
                    onChange={this.props.isEndDate ? this.handleEndDateChange : this.handleStartDateChange}
                    minDate={this.state.minDate}
                    date={this.state.date}
                />
            </div>
        )
    }
}
