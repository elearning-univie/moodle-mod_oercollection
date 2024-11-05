<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mandatory public API of oercollection
 *
 * @package   mod_oercollection
 * @author    Adrian Czermak
 * @author    Angela Baier
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_oercollection_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB, $OUTPUT; 

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('oercollectionname', 'oercollection'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'display', get_string('display', 'oercollection'));
        $options = array(NEWPAGE       => get_string('newpage', 'oercollection'),
                        THISPAGE => get_string('thispage', 'oercollection'));
        $mform->addElement('select', 'displaymode', get_string('displaymode', 'oercollection'), $options, NOGROUPS);
        $mform->addHelpButton('displaymode', 'displaymode', 'oercollection');
        
        // hide group mode...for now
        $this->_features->groups= false;

        // Standard Moodle course module elements (course, category, etc.).
        $this->standard_coursemodule_elements();

        // Standard Moodle form buttons.
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = array();

        if (array_key_exists('completionview', $data)){
            if ($data['displaymode'] == THISPAGE && $data['completionview'] == 1) {
                $errors['displaymode'] = get_string('errordisplaymode', 'oercollection');
            }
        }
        return $errors;
    }
}