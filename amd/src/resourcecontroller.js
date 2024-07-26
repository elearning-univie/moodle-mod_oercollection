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
        queryParams.set("delete", "0");

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
    $.mod_oercollection_selected = function() {
        var checkboxes = document.getElementsByName('selectbox');
        var checkboxesChecked = [];
        var listi = '';
        for (var i=0; i<checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                listi = listi + checkboxes[i].value + ' ';
                checkboxesChecked.push(checkboxes[i]);
            }
        }
    };
    $.mod_oercollection_select_all = function(selected) {
        $('input:checkbox').not(selected).prop('checked', selected.checked);
        this.mod_oercollection_selected();
    };
    $.mod_oercollection_checkall = function() {
        var checkboxes = document.getElementsByName('selectbox');
        for (var i=0; i<checkboxes.length; i++) {
            checkboxes[i].checked = true;
        }
    };
    $.mod_oercollection_checknone = function() {
        var checkboxes = document.getElementsByName('selectbox');
        for (var i=0; i<checkboxes.length; i++) {
            checkboxes[i].checked = false;
        }
    };

    $.mod_oercollection_move_resource_action = function(oer, oereidtomove, oereidmoveafter) {
            ajax.call([{
                methodname: 'mod_oercollection_move_resource',
                args: {oerid: oer, oereidtomove: oereidtomove, oereidmoveafter: oereidmoveafter},
                done: function () {
                    location.reload();
                },
                fail: notification.exception
            }]);
    };

    $.mod_oercollection_bulk_action = function(oer) {
        var checkboxes = document.getElementsByName('selectbox');
        var oerids = [];
        var rlinks = [];
        var todelete = 0;
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked == true) {
                todelete++;
                oerids[i] = checkboxes[i].value;
                rlinks[i] = document.getElementById('resourcelink' + checkboxes[i].value).href;
            }
        }
        var bulkaction = document.getElementById("editoptionselect").value;
        var show = 0;
        if (bulkaction == 1) {
           for (var i = 0; i < rlinks.length; i++) {
           window.open(rlinks[i], '_blank');
           }
        location.reload();
        }
        if (bulkaction == 2) {
           show = 1;
        }
        if (bulkaction == 2 || bulkaction == 3) {
                      ajax.call([{
                methodname: 'mod_oercollection_set_visibility_all',
                args: {oerid: oer, oerentryids: oerids, show: show},
                done: function () {
                    location.reload();
                },
                fail: notification.exception
            }]);
        }
        if (bulkaction == 4) {
                      ajax.call([{
                methodname: 'mod_oercollection_delete_selected_oerentries',
                args: {oerid: oer, oerentryids: oerids},
                done: function () {
                    var queryParams = new URLSearchParams(window.location.search);
                    queryParams.set("delete", todelete);
                    history.replaceState(null, null, "?"+queryParams.toString());
                    location.reload();
                },
                fail: notification.exception
            }]);
        }
    };
};
