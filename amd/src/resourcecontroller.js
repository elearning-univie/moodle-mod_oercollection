import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";
import {getString} from 'core/str';

export const init = () => {

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
    $.mod_oercollection_delete_oerentry = function(oer, oerentryid) {
 //                 notification.addNotification({
 //               message: "hellooooo",
  //              type: "info"
 //           });
        getString('deletewarning', 'mod_oercollection').then(function (warningmessage) {
            if (confirm(warningmessage) ) {
                ajax.call([{
                    methodname: 'mod_oercollection_delete_oerentry',
                    args: {oerid: oer, oerentryid: oerentryid},
                    done: function () {
                        let currentUrl = new URL(window.location.href);
                        let params = new URLSearchParams(currentUrl.search);
                        params.set('deleted', 1);
                        window.location.href = currentUrl;
                       // let currentUrl = window.location.href + '\&deleted=1';
                        //params.append("deleted", 1);
                        //window.location.href = window.location.href + 'deleted=1';
                        location.reload();
                    },
                    fail: notification.exception
                }]);
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
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked == true) {
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
                    location.reload();
                },
                fail: notification.exception
            }]);
        }
    };
};
