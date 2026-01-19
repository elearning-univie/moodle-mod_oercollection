/**
 * Shared card utilities for OER Collection.
 *
 * @module     mod_oercollection/cardutils
 * @copyright  2024 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';

/**
 * Update the resource counts display in the DOM.
 *
 * @param {number} totalDelta - Change in total count
 * @param {number} hiddenDelta - Change in hidden countS
 */
export const updateResourceCounts = async (totalDelta, hiddenDelta) => {
    const countsElement = document.getElementById('oer-resource-counts');
    if (!countsElement) {
        return;
    }

    const currentTotal = parseInt(countsElement.dataset.total, 10) || 0;
    const currentHidden = parseInt(countsElement.dataset.hidden, 10) || 0;

    const newTotal = Math.max(0, currentTotal + totalDelta);
    const newHidden = Math.max(0, currentHidden + hiddenDelta);

    // Update data attributes
    countsElement.dataset.total = newTotal;
    countsElement.dataset.hidden = newHidden;

    try {
        const message = await getString('availableresourcesnumberinfo', 'mod_oercollection', {
            total: newTotal,
            hidden: newHidden
        });
        countsElement.textContent = message;
    } catch (error) {
        // Fallback if string fetch fails
        countsElement.textContent = `${newTotal} available resources (${newHidden} hidden)`;
    }
};

/**
 * Update card visibility in DOM.
 * @param {string} entryId - The entry ID
 * @param {boolean} isNowVisible - New visibility state
 */
export const updateCardVisibility = (entryId, isNowVisible) => {
    const card = document.querySelector(`.resource-frame[data-entry-id="${entryId}"]`);
    if (!card) {
        return;
    }

    card.classList.toggle('bg-light', !isNowVisible);

    const badge = card.querySelector('.oer-hidden-badge');
    if (badge) {
        badge.style.display = isNowVisible ? 'none' : '';
    }

    const showAction = card.querySelector('[data-action="toggle-visibility"][data-show="1"]');
    const hideAction = card.querySelector('[data-action="toggle-visibility"][data-show="0"]');
    if (showAction) {
        showAction.style.display = isNowVisible ? 'none' : '';
    }
    if (hideAction) {
        hideAction.style.display = isNowVisible ? '' : 'none';
    }
};

/**
 * Remove deleted resource from all move modals.
 *
 * @param {number|string} deletedEntryId
 */
export const removeFromMoveModals = (deletedEntryId) => {
    document.querySelectorAll(
        `[data-action="move-resource"][data-move-after="${deletedEntryId}"]`
    ).forEach(el => {
        const li = el.closest('li');
        if (li) {
            li.remove();
        }
    });
};