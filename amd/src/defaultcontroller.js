import ajax from "core/ajax";
import notification from "core/notification";
import {getString} from 'core/str';

export const init = () => {
    const mod_oercollection_setall = (status) => {
        document.querySelectorAll('input[name="selectbox"]').forEach(checkbox => {
            checkbox.checked = status;
        });
    };

    const mod_oercollection_bulk_action = (oer) => {
        // Get all checked checkboxes and their associated data
        const checkboxes = document.querySelectorAll('input[name="selectbox"]:checked');
        const oerids = [];
        const rlinks = [];
        const rtitles = [];

        var empty = 0;
        checkboxes.forEach(checkbox => {
            empty++;
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

        if (empty == 0) {
            getString('noselection', 'mod_oercollection').then(function (warningmessage) {
            alert(warningmessage);
            });
        }

        const bulkaction = parseInt(document.getElementById("editoptionselect").value, 10);

        switch (bulkaction) {
            case 1:
                // Open each link in a new tab
                //rlinks.forEach(link => window.open(link, '_blank'));
                for (let link of rlinks) {
                   var newWin = window.open(link);
                   if(!newWin || newWin.closed || typeof newWin.closed=='undefined') {
                      getString('popupblockmessage', 'mod_oercollection').then(function (infomessage) {
                         notification.addNotification({
                         message: infomessage,
                         type: "danger"
                         });
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
                deleteSelectedEntries(oer, oerids, checkboxes.length);
                break;
            case 5:
                // Delete selected entries
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
     * @param {number} vn - Number of elements shown or hidden
     */
    function setVisibility(oer, oerids, show, vn) {
        ajax.call([{
            methodname: 'mod_oercollection_set_visibility_all',
            args: { oerid: oer, oerentryids: oerids, show: show},
            done: () => {
                const queryParams = new URLSearchParams(window.location.search);
                if (show == 1) {
                    queryParams.set("vyes", vn);
                } else {
                    queryParams.set("vno", vn);
                }
                history.replaceState(null, null, `?${queryParams.toString()}`);
                location.reload();
            },
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
    /**
     * Deletes the selected OER entries.
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
            done: function (returnval) {
                const queryParams = new URLSearchParams(window.location.search);
                queryParams.set("nadded", nadded);
                history.replaceState(null, null, `?${queryParams.toString()}`);
                if (returnval.incollectionnr >= 1) {
                    var added = nadded - returnval.incollectionnr;
                    var notadded = returnval.incollectionnr;
                    getString('multiaddedinfomessage', 'mod_oercollection', {added: added, notadded: notadded}).then(
                       function (infomessage) {
                       notification.addNotification({
                       message: infomessage,
                       type: "warning"
                       });
                    });
                } else {
                    getString('addedinfomessage', 'mod_oercollection', nadded).then(function (infomessage) {
                       notification.addNotification({
                       message: infomessage,
                       type: "success"
                       });
                    });
              }
            },
            fail: notification.exception
        }]);
    }

    window.mod_oercollection_setall = mod_oercollection_setall;
    window.mod_oercollection_bulk_action = mod_oercollection_bulk_action;
};
