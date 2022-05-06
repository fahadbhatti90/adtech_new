import React, {useEffect, useState} from 'react'
import { LinearIndeterminate } from '../DT-Linear-ProgressBar/DataTablePB';
import {columns} from './Helper';
import { getKeywords } from './apiCalls';
import Card from '@material-ui/core/Card';
import SearchIcon from '@material-ui/icons/Search';
import DataTable from 'react-data-table-component';
import {withStyles} from "@material-ui/core/styles";
import {styles} from "./../../components/Admin-Module/Manage-Users/styles";

function KeywordsTable({
    selectedRow,
    classes,
    url
}) {
    const [state, setState] = useState({
        data:[],
        originalData:[],
        totalRows:0,
        loading:true,
    });
    useEffect(() => {
        getKeywords(
            `${url}/${selectedRow.fkMultiplierListId ?? selectedRow.fkTacosId}/keywords`,
            (reponse)=>{
                console.log("response::", reponse.data);
                setState({  
                    data:reponse.data,
                    originalData:reponse.data,
                    totalRows:reponse.data.length,
                    loading:false,
                })
            },
            (error)=>{
                console.error(error)
                setState(prevState=>({  
                    ...prevState,
                    loading:false,
                }))
            }
        );

    }, [])
    
    const onDataTableSearch = (e) => {
        if (e.target.value.length > 0) {
            let result = state.originalData.filter(row => {
                return row?.keywordText?.toString().toLowerCase().includes(e.target.value.toLowerCase()) ||
                    row?.keywordId?.toLowerCase().includes(e.target.value.toLowerCase()) ||
                    row?.oldBid?.toLowerCase().includes(e.target.value.toLowerCase()) ||
                    row?.bid?.toLowerCase().includes(e.target.value.toLowerCase()) ||
                    row?.bidOptimizationValue?.toLowerCase().includes(e.target.value.toLowerCase()) ||
                    row?.creationDate?.toString().toLowerCase().includes(e.target.value.toLowerCase())
            });
            setState(prevState => ({
                ...prevState,
                data: result,
                totalRows: result.length
            }))
        } else {
            setState(prevState => ({
                ...prevState,
                data: state.originalData,
                totalRows: state.originalData.length
            }))
        }
    }

    return (
        <div>
           <div style={{display: 'table', tableLayout: 'fixed', width: '100%'}} className="managerUser">
                <Card className="overflow-hidden" classes={{root: classes.card}}>
                    <div className="flex p-5">
                        <div className="font-semibold w-3/12">Keywords List</div>
                        <div className="searchDataTable w-9/12">
                            <div
                                className="border border-gray-300 border-solid flex inputGroup mr-4 px-3 py-1 rounded-full w-9/12 ml-auto">
                                <input type="text"
                                       className="border-0 flex-1 focus:outline-none font-semibold outline-none px-2 text-xs"
                                       placeholder="Search"
                                       onChange={onDataTableSearch}
                                />
                                <SearchIcon className="text-gray-300"/>
                            </div>
                        </div>
                    </div>
                    <div className={"w-full dataTableContainer"}>
                        <DataTable
                            Clicked
                            noHeader={true}
                            wrap={false}
                            responsive={true}
                            columns={columns()}
                            data={state.data}
                            pagination
                            paginationTotalRows={state.totalRows}
                            progressPending={state.loading}
                            progressComponent={<LinearIndeterminate/>}
                            persistTableHead
                        />
                    </div>
                </Card>
            </div>
            

        </div>
    )
}

export default withStyles(styles) (KeywordsTable);