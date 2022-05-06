import React, { Component } from 'react';
import C3Chart from 'react-c3js';
import 'c3/c3.css';
import * as d3 from 'd3';
//import "./../MultiComCards/styles.scss";

class AreaGraph extends Component {
    constructor(props){
        super(props);
    }

    render() {
        let data = {
            x: 'x',
            columns: [
                ["x",...this.props.categories],
                ...this.props.dataChart
            ],
            empty: {
                label: {
                    text: "No Data"
                }
            },
            colors: {
                'Mandatory ID':'#571986',
                'Report ID':'#FF7F0E',
            },
            types: {
                'Mandatory ID': 'area',
                'Report ID': 'area-spline'
            }
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
                <C3Chart 
                    data={data}
                    grid={grid}
                    axis={axis}
                    legend={legend}
                    padding={padding}
                />
            </div>
        );
    }
}

export default AreaGraph;