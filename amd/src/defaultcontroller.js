/**
 * Default controller for OER Collection - handles bulk actions and selection.
 *
 * @module     mod_oercollection/defaultcontroller
 * @copyright  2024 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {updateCardVisibility, updateResourceCounts, removeFromMoveModals} from 'mod_oercollection/cardutils';
import ajax from "core/ajax";
import {add as addToast} from 'core/toast';
import notification from "core/notification";
import {getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

export const init = () => {
    /**
     * Select or deselect all checkboxes.
     *
     * @param {boolean} status - True to select all, false to deselect all
     */
    const mod_oercollection_setall = (status) => {
        document.querySelectorAll('input[name="selectbox"]').forEach(checkbox => {
            checkbox.checked = status;
        });
        const nothingselected = document.getElementById("nothingselectedwarning");
        if (nothingselected) {
            nothingselected.classList.add("d-none");
        }
    };

    /**
     * Handle checkbox selection change.
     */
    const mod_oercollection_cb_selected = () => {
        const nothingselected = document.getElementById("nothingselectedwarning");
        const checkboxes = document.querySelectorAll('input[name="selectbox"]:checked');
        if (nothingselected && checkboxes.length > 0) {
            nothingselected.classList.add("d-none");
        }
    };


    /**
     * Handle bulk actions.
     *
     * @param {string} oer - The OER ID
     */
    const mod_oercollection_bulk_action = (oer) => {
        // Get all checked checkboxes and their associated data
        const checkboxes = document.querySelectorAll('input[name="selectbox"]:checked');
        const oerids = [];
        const rlinks = [];
        const rtitles = [];

        checkboxes.forEach(checkbox => {
            oerids.push(checkbox.value);
            const linkElement = document.getElementById(`resourcelink${checkbox.value}`);
            if (linkElement) {
                rlinks.push(linkElement.href);
            } else {
                rlinks.push("");
            }
            const titleElement = document.getElementById(`title${checkbox.value}`);
            if (titleElement) {
                rtitles.push(titleElement.value);
            } else {
                rtitles.push("");
            }
            checkbox.checked = false;
        });

        let bulkaction = parseInt(document.getElementById("editoptionselect").value, 10);

        const nothingselected = document.getElementById("nothingselectedwarning");
        if (checkboxes.length > 0) {
            nothingselected.classList.add("d-none");
        } else {
            nothingselected.classList.remove("d-none");
            bulkaction = 10;
        }

        switch (bulkaction) {
            case 1:
                // Open each link in a new tab
                for (let link of rlinks) {
                    const newWin = window.open(link);
                    if (!newWin || newWin.closed || typeof newWin.closed === 'undefined') {
                        getString('popupblockmessage', 'mod_oercollection').then(async function(infomessage) {
                            await addToast(infomessage, {
                                type: "danger",
                                delay: 10000
                            });
                            // Apply inline styles as backup in case CSS !important rules are overridden.
                            setTimeout(() => {
                                const wrapper = document.querySelector('.toast-wrapper');
                                const toastEl = wrapper?.firstElementChild;
                                if (toastEl) {
                                    toastEl.style.maxWidth = '95vw';
                                    toastEl.style.width = 'fit-content';
                                }
                            }, 0);
                        });
                        break;
                    }
                }
                break;
            case 2:
                // Set visibility to show
                setVisibility(oer, oerids, true, checkboxes.length);
                break;
            case 3:
                // Set visibility to hide
                setVisibility(oer, oerids, false, checkboxes.length);
                break;
            case 4:
                // Delete selected entries
                (async () => {
                    try {
                        const warningMessage = await getString('deletemultipopup', 'mod_oercollection', checkboxes.length);
                        const deleteTitle = await getString('deletewarning', 'mod_oercollection');
                        const deleteLabel = await getString('removeselected', 'mod_oercollection');

                        // Create modal with SAVE_CANCEL type
                        const modal = await ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: deleteTitle,
                            body: warningMessage,
                        });

                        modal.setSaveButtonText(deleteLabel);

                        // Handle delete button click
                        modal.getRoot().on(ModalEvents.save, () => {
                            deleteSelectedEntries(oer, oerids, checkboxes.length);
                        });

                        modal.getRoot().on(ModalEvents.hidden, () => {
                            modal.destroy();
                        });

                        modal.show();
                    } catch (error) {
                        notification.exception(error);
                    }
                })();
                break;
            case 5:
                // Add selected entries
                addSelectedEntries(oer, oerids, rlinks, rtitles, checkboxes.length);
                break;
            default:
                break;
        }
    };

    /**
     * Sets the visibility of selected OER entries.
     *
     * @param {string} oer - The ID of the OER (Open Educational Resource) to act upon.
     * @param {Array<string>} oerids - Array of IDs of the OER entries to update visibility for.
     * @param {boolean} show - Whether to show (true) or hide (false) the entries.
     * @param {number} count - Number of elements shown or hidden
     */
    async function setVisibility(oer, oerids, show, count) {
        try {
            // Count how many were actually in opposite state before changing
            let actuallyChanged = 0;
            oerids.forEach(entryId => {
                const card = document.querySelector(`.resource-frame[data-entry-id="${entryId}"]`);
                if (card) {
                    const wasHidden = card.classList.contains('bg-light');
                    if ((show && wasHidden) || (!show && !wasHidden)) {
                        actuallyChanged++;
                    }
                }
            });

            await ajax.call([{
                methodname: 'mod_oercollection_set_visibility_all',
                args: { oerid: oer, oerentryids: oerids, show: show }
            }])[0];

            // Update DOM for each card
            oerids.forEach(entryId => {
                updateCardVisibility(entryId, show);
            });

            // Update counts
            await updateResourceCounts(0, show ? -actuallyChanged : actuallyChanged);

            // Show notification
            const stringKey = show ? 'visibilityyesinfomessage' : 'visibilitynoinfomessage';
            const message = await getString(stringKey, 'mod_oercollection', count);
            await addToast(message, {
                type: "success",
                delay: 5000
            });
            setTimeout(() => {
                const wrapper = document.querySelector('.toast-wrapper');
                const toastEl = wrapper?.firstElementChild;
                if (toastEl) {
                    toastEl.style.maxWidth = '95vw';
                    toastEl.style.width = 'fit-content';
                }
            }, 0);
        } catch (error) {
            notification.exception(error);
        }
    }

    /**
     * Deletes the selected OER entries.
     *
     * @param {string} oer - The ID of the OER (Open Educational Resource) to act upon.
     * @param {Array<string>} oerids - Array of IDs of the OER entries to delete.
     * @param {number} count - The number of entries to be deleted.
     */
    async function deleteSelectedEntries(oer, oerids, count) {
        try {
            // Count how many were hidden before deleting
            let hiddenCount = 0;
            oerids.forEach(entryId => {
                const card = document.querySelector(`.resource-frame[data-entry-id="${entryId}"]`);
                if (card?.classList.contains('bg-light')) {
                    hiddenCount++;
                }
            });

            await ajax.call([{
                methodname: 'mod_oercollection_delete_selected_oerentries',
                args: { oerid: oer, oerentryids: oerids }
            }])[0];

            // Remove cards from DOM
            oerids.forEach(entryId => {
                const card = document.querySelector(`.resource-frame[data-entry-id="${entryId}"]`);
                const modal = document.getElementById(`moveModal${entryId}`);
                if (card) {
                    card.remove();
                }
                if (modal) {
                    modal.remove();
                }
            });

            oerids.forEach(id => removeFromMoveModals(id));

            // Update count, total decreases by count, hidden decreases by hiddenCount
            await updateResourceCounts(-count, -hiddenCount);

            // Show notification
            const message = await getString('deleteinfomessage', 'mod_oercollection', count);
            await addToast(message, {
                type: "success",
                delay: 5000
            });
            setTimeout(() => {
                const wrapper = document.querySelector('.toast-wrapper');
                const toastEl = wrapper?.firstElementChild;
                if (toastEl) {
                    toastEl.style.maxWidth = '95vw';
                    toastEl.style.width = 'fit-content';
                }
            }, 0);
        } catch (error) {
            notification.exception(error);
        }
    }

    /**
     * Adds selected OER entries to collection.
     *
     * @param {string} oerid - The ID of the OER (Open Educational Resource) to act upon.
     * @param {Array<string>} oerhubids - The number of entries to be deleted.
     * @param {Array<string>} resourcelinks - The ID of the OER (Open Educational Resource) to act upon.
     * @param {Array<string>} resourcenames - The ID of the OER (Open Educational Resource) to act upon.
     * @param {number} nadded - The number of entries to be deleted.
     */
    function addSelectedEntries(oerid, oerhubids, resourcelinks, resourcenames, nadded) {
        ajax.call([{
            methodname: 'mod_oercollection_add_selected_oerentries',
            args: {oerid: oerid, oerhubids: oerhubids, resourcelinks: resourcelinks, resourcenames: resourcenames},
            done: function(returnval) {
                if (returnval.incollectionnr >= 1) {
                    const added = nadded - returnval.incollectionnr;
                    const notadded = returnval.incollectionnr;
                    getString('multiaddedinfomessage', 'mod_oercollection', {added: added, notadded: notadded}).then(
                        async function(infomessage) {
                            await addToast(infomessage, {
                                type: "success",
                                delay: 5000
                            });
                            setTimeout(() => {
                                const wrapper = document.querySelector('.toast-wrapper');
                                const toastEl = wrapper?.firstElementChild;
                                if (toastEl) {
                                    toastEl.style.maxWidth = '95vw';
                                    toastEl.style.width = 'fit-content';
                                }
                            }, 0);
                        });
                } else {
                    getString('addedinfomessage', 'mod_oercollection', nadded).then(async function(infomessage) {
                        await addToast(infomessage, {
                            type: "success",
                            delay: 5000
                        });
                        setTimeout(() => {
                            const wrapper = document.querySelector('.toast-wrapper');
                            const toastEl = wrapper?.firstElementChild;
                            if (toastEl) {
                                toastEl.style.maxWidth = '95vw';
                                toastEl.style.width = 'fit-content';
                            }
                        }, 0);
                    });
                }
            },
            fail: notification.exception
        }]);
    }

    // Handle select all/none via event delegation
    document.addEventListener('click', (e) => {
        const selectAllElement = e.target.closest('[data-action="select-all"]');
        if (selectAllElement) {
            e.preventDefault();
            const selectAll = selectAllElement.dataset.select === 'true';
            mod_oercollection_setall(selectAll);
            return;
        }

        // Handle bulk action button via event delegation
        const bulkActionElement = e.target.closest('[data-action="bulk-action"]');
        if (bulkActionElement) {
            e.preventDefault();
            const oerId = bulkActionElement.dataset.oerId;
            mod_oercollection_bulk_action(oerId);
        }
    });

    // Handle checkbox selection change via delegation (REPLACES inline onchange)
    document.addEventListener('change', (e) => {
        if (e.target.matches('input[name="selectbox"]')) {
            mod_oercollection_cb_selected();
        }
    });
};
