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
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * mod_oercollection_mod_form
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_oercollection_mod_form extends moodleform_mod {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition(): void {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('oercollectionname', 'oercollection'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'display', get_string('display', 'oercollection'));
        $options = [
            NEWPAGE => get_string('newpage', 'oercollection'),
            THISPAGE => get_string('thispage', 'oercollection'),
        ];
        $mform->addElement('select', 'displaymode', get_string('displaymode', 'oercollection'), $options, NOGROUPS);
        $mform->addHelpButton('displaymode', 'displaymode', 'oercollection');

        // Hide group mode...for now.
        $this->_features->groups = false;

        // Standard Moodle course module elements (course, category, etc.).
        $this->standard_coursemodule_elements();

        // Standard Moodle form buttons.
        $this->add_action_buttons();
    }

    /**
     * Perform extra validation.
     * @param array $data submitted form fields.
     * @param array $files submitted with the form.
     * @return array errors occuring during validation.
     */
    public function validation($data, $files) {
        $errors = [];

        if (array_key_exists('completionview', $data)) {
            if ($data['displaymode'] == THISPAGE && $data['completionview'] == 1) {
                $errors['displaymode'] = get_string('errordisplaymode', 'oercollection');
            }
        }
        return $errors;
    }
}
