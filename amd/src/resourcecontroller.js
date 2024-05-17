import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";

export const init = () => {
    $.mod_oercollection_set_visibility_oerentry = function (oer, oerentryid) {
            ajax.call([{
                methodname: 'mod_oercollection_set_visibility_oerentry',
                args: {oerid: oer, oerentryid: oerentryid},
                done: function () {
                    location.reload();
                },
                fail: notification.exception
            }]);
    };
    /*
    $.mod_oercollection_remove_questions = function (aid) {
        var data = document.querySelectorAll(".mod-flashcards-checkbox");
        var qids = [];
        for (var i = 0; i < data.length; i++) {
            if (data[i].checked == true) {
                qids[i] = data[i].dataset.value;
            }
        }
        if (qids && qids.length) {
            ajax.call([{
                methodname: 'mod_flashcards_remove_questions',
                args: {flashcardsid: aid, qids: qids},
                done: function () {
                    location.reload();
                },
                fail: notification.exception
            }]);
        }
    };
    $.mod_oercollection_selected = function () {
        var checkboxes = document.getElementsByName('selectbox');
        var checkboxesChecked = [];
        for (var i=0; i<checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                checkboxesChecked.push(checkboxes[i]);
            }
        }
        if(checkboxesChecked.length > 0){
            document.getElementById("maintanancebtn").disabled = false;
        } else{
            document.getElementById("maintanancebtn").disabled = true;
        }
    };
    $.mod_oercollection_select_all = function (selected) {
        $('input:checkbox').not(selected).prop('checked', selected.checked);
        this.mod_flashcards_selected();
    };
    */
};
