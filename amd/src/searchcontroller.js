/**
 * Search controller for OER Collection - handles single "add to collection" action.
 *
 * @module     mod_oercollection/searchcontroller
 * @copyright  2024 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ajax from "core/ajax";
import notification from "core/notification";
import {add as addToast} from 'core/toast';
import {getString} from 'core/str';

/**
 * Handle adding a single resource to the collection.
 *
 * @param {HTMLElement} element - The clicked element with data attributes
 */
const handleAddToCollection = async (element) => {
    const oerId = element.dataset.oerId;
    const oerhubId = element.dataset.oerhubId;
    const resourceLink = element.dataset.resourceLink;
    const resourceTitle = element.dataset.resourceTitle;

    try {
        const result = await ajax.call([{
            methodname: 'mod_oercollection_add_to_collection',
            args: {
                oerid: oerId,
                oerhubid: oerhubId,
                resourcelink: resourceLink,
                resourcename: resourceTitle
            }
        }])[0];

        if (result.alreadyincollection === 1) {
            const message = await getString('resourceexistsinfomessage', 'mod_oercollection');
            await addToast(message, {type: 'warning', delay: 5000});
        } else {
            const message = await getString('addedinfomessage', 'mod_oercollection', 1);
            await addToast(message, {type: 'success', delay: 5000});
        }
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
};

/**
 * Initialize event delegation for search page actions.
 */
const initActionHandlers = () => {
    document.addEventListener('click', async (e) => {
        const actionElement = e.target.closest('[data-action="add-to-collection"]');
        if (!actionElement) {
            return;
        }

        e.preventDefault();
        await handleAddToCollection(actionElement);
    });
};

export const init = () => {
    initActionHandlers();
};
