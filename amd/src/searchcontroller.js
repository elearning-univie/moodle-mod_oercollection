import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";
import {getString} from 'core/str';

export const init = () => {
    $.mod_oercollection_add_to_collection = function(oer, oerhubid, resourcelink, resourcename) {
        ajax.call([{
            methodname: 'mod_oercollection_add_to_collection',
            args: {oerid: oer, oerhubid: oerhubid, resourcelink: resourcelink, resourcename: resourcename},
            done: () => {
              getString('addedinfomessage', 'mod_oercollection', 1).then(function (infomessage) {
              notification.addNotification({
              message: infomessage,
              type: "success"
              });
            });
            },
            fail: notification.exception
            }]);
    };
};
