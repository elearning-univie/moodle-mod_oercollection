import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";

export const init = () => {
    $.mod_oercollection_add_to_collection = function(oer, oerhubid, resourcelink, resourcename) {
        ajax.call([{
            methodname: 'mod_oercollection_add_to_collection',
            args: {oerid: oer, oerhubid: oerhubid, resourcelink: resourcelink, resourcename: resourcename},
            done: function () {
                location.reload();
            },
            fail: notification.exception
            }]);
    };
};
