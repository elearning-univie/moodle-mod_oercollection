import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";
import {getString} from 'core/str';

export const init = () => {
    window.onload = function() {
      // Construct URLSearchParams object instance from current URL querystring.
      var queryParams = new URLSearchParams(window.location.search);

      if (queryParams.get("delete") > 0) {
        getString('deleteinfomessage', 'mod_oercollection', queryParams.get("delete")).then(function (infomessage) {
              notification.addNotification({
              message: infomessage,
              type: "info"
            });
        // Set new or modify existing parameter value
        //queryParams.set("delete", "0");
        queryParams.delete("delete");
        // Replace current querystring with the new one.
        history.replaceState(null, null, "?"+queryParams.toString());
        });
      }
      if (queryParams.get("vyes") > 0) {
        getString('visibilityyesinfomessage', 'mod_oercollection', queryParams.get("vyes")).then(function (infomessage) {
              notification.addNotification({
              message: infomessage,
              type: "info"
            });
        // Set new or modify existing parameter value
        //queryParams.set("vyes", "0");
        queryParams.delete("vyes");
        // Replace current querystring with the new one.
        history.replaceState(null, null, "?"+queryParams.toString());
        });
      }
      if (queryParams.get("vno") > 0) {
        getString('visibilitynoinfomessage', 'mod_oercollection', queryParams.get("vno")).then(function (infomessage) {
              notification.addNotification({
              message: infomessage,
              type: "info"
            });
        // Set new or modify existing parameter value
        //queryParams.set("vno", "0");
        queryParams.delete("vno");
        // Replace current querystring with the new one.
        history.replaceState(null, null, "?"+queryParams.toString());
        });
      }
      if (queryParams.get("moved") > 0) {
        getString('movedinfomessage', 'mod_oercollection').then(function (infomessage) {
              notification.addNotification({
              message: infomessage,
              type: "info"
            });
        // Set new or modify existing parameter value
        queryParams.delete("moved");
        // Replace current querystring with the new one.
        history.replaceState(null, null, "?"+queryParams.toString());
        });
      }
    };
    $.mod_oercollection_set_visibility_oerentry = function(oer, oerentryid) {
        ajax.call([{
            methodname: 'mod_oercollection_set_visibility_oerentry',
            args: {oerid: oer, oerentryid: oerentryid},
            done: function () {
                location.reload();
            },
            fail: notification.exception
        }]);
    };
    $.mod_oercollection_delete_oerentry = function(oer, oerentryid, resourcename) {
        getString('deletepopup', 'mod_oercollection', resourcename).then(function (warningmessage) {
            if(confirm(warningmessage) ) {
                ajax.call([{
                    methodname: 'mod_oercollection_delete_oerentry',
                    args: {oerid: oer, oerentryid: oerentryid},
                    done: function() {
                        var queryParams = new URLSearchParams(window.location.search);
                        queryParams.set("delete", "1");
                        history.replaceState(null, null, "?"+queryParams.toString());
                        location.reload();
                    },
                    fail: notification.exception
                }]);
            } else {
                var uri = window.location.toString();
                if (uri.indexOf("#")) {
                  var clean_uri = uri.substring(0, uri.indexOf("#"));
                  window.history.replaceState({}, document.title, clean_uri);
                }
            }
        });
    };

    $.mod_oercollection_move_resource_action = function(oer, oereidtomove, oereidmoveafter) {
        ajax.call([{
            methodname: 'mod_oercollection_move_resource',
            args: {oerid: oer, oereidtomove: oereidtomove, oereidmoveafter: oereidmoveafter},
            done: () => {
                const queryParams = new URLSearchParams(window.location.search);
                queryParams.set("moved", 1);
                history.replaceState(null, null, `?${queryParams.toString()}`);
                location.reload();
            },
            fail: notification.exception
        }]);
    };
};
