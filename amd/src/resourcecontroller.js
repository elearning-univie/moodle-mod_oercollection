/**
 * Resource controller for OER Collection - handles single resource actions.
 *
 * @module     mod_oercollection/resourcecontroller
 * @copyright  2024 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {updateCardVisibility, updateResourceCounts, removeFromMoveModals} from 'mod_oercollection/cardutils';
import ajax from "core/ajax";
import notification from "core/notification";
import {add as addToast} from 'core/toast';
import {getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

/**
 * Show a notification using existing language strings.
 *
 * @param {string} stringKey - Language string key
 * @param {number|object} param - Parameter for the string
 * @param {string} type - Notification type: success, info, warning, danger
 */
const showNotification = async (
    stringKey,
    param = 1,
    type = "success"
    ) => {
    try {
        const message = await getString(stringKey, 'mod_oercollection', param);
        await addToast(message, {
            type: type,
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
};

/**
 * Remove a card from the DOM after delete action.
 *
 * @param {string} entryId - The entry ID
 */
const removeCardFromDOM = (entryId) => {
    const card = document.querySelector(`.resource-frame[data-entry-id="${entryId}"]`);
    const modal = document.getElementById(`moveModal${entryId}`);

    if (card) {
        card.remove();
    }
    if (modal) {
        modal.remove();
    }
};

/**
 * Handle visibility toggle action.
 *
 * @param {HTMLElement} element - The clicked element
 */
const handleVisibilityToggle = async (element) => {
    const oerId = element.dataset.oerId;
    const entryId = element.dataset.entryId;
    const willBeVisible = element.dataset.show === '1';

    try {
        await ajax.call([{
            methodname: 'mod_oercollection_set_visibility_oerentry',
            args: { oerid: oerId, oerentryid: entryId }
        }])[0];

        updateCardVisibility(entryId, willBeVisible);
        // Update counts
        await updateResourceCounts(0, willBeVisible ? -1 : 1);
        await showNotification(willBeVisible ? 'visibilityyesinfomessage' : 'visibilitynoinfomessage');
    } catch (error) {
        notification.exception(error);
    }
};

/**
 * Handle delete resource action.
 *
 * @param {HTMLElement} element - The clicked element
 */
const handleDeleteResource = async (element) => {
    const oerId = element.dataset.oerId;
    const entryId = element.dataset.entryId;
    const resourceName = element.dataset.resourceName;

    try {
        const warningMessage = await getString('deletepopup', 'mod_oercollection', resourceName);
        const deleteTitle = await getString('deletewarning', 'mod_oercollection');
        const deleteLabel = await getString('removeoer', 'mod_oercollection');

        // Create modal with SAVE_CANCEL type
        const modal = await ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: deleteTitle,
            body: warningMessage,
        });

        // Change the save button text
        modal.setSaveButtonText(deleteLabel);

        // Handle save (delete) button click
        modal.getRoot().on(ModalEvents.save, async () => {
            try {
                // Check if resource was hidden before deleting
                const card = document.querySelector(`.resource-frame[data-entry-id="${entryId}"]`);
                const wasHidden = card?.classList.contains('bg-light') ?? false;

                await ajax.call([{
                    methodname: 'mod_oercollection_delete_oerentry',
                    args: {oerid: oerId, oerentryid: entryId}
                }])[0];

                removeCardFromDOM(entryId);
                removeFromMoveModals(entryId);
                // Update counts, total decreases by 1, hidden decreases by 1 if was hidden
                await updateResourceCounts(-1, wasHidden ? -1 : 0);
                await showNotification('deleteinfomessage');
            } catch (error) {
                notification.exception(error);
            }
        });

        // Clean up modal when hidden
        modal.getRoot().on(ModalEvents.hidden, () => {
            modal.destroy();
        });

        // Show the modal
        modal.show();
    } catch (error) {
        notification.exception(error);
    }
};

/**
 * Handle move resource action.
 *
 * @param {HTMLElement} element - The clicked element
 */
const handleMoveResource = async (element) => {
    const oerId = element.dataset.oerId;
    const entryId = element.dataset.entryId;
    const moveAfter = element.dataset.moveAfter;

    try {
        await ajax.call([{
            methodname: 'mod_oercollection_move_resource',
            args: {
                oerid: oerId,
                oereidtomove: entryId,
                oereidmoveafter: moveAfter
            }
        }])[0];

        sessionStorage.setItem('showMoveToast', 'true');
        location.reload();
    } catch (error) {
        notification.exception(error);
    }
};

/**
 * Initialize action handlers using event delegation.
 */
const initActionHandlers = () => {
    document.addEventListener('click', async (e) => {
        const actionElement = e.target.closest('[data-action]');
        if (!actionElement) {
            return;
        }

        // Only handle our specific actions
        const action = actionElement.dataset.action;
        const validActions = ['toggle-visibility', 'delete-resource', 'move-resource'];
        if (!validActions.includes(action)) {
            return;
        }

        e.preventDefault();

        switch (action) {
            case 'toggle-visibility':
                await handleVisibilityToggle(actionElement);
                break;
            case 'delete-resource':
                await handleDeleteResource(actionElement);
                break;
            case 'move-resource':
                await handleMoveResource(actionElement);
                break;
        }
    });
};

/**
 * Text truncation for descriptions and comments.
 */
const initTextTruncation = async () => {
    const showMoreStr = await getString('showmore', 'core');
    const showLessStr = await getString('showless', 'core');

    const isOverflowing = (element) => {
        return element.scrollHeight > element.clientHeight;
    };

    /**
     * Initialize truncation for a specific selector.
     *
     * @param {string} textSelector - Selector for text elements
     * @param {string} toggleSelector - Selector for toggle links
     * @param {string} idAttribute - Attribute used to match text and toggle
     */
    const initTruncationForSelector = (textSelector, toggleSelector, idAttribute) => {
        const elements = document.querySelectorAll(textSelector);

        elements.forEach((textElement) => {
            if (isOverflowing(textElement)) {
                const id = textElement.getAttribute(idAttribute);
                const toggleLink = document.querySelector(`${toggleSelector}[${idAttribute}="${id}"]`);

                if (toggleLink) {
                    toggleLink.classList.remove('d-none');
                    const textSpan = toggleLink.querySelector('.toggle-text');
                    const iconElement = toggleLink.querySelector('.toggle-icon');

                    if (!textSpan || !iconElement) {
                        return;
                    }

                    // Initial state
                    textSpan.textContent = showMoreStr;
                    iconElement.className = 'fa fa-chevron-down toggle-icon';

                    toggleLink.addEventListener('click', (e) => {
                        e.preventDefault();

                        const isExpanded = toggleLink.getAttribute('data-expanded') === 'true';

                        if (isExpanded) {
                            // Collapse
                            textElement.classList.remove('expanded');

                            if (textSelector === '.oer-comment-text') {
                                textElement.style.display = '-webkit-box';
                                textElement.style.webkitLineClamp = '3';
                                textElement.style.webkitBoxOrient = 'vertical';
                                textElement.style.overflow = 'hidden';
                                textElement.style.maxHeight = '4.40em';
                            }

                            toggleLink.setAttribute('data-expanded', 'false');
                            toggleLink.setAttribute('aria-expanded', 'false');
                            textSpan.textContent = showMoreStr;
                            iconElement.className = 'fa fa-chevron-down toggle-icon';

                        } else {
                            // Expand
                            textElement.classList.add('expanded');

                            if (textSelector === '.oer-comment-text') {
                                textElement.style.display = 'block';
                                textElement.style.webkitBoxOrient = '';
                                textElement.style.overflow = 'visible';
                                textElement.style.maxHeight = 'none';
                            }

                            toggleLink.setAttribute('data-expanded', 'true');
                            toggleLink.setAttribute('aria-expanded', 'true');
                            textSpan.textContent = showLessStr;
                            iconElement.className = 'fa fa-chevron-up toggle-icon';
                        }
                    });
                }
            }
        });
    };

    requestAnimationFrame(() => {
        setTimeout(() => {
            initTruncationForSelector('.oer-description', '.oer-description-toggle', 'data-resource-id');
            initTruncationForSelector('.oer-comment-text', '.oer-comment-toggle', 'data-entry-id');
        }, 100);
    });
};

/**
 * Initialize the resource controller.
 */
export const init = () => {
    // Text truncation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTextTruncation);
    } else {
        initTextTruncation();
    }

    // Action handlers via event delegation
    initActionHandlers();

    // Show "move" toast if flagged
    if (sessionStorage.getItem('showMoveToast')) {
        showNotification('movedinfomessage');
        sessionStorage.removeItem('showMoveToast');
    }
};
