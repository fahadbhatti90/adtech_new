import React, {useState, useEffect} from "react";
import {Helmet} from "react-helmet";
import Card from "@material-ui/core/Card";
import {withStyles} from "@material-ui/core/styles";
import {styles} from './style';
import {connect} from "react-redux";
import AddBudgetRuleForm from "./Add/AddBudgetRuleForm";
import BudgetRuleDataTables from "./dataTables/BudgetRuleDataTables";
import './budgetRule.scss';
function BudgetRule (props){

    const [DataTableReload, setDataTableReload] = useState(false);

    const classes = props;
    const updateDataTable = () => {
        setDataTableReload(!DataTableReload);
    }
    const updateDataTableAfterSubmit = () => {
        setDataTableReload(!DataTableReload);
    }
    return(
        <>
            <div>
                <Helmet>
                    <title>Pulse Advertising | Budget Rule</title>
                </Helmet>

                <div className="budgetRuleModule">
                    <Card classes={{root: classes.card}}>
                        <div className="pt-5 pl-5"> Budget Rule</div>
                        <AddBudgetRuleForm updateDataTable ={updateDataTableAfterSubmit} />
                    </Card>
                    <div className={' mt-12'}></div>
                    <Card classes={{root: classes.tableCard}}>
                        <BudgetRuleDataTables
                            isDataTableReload ={DataTableReload}
                            updateDataTable ={updateDataTable}
                        />
                    </Card>
                </div>
            </div>

        </>
    )
}

export default withStyles(styles) (connect(null)(BudgetRule))