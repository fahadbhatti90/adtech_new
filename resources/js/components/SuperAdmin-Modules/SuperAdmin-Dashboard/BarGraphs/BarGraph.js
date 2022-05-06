import React, {Component} from 'react';
import C3Chart from 'react-c3js';
import 'c3/c3.css';

class BarGraph extends Component {
    constructor(props) {
        super(props);
    }

    render() {

        let data = {
            x: 'x',
            columns: [
                ["x", ...this.props.categories],
                ...this.props.dataChart
            ],
            empty: {
                label: {
                    text: "No Data"
                }
            },
            colors: {
                'Report ID': '#571986',
                'Report Links': '#FF7F0E',
            },
            type: 'bar'
        };

        let grid = {
            y: {
                show: true
            },
        };
        let legend = {
            show: true,
            position: 'inset',
            inset: {
                anchor: 'top-right',
                x:0,
                y: -30,
                step: -5
            }
        };
        let axis = {
            x: {
                type: 'category',
            },
        };
        let padding = {
            top: 20,
        };

        return (
            <div>
                <C3Chart data={data}
                         grid={grid}
                         axis={axis}
                         legend={legend}
                         padding={padding}
                />
            </div>
        );
    }
}

export default BarGraph;