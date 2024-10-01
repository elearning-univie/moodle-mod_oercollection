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
 * Class for the structure used to backup one flashcards activity.
 *
 * @package   mod_flashcards
 * @copyright 2021 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete flashcards structure for backup, with file and id annotations
 *
 * @package   mod_oercollection
 * @category  backup
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_oercollection_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        $oercollection = new backup_nested_element('oercollection', ['id'],
            [ 'name', 'course', 'intro', 'introformat', 'timemodified', 'displaymode']);

        $resources = new backup_nested_element('oercollection_resources');
        
        $resource = new backup_nested_element('oercollection_resource', ['id'],
            [ 'oerid', 'oerresourceid', 'apipluginid', 'showresource', 'position',
                'notenameinternal', 'notetextinternal', 'resourcelink', 'resourcename']);

        $oercollection->add_child($resources);
        $resources->add_child($resource);
        
        $oercollection->set_source_table('oercollection', array('id' => backup::VAR_ACTIVITYID));
        
        $resource->set_source_sql('
            SELECT *
              FROM {oercollection_resource}
             WHERE oerid = ?',
            array(backup::VAR_PARENTID));
        
       // $oercollection->annotate_files('mod_oercollection', 'intro', null);
        //$resource->annotate_ids('oercollection', 'oerid');
        
        return $this->prepare_activity_structure($oercollection);
    }
}
