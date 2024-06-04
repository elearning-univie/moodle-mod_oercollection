import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";

export const init = () => {
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
    $.mod_oercollection_add_to_collection = function(oer, oerhubid) {
        ajax.call([{
            methodname: 'mod_oercollection_add_to_collection',
            args: {oerid: oer, oerhubid: oerhubid},
            done: function () {
                location.reload();
            },
            fail: notification.exception
            }]);
    };
 //   $.mod_oersearch_checknone = function() {
 //       var checkboxes = document.getElementsByName('selectbox');
 //       for (var i=0; i<checkboxes.length; i++) {
 //           checkboxes[i].checked = false;
 //       }
 //   };
    $.mod_oercollection_bulk_action = function(oer) {
      var checkboxes = document.getElementsByName('selectbox');
        var oerids = [];
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked == true) {
                oerids[i] = checkboxes[i].value;
            }
        }
        var bulkaction = document.getElementById("editoptionselect").value;
        var show = 0;
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
