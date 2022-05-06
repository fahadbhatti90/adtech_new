import React from "react";
import CellTooltip from "./../dataTableToolTip/CellTooltip";

export const columns =()=>[
    {
        name: 'keyword Id',
        selector: 'keywordId',
        sortable: true,
        maxWidth:"150px"
    },
    {
        name: 'Keyword Text',
        selector: 'keywordText',
        sortable: true,
        maxWidth:"200px",
        wrap:true,	
        cell:row => <CellTooltip 
                        title={row.keywordText?? "NA"} 
                        placement="top"
                        limit={14}
                    />
    },
    {
        name: 'Optimization Value',
        selector: 'bidOptimizationValue',
        sortable: true,
        wrap:true
    },
    {
        name: 'Old Bid',
        selector: 'oldBid',
        sortable: true,
        wrap:true
    },
    {
        name: 'Bid',
        selector: 'bid',
        sortable: true,
        wrap:true
    },
    {
        name: 'Creation Date',
        selector: 'creationDate',
        sortable: true,
        wrap:true,
        maxWidth:"200px"
    },
];