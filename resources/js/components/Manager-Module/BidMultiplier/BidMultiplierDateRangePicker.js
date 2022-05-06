import React, {Component} from 'react'
import 'react-date-range/dist/styles.css'; // main style file
import 'react-date-range/dist/theme/default.css';
import {DateRangePicker} from 'react-date-range';


export default class BidMultiplierDateRangePicker extends Component {
    isMounted = false;

    constructor(props) {
        super(props)
        this.state = {
            selectionRange: {
                startDate: new Date(),
                endDate: new Date(),
                key: 'selection',
            },
            dateRangeCounter: 0,
            isVisible: false,
        }

        this.wrapperRef = React.createRef();
    }

    componentDidMount() {

        this.isMounted = true;
        this.setState({
            isVisible: true,
            selectionRange: {
                startDate: (this.props.range.startDate),
                endDate: this.props.range.endDate,
                key: 'selection',
            },
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
        if ($(event.target).hasClass("rdrStaticRangeLabel")) {
            this.resetAndClose(this.state.selectionRange);
        }
    }
    handleOnDateRangeChange = (ranges) => {
        let currentCounter = this.state.dateRangeCounter;
        this.setState({
            selectionRange: ranges.selection,
            dateRangeCounter: ++this.state.dateRangeCounter
        })
        if ((currentCounter + 1) > 1) {
            this.resetAndClose(ranges.selection);
        }
    }
    resetAndClose = (ranges) => {
        this.setState({
            dateRangeCounter: 0
        })
        this.props.getValue(ranges)
    }

    handleSingleDateChange = (date) => {

        this.setState({
            date
        }, () => {
            this.props.setSingleDate(date);
        })
    }

    render() {
        return (
            <div ref={this.wrapperRef} className="CustomizedDateRangePickerParent"
                 style={this.state.isVisible ? {transform: "scale(1) translate(0px, 0px)"} : {opacity: "0"}}>
                <DateRangePicker
                    className="CustomDateRangePicker "
                    ranges={this.props.showRanges ? [] : [this.state.selectionRange]}
                    onChange={this.handleOnDateRangeChange}
                    showSelectionPreview={true}
                    moveRangeOnFirstSelection={false}
                    months={2}
                    direction={this.props.direction}//"horizontal"|"vertical"
                    minDate={this.props.minDate && this.props.minDate}
                />
            </div>
        )
    }
}