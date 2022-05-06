import CellTooltip from "../../../../general-components/dataTableToolTip/CellTooltip";
import React from "react";
import ActionButton from "../ActionButton";
import Link from '@material-ui/core/Link';


const PermissionButton = (props) => {
    const viewPermission = () => {
        props.viewPermission(props.row)
    }
    return (
        <>
            <Link
                className={"text-gray-600"}
                component="button"
                variant="body2"
                onClick={viewPermission}
            >
                View Permissions
            </Link>
        </>
    )
}
export const alertColumns = (deleteAlert, editAlert, viewPermission) => [
    {
        name: 'Sr.#',
        selector: 'serial',
        sortable: true,
        maxWidth: "100px"
    },
    {
        name: 'Alert Name',
        selector: 'alertName',
        sortable: true,
        maxWidth: "200px",
        wrap: true,
        cell: row => <CellTooltip
            title={row.alertName}
            placement="top"
            limit={14}
        />
    },
    {
        name: 'Child Brand',
        selector: 'accounts',
        sortable: true,
        wrap: true
    },
    {
        name: 'Permissions',
        selector: 'permission',
        sortable: true,
        wrap: true,
        cell: row => <PermissionButton row={row} viewPermission={viewPermission} />
    },
    {
        name: 'Cc Emails',
        selector: 'addCC',
        cell: row => <CellTooltip
            title={row.addCC}
            placement="top"
            limit={14}
        />,
        sortable: true,
        wrap: true,
        maxWidth: "200px"
    },
    {
        name: "Actions",
        cell: row => <ActionButton
            row={row}
            deleteAlert={deleteAlert}
            editAlert={editAlert}
        />,
        ignoreRowClick: true,
        allowOverflow: true,
        button: true,
        minWidth: "200px"
    },
];