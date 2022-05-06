import React, { useState, useEffect } from 'react'

export default function useFilter(dataTableRef, resetSelectedAsinsState, initialState) {
    const [state, setState] = useState({
        showFilter:false,
        ...initialState,
    })
    useEffect(() => {
        dataTableRef.current && dataTableRef.current.helperReloadDataTable && dataTableRef.current.helperReloadDataTable();
        if(resetSelectedAsinsState)
        resetSelectedAsinsState();
    }, [state.filter])
    const applyFilterOnTable = (filter) => {
        setState((prevState)=>({
                ...prevState,
                filter
        }));
    }
    const handleApplyFilterButtonClick = (e) => {
        setState((prevState)=>({
            ...prevState,
            showFilter:!state.showFilter
        }))
    }
    const helperLoadFilterAgain = () =>{
        if(state.showFilter)
            setState((prevState)=>{ 
                return {
                    ...prevState,
                    showFilter:false,
                }
            });
            
            setState((prevState)=>{ 
                return {
                    ...prevState,
                    showFilter:true,
                }
            })  
    }
    return {
        filter:state.filter,
        showFilter:state.showFilter,
        applyFilterOnTable,
        helperLoadFilterAgain,
        handleApplyFilterButtonClick,
    }
}
