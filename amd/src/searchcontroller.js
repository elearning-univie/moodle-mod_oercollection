import ajax from "core/ajax";
import notification from "core/notification";
import {getString} from 'core/str';

export const init = () => {
    const mod_oercollection_add_to_collection = (oer, oerhubid, resourcelink, resourcename) => {
        ajax.call([{
            methodname: 'mod_oercollection_add_to_collection',
            args: {oerid: oer, oerhubid: oerhubid, resourcelink: resourcelink, resourcename: resourcename},
            done: function (returnval) {
                if (returnval.alreadyincollection === 1) {
                    getString('resourceexistsinfomessage', 'mod_oercollection').then((infomessage) => {
                        notification.addNotification({
                            message: infomessage,
                            type: "warning"
                        });
                    });
                } else {
                    getString('addedinfomessage', 'mod_oercollection', 1).then((infomessage) => {
                        notification.addNotification({
                            message: infomessage,
                            type: "success"
                        });
                    });
                }
            },
            fail: notification.exception
        }]);
    };

    // Attach the function to the global namespace or use it where needed
    window.mod_oercollection_add_to_collection = mod_oercollection_add_to_collection;
};
