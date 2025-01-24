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

export const init = () => {
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