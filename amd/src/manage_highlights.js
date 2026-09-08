// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Opens the highlight add/edit form in a modal and performs the
 * toggle/delete row actions via fetch() to action.php, refreshing the
 * highlights table fragment (see lib.php) after each of these, without a
 * full page reload.
 *
 * @module     filter_externalcontent/manage_highlights
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import Fragment from 'core/fragment';
import Templates from 'core/templates';
import Notification, {deleteCancelPromise} from 'core/notification';
import * as Str from 'core/str';

const FORM_CLASS = 'filter_externalcontent\\local\\form\\edit';
const TABLE_CONTAINER_SELECTOR = '#filter-externalcontent-highlights-table';

/**
 * Re-fetch and swap in the highlights table, so it reflects the latest
 * add/edit/delete/toggle without reloading the whole settings page.
 *
 * @param {Number} contextId
 * @return {Promise}
 */
const refreshTable = (contextId) => {
    return Fragment.loadFragment('filter_externalcontent', 'highlights_table', contextId, {})
        .then((html, js) => {
            Templates.replaceNodeContents(TABLE_CONTAINER_SELECTOR, html, js);
        })
        .catch(Notification.exception);
};

/**
 * Open the add/edit modal form for a given highlight id (empty for a new one).
 *
 * @param {HTMLElement} trigger element that triggered the modal, focus is returned to it on close
 * @param {String} id highlight id, or '' to add a new highlight
 * @param {Number} contextId
 */
const showForm = (trigger, id, contextId) => {
    const titleKey = id === '' ? 'add_heading' : 'edit_heading';
    const modalForm = new ModalForm({
        formClass: FORM_CLASS,
        args: {id},
        modalConfig: {title: Str.get_string(titleKey, 'filter_externalcontent')},
        returnFocus: trigger,
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => refreshTable(contextId));

    modalForm.show();
};

/**
 * Call the given action.php link via fetch(), identifying the request as
 * AJAX so action.php can skip its redirect (see action.php for details).
 *
 * @param {String} url
 * @return {Promise}
 */
const performAction = (url) => fetch(url, {
    credentials: 'same-origin',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
}).then((response) => {
    if (!response.ok) {
        throw new Error(`${response.status} ${response.statusText}`);
    }
    return response;
});

/**
 * Toggle a highlight's enabled state, then refresh the table.
 *
 * @param {HTMLAnchorElement} trigger
 * @param {Number} contextId
 */
const toggleHighlight = (trigger, contextId) => {
    performAction(trigger.href)
        .then(() => refreshTable(contextId))
        .catch(Notification.exception);
};

/**
 * Confirm, then delete a highlight, then refresh the table.
 *
 * @param {HTMLAnchorElement} trigger
 * @param {Number} contextId
 */
const deleteHighlight = (trigger, contextId) => {
    deleteCancelPromise(
        Str.get_string('delete_heading', 'filter_externalcontent'),
        Str.get_string('delete_confirm', 'filter_externalcontent', trigger.dataset.highlightName),
        Str.get_string('delete', 'core'),
        {triggerElement: trigger},
    ).then(() => {
        // Only errors from the actual delete + refresh should be reported;
        // a rejection from deleteCancelPromise above (user cancelled) is
        // swallowed by the outer catch below.
        return performAction(trigger.href).then(() => refreshTable(contextId));
    }).catch((e) => {
        if (e instanceof Error) {
            Notification.exception(e);
        }
    });
};

/**
 * Initialise the module: bind click handlers for the "Add" button and each
 * row's "Edit"/toggle/"Delete" action icons.
 *
 * @param {Number} contextId
 */
export const init = (contextId) => {
    document.addEventListener('click', (e) => {
        const addTrigger = e.target.closest('[data-action="add-highlight"]');
        if (addTrigger) {
            e.preventDefault();
            showForm(addTrigger, '', contextId);
            return;
        }

        const editTrigger = e.target.closest('[data-action="edit-highlight"]');
        if (editTrigger) {
            e.preventDefault();
            showForm(editTrigger, editTrigger.dataset.id, contextId);
            return;
        }

        const toggleTrigger = e.target.closest('[data-action="toggle-highlight"]');
        if (toggleTrigger) {
            e.preventDefault();
            toggleHighlight(toggleTrigger, contextId);
            return;
        }

        const deleteTrigger = e.target.closest('[data-action="delete-highlight"]');
        if (deleteTrigger) {
            e.preventDefault();
            deleteHighlight(deleteTrigger, contextId);
        }
    });
};
