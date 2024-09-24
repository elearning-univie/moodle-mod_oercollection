import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";
import {getString} from 'core/str';

export const init = () => {
    $.mod_oercollection_setall = function(status) {
        document.querySelectorAll('input[name="selectbox"]').forEach(checkbox => {
            checkbox.checked = status;
        });
    };

    $.mod_oercollection_bulk_action = function(oer) {
        // Get all checked checkboxes and their associated data
        const checkboxes = document.querySelectorAll('input[name="selectbox"]:checked');
        const oerids = [];
        const rlinks = [];

        var empty = 0;
        checkboxes.forEach(checkbox => {
        empty++;
            oerids.push(checkbox.value);
            const linkElement = document.getElementById(`resourcelink${checkbox.value}`);
            if (linkElement) {
                rlinks.push(linkElement.href);
            }
        });

        if (empty == 0) {
            getString('noselection', 'mod_oercollection').then(function (warningmessage) {
            alert(warningmessage);
            });
        }

        const bulkaction = parseInt(document.getElementById("editoptionselect").value, 10);

        switch (bulkaction) {
            case 1:
                // Open each link in a new tab
                rlinks.forEach(link => window.open(link, '_blank'));
                break;
            case 2:
                // Set visibility to show
                setVisibility(oer, oerids, true);
                break;
            case 3:
                // Set visibility to hide
                setVisibility(oer, oerids, false);
                break;
            case 4:
                // Delete selected entries
                deleteSelectedEntries(oer, oerids, checkboxes.length);
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
     */
    function setVisibility(oer, oerids, show) {
        ajax.call([{
            methodname: 'mod_oercollection_set_visibility_all',
            args: { oerid: oer, oerentryids: oerids, show: show },
            done: () => location.reload(),
            fail: notification.exception
        }]);
    }

    /**
     * Deletes the selected OER entries.
     *
     * @param {string} oer - The ID of the OER (Open Educational Resource) to act upon.
     * @param {Array<string>} oerids - Array of IDs of the OER entries to delete.
     * @param {number} todelete - The number of entries to be deleted.
     */
    function deleteSelectedEntries(oer, oerids, todelete) {
        ajax.call([{
            methodname: 'mod_oercollection_delete_selected_oerentries',
            args: { oerid: oer, oerentryids: oerids },
            done: () => {
                const queryParams = new URLSearchParams(window.location.search);
                queryParams.set("delete", todelete);
                history.replaceState(null, null, `?${queryParams.toString()}`);
                location.reload();
            },
            fail: notification.exception
        }]);
    }
};
