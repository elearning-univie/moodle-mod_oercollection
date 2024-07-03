import $ from "jquery";
import ajax from "core/ajax";
//import {call as fetchMany} from 'core/ajax';
import ModalFactory from "core/modal_factory";
import notification from "core/notification";
import ModalEvents from "core/modal_events";
import {get_string as getString} from 'core/str';
//import ModalForm from 'core_form/modalform';
//import Notification from 'core/notification';
//import Pending from 'core/pending';
///import SortableList from 'core/sortable_list';
import Templates from 'core/templates';
//import jQuery from 'jquery';

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
            ajax.call([{
                methodname: 'mod_oercollection_delete_oerentry',
                args: {oerid: oer, oerentryid: oerentryid},
                done: function () {
                    location.reload();
                },
                fail: notification.exception
            }]);
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
        //alert("is " + listi);
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
    $.mod_oercollection_move_resource = function(data) {

ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: getString('moveresource', 'mod_oercollection'),
                body: Templates.render('mod_oercollection/moveresourcemodal', data),
            }).then(function (modal) {
                modal.getRoot().on(ModalEvents.save, function() {
                    alert('LALALAMMMMAAAAA');
                });

                modal.getRoot().on(ModalEvents.cancel, () => {
                    location.reload();
                });

                modal.getRoot().on(ModalEvents.hidden, () => {
                    modal.destroy();
                });

//modal.addEventListener(modal.events.FORM_SUBMITTED, () => {
//alert('LALALAMMMMAAAAA');
//    });


                modal.show();
            });


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
