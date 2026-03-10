/**
 * Sync controller for OER Collection, mirrors the bottom bulk-action controls to the top originals.
 *
 * @module     mod_oercollection/sync_controller
 * @copyright  2024 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {
    const originalSelect = document.getElementById('editoptionselect');
    const mirrorSelect = document.getElementById('editoptionselect_mirror');

    const originalWarning = document.getElementById('nothingselectedwarning');
    const mirrorWarning = document.getElementById('nothingselectedwarning_mirror');

    if (originalSelect && mirrorSelect) {
        mirrorSelect.addEventListener('change', () => {
            originalSelect.value = mirrorSelect.value;
        });

        originalSelect.addEventListener('change', () => {
            mirrorSelect.value = originalSelect.value;
        });

        mirrorSelect.value = originalSelect.value;
    }

    if (originalWarning && mirrorWarning) {
        const syncWarning = () => {
            mirrorWarning.className = originalWarning.className;
        };

        syncWarning();
        new MutationObserver(syncWarning).observe(originalWarning, {attributes: true, attributeFilter: ['class']});
    }
};
