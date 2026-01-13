import ajax from "core/ajax";
import notification from "core/notification";
import { getString } from 'core/str';

const handleQueryParamNotification = async (param, stringKey, needsValue = true) => {
    const queryParams = new URLSearchParams(window.location.search);
    const paramValue = queryParams.get(param);

    if (!paramValue || Number(paramValue) <= 0) {
        return;
    }

    try {
        const messageArgs = needsValue ? [stringKey, 'mod_oercollection', paramValue]
            : [stringKey, 'mod_oercollection'];
        const infoMessage = await getString(...messageArgs);

        notification.addNotification({
            message: infoMessage,
            type: "info"
        });

        queryParams.delete(param);
        history.replaceState(null, null, `?${queryParams.toString()}`);
    } catch (error) {
        notification.exception(error);
    }
};

const setQueryParamAndReload = (param, value) => {
    const queryParams = new URLSearchParams(window.location.search);
    queryParams.set(param, value);
    history.replaceState(null, null, `?${queryParams.toString()}`);
    location.reload();
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
     * Truncation for a specific selector
     *
     * @param {string} textSelector - Selector for text elements
     * @param {string} toggleSelector - Selector for toggle links
     * @param {string} idAttribute - Attribute used to match text and toggle
     */
    const initTruncationForSelector = (textSelector, toggleSelector, idAttribute) => {
        const elements = document.querySelectorAll(textSelector);

        elements.forEach((textElement) => {

            // For comments using the nested .text_to_html element.
            let elementToCheck = textElement;
            if (textSelector === '.oer-comment-text') {
                const textToHtml = textElement.querySelector('.text_to_html');
                if (textToHtml) {
                    elementToCheck = textToHtml;
                }
            }

            if (isOverflowing(elementToCheck)) {
                const id = textElement.getAttribute(idAttribute);
                const toggleLink = document.querySelector(`${toggleSelector}[${idAttribute}="${id}"]`);

                if (toggleLink) {
                    toggleLink.classList.remove('d-none');

                    // Check if toggle uses icon or text
                    const iconElement = toggleLink.querySelector('i');
                    const hasIcon = iconElement !== null;

                    if (hasIcon) {
                        iconElement.className = 'fa fa-chevron-down';
                    } else {
                        toggleLink.querySelector('small').textContent = showMoreStr;
                    }

                    toggleLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        const isExpanded = toggleLink.getAttribute('data-expanded') === 'true';

                        if (isExpanded) {
                            textElement.classList.remove('expanded');
                            // Re-apply inline truncation styles for comments
                            if (textSelector === '.oer-comment-text') {
                                textElement.style.display = '-webkit-box';
                                textElement.style.webkitLineClamp = '3';
                                textElement.style.webkitBoxOrient = 'vertical';
                                textElement.style.overflow = 'hidden';
                                textElement.style.maxHeight = '4.5em';
                            }
                            toggleLink.setAttribute('data-expanded', 'false');
                            if (hasIcon) {
                                iconElement.className = 'fa fa-chevron-down';
                            } else {
                                toggleLink.querySelector('small').textContent = showMoreStr;
                            }
                        } else {
                            textElement.classList.add('expanded');
                            // Remove inline truncation styles for comments
                            if (textSelector === '.oer-comment-text') {
                                textElement.style.display = 'block';
                                textElement.style.webkitLineClamp = 'unset';
                                textElement.style.webkitBoxOrient = '';
                                textElement.style.overflow = 'visible';
                                textElement.style.maxHeight = 'none';
                            }
                            toggleLink.setAttribute('data-expanded', 'true');
                            if (hasIcon) {
                                iconElement.className = 'fa fa-chevron-up';
                            } else {
                                toggleLink.querySelector('small').textContent = showLessStr;
                            }
                        }
                    });
                }
            }
        });
    };

    const runTruncation = () => {
        requestAnimationFrame(() => {
            // Small delay to ensure CSS is applied
            setTimeout(() => {
                initTruncationForSelector('.oer-description', '.oer-description-toggle', 'data-resource-id');
                initTruncationForSelector('.oer-comment-text', '.oer-comment-toggle', 'data-entry-id');
            }, 100);
        });
    };

    runTruncation();
};

export const init = () => {
    // Skip image load and run truncation as soon as DOM is ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTextTruncation);
    } else {
        initTextTruncation();
    }

    window.addEventListener('load', async () => {
        await Promise.all([
            handleQueryParamNotification('delete', 'deleteinfomessage'),
            handleQueryParamNotification('vyes', 'visibilityyesinfomessage'),
            handleQueryParamNotification('vno', 'visibilitynoinfomessage'),
            handleQueryParamNotification('moved', 'movedinfomessage', false)
        ]);
    });

    const setVisibilityHandler = (oer, oerentryid, show) => {
        ajax.call([{
            methodname: 'mod_oercollection_set_visibility_oerentry',
            args: { oerid: oer, oerentryid: oerentryid },
            done: () => setQueryParamAndReload(show === 1 ? 'vyes' : 'vno', 1),
            fail: notification.exception
        }]);
    };

    const deleteHandler = (oer, oerentryid, resourcename) => {
        getString('deletepopup', 'mod_oercollection', resourcename)
            .then(warningMessage => {
                if (!confirm(warningMessage)) {
                    const url = new URL(window.location);
                    url.hash = '';
                    history.replaceState({}, document.title, url.toString());
                    return;
                }

                ajax.call([{
                    methodname: 'mod_oercollection_delete_oerentry',
                    args: { oerid: oer, oerentryid: oerentryid },
                    done: () => setQueryParamAndReload('delete', 1),
                    fail: notification.exception
                }]);
            })
            .catch(notification.exception);
    };

    const moveResourceHandler = (oer, oereidtomove, oereidmoveafter) => {
        ajax.call([{
            methodname: 'mod_oercollection_move_resource',
            args: {
                oerid: oer,
                oereidtomove: oereidtomove,
                oereidmoveafter: oereidmoveafter
            },
            done: () => setQueryParamAndReload('moved', 1),
            fail: notification.exception
        }]);
    };

    // Expose handlers to global scope
    Object.assign(window, {
        mod_oercollection_set_visibility_oerentry: setVisibilityHandler,
        mod_oercollection_delete_oerentry: deleteHandler,
        mod_oercollection_move_resource_action: moveResourceHandler
    });
};