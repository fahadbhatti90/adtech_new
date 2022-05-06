import React, {useRef } from 'react';
import './NotesTable.scss';
import ArrowLeftIcon from '@material-ui/icons/ArrowLeft';
import ArrowRightIcon from '@material-ui/icons/ArrowRight';
import ArrowBottomIcon from '@material-ui/icons/ArrowDropDown';

export default function NotesTable({
    notes
}) {
    const notesTableRef = useRef(null);
 
    return (
        <div class="notesTable" ref = {notesTableRef}> 
            <ArrowLeftIcon className="arrowLeft"/>
            <div class="notesTableHeader">
                <div class="notesTableCol">Date</div>
                <div class="notesTableCol">Note</div>
            </div>
            <div class="notesTableBody">
                {
                    notes && notes.length > 0 && notes.map(note => 
                        <div class="notesTableBodyRow">
                            <div class="notesTableCol">{note.occurrenceDate}</div>
                            <div class="notesTableCol">{note.notes === "NA" ? "No Notes Available": note.notes}</div>
                        </div>
                        )
                }
            </div>
            <ArrowRightIcon className="arrowRight"/>
            <ArrowBottomIcon className="arrowBottom"/>
        </div>
    )
}
