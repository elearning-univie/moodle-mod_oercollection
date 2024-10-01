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
 * Class for the structure used to restore one flashcards activity.
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one flashcards activity
 *
 * @package   mod_oercollection
 * @category  backup
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_oercollection_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines structure of path elements to be processed during the restore
     *
     * @return array of {@see restore_path_element}
     */
    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('oercollection', '/activity/oercollection');
        $paths[] = new restore_path_element('oercollection_resource', '/activity/wordcloud/resources/resource');
        
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_oercollection($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        if (empty($data->timemodified)) {
            $data->timemodified = time();
        }

        $newitemid = $DB->insert_record('oercollection', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_oercollection_resource($data) {
        global $DB;
       
        $data = (object)$data;
        $data->course = $this->get_courseid();
        $oldid = $data->id;

        $newoerid = $this->get_new_parentid('oercollection');
        $data->oerid = $newoerid;
        $newitemid = $DB->insert_record('oercollection_resource', $data);
        $this->set_mapping('oercollection_resource', $oldid, $newitemid, true);
    }
    /**
     * Post-execution actions
     */
    protected function after_execute() {
        $this->add_related_files('mod_oercollection', 'intro', null);
    }
    protected function inform_new_usage_id($newusageid)
    {}

}
