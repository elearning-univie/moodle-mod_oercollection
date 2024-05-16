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
 * Mandatory public API of folder oercollection
 *
 * @package   mod_oercollection
 * @author    Adrian Czermak
 * @author    Angela Baier
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Groups not used in course or activity
 */
define('NEWPAGE', 0);

/**
 * Groups used, users do not see other groups
 */
define('THISPAGE', 1);


/**
 * Returns the information on whether the module supports a feature
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function oercollection_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

/**
 * Add oercollection instance.
 * @param object $data
 * @param object $mform
 * @return int new folder instance id
 */
function oercollection_add_instance($data, $mform) {
    global $DB;
    
    $cmid        = $data->coursemodule;
   // $draftitemid = $data->files;
    
    $data->timemodified = time();
    // If 'showexpanded' is not set, apply the site config.
    if (!isset($data->showexpanded)) {
        $data->showexpanded = get_config('oercollection', 'showexpanded');
    }
    $data->id = $DB->insert_record('oercollection', $data);
    
    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'folder', $data->id, $completiontimeexpected);
    
    return $data->id;
}

/**
 * Update oercollection instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function oercollection_update_instance($data, $mform) {
    global $DB;
    
    $cmid        = $data->coursemodule;
    
    $data->timemodified = time();
    $data->id           = $data->instance;
    
    $DB->update_record('oercollection', $data);
    
    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'oercollection', $data->id, $completiontimeexpected);
    
    return true;
}

/**
 * Delete oercollection instance.
 * @param int $id
 * @return bool true
 */
function oercollection_delete_instance($id) {
    global $DB;
    
    if (!$oercollection = $DB->get_record('oercollection', array('id'=>$id))) {
        return false;
    }
    
    $cm = get_coursemodule_from_instance('oercollection', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'oercollection', $oercollection->id, null);
    
    // note: all context files are deleted automatically
    
    $DB->delete_records('oercollection', array('id'=>$oercollection->id));
    
    return true;
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param navigation_node $wordcloudnode The node to add module settings to
 */
function oercollection_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $oercollectionnode) {
    if (has_capability('mod/oercollection:editresources', $settingsnav->get_page()->context)) {
        $url = new moodle_url('/mod/oercollection/resources.php', ['id' => $settingsnav->get_page()->cm->id]);
        $oercollectionnode->add(get_string('resources', 'mod_oercollection'), $url, navigation_node::TYPE_SETTING, null, 'mod_wordcloud_list');
    }
}