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
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_MOD_PURPOSE:             return MOD_PURPOSE_CONTENT;
        default: return null;
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
    $DB->delete_records('oercollection_resource', array('oerid'=>$oercollection->id));
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

/**
 * Given a coursemodule object, this function returns the extra
 * information needed to print this activity in various places.
 *
 * If folder needs to be displayed inline we store additional information
 * in customdata, so functions {@link folder_cm_info_dynamic()} and
 * {@link folder_cm_info_view()} do not need to do DB queries
 *
 * @param cm_info $cm
 * @return cached_cm_info info
 */
function oercollection_get_coursemodule_info($cm) {
    global $DB;
    if (!($oercollection = $DB->get_record('oercollection', array('id' => $cm->instance)))) {
        return NULL;
    }
    $cminfo = new cached_cm_info();
    $cminfo->name = $oercollection->name;
    if ($oercollection->displaymode == 1) {
        // prepare folder object to store in customdata
        $oerdata = new stdClass();
        if ($cm->showdescription && strlen(trim($oercollection->intro))) {
            $oerdata->intro = $oercollection->intro;
            if ($oercollection->introformat != FORMAT_MOODLE) {
                $oerdata->introformat = $oercollection->introformat;
            }
        }
       $cminfo->customdata = $oerdata;
    } else {
        if ($cm->showdescription) {
            // Convert intro to html. Do not filter cached version, filters run at display time.
            $cminfo->content = format_module_intro('oercollection', $oercollection, $cm->id, false);
        }
    }
    return $cminfo;
}
/**
 * Sets dynamic information about a course module
 *
 * This function is called from cm_info when displaying the module
 * mod_folder can be displayed inline on course page and therefore have no course link
 *
 * @param cm_info $cm
 */
function oercollection_cm_info_dynamic(cm_info $cm) {
//     if ($cm->get_custom_data()) {
//         // the field 'customdata' is not empty IF AND ONLY IF we display contens inline
//         $cm->set_no_view_link();
//     }
}
/**
 * Overwrites the content in the course-module object with the folder files list
 * if folder.display == 1
 *
 * @param cm_info $cm
 */
function oercollection_cm_info_view(cm_info $cm) {
    global $PAGE, $DB;
    
    $templatecontext = [];
    if (!($oercollection = $DB->get_record('oercollection', array('id' => $cm->instance)))) {
        return NULL;
    }
    //$cminfo = new cached_cm_info();
    $oerdata = new stdClass();
    if ($cm->showdescription && strlen(trim($oercollection->intro))) {
        $oerdata->intro = $oercollection->intro;
        if ($oercollection->introformat != FORMAT_MOODLE) {
            $oerdata->introformat = $oercollection->introformat;
        }
    }
    $templatecontext['intro'] = '';
    if (isset($oerdata->intro)) {
        $templatecontext['intro'] = $oerdata->intro;
    }
    if ($cm->uservisible && $cm->customdata) {
            // Restore folder object from customdata.
            // Note the field 'customdata' is not empty IF AND ONLY IF we display contens inline.
            // Otherwise the content is default.
            $folder = $cm->customdata;
            $folder->id = (int)$cm->instance;
            $folder->course = (int)$cm->course;
            $folder->display = 1;
            $folder->name = $cm->name;
            if (empty($folder->intro)) {
                $folder->intro = '';
            }
            if (empty($folder->introformat)) {
                $folder->introformat = FORMAT_MOODLE;
            }

            // get oer entries
            $sql = 'SELECT * FROM {oercollection_resource} oerr WHERE oerr.oerid = :oerid AND oerr.showresource = 1 ORDER BY oerr.position ASC';
            $oerentries = $DB->get_records_sql($sql, ['oerid' => $cm->instance]); //, "position ASC"


            $oerlist = [];
            $oerapi = new \oerapi_oerhub\api\general($PAGE->url, $cm->instance);
            
            foreach ($oerentries as $oerentry) {
                $commentexists = true;
                if (is_null($oerentry->notetextinternal) || empty($oerentry->notetextinternal)) {
                    $commentexists = false;
                }
                $oerlist[] = array(
                    'oerentryid' => $oerentry->id,
                    'oerhtml' => $oerapi->get_resource_html($oerentry->oerresourceid),
                    'commentexists' => $commentexists,
                    'commenttext' => $oerentry->notetextinternal,
                    'commentname' => $oerentry->notenameinternal,
                );
            }
            $templatecontext['oerresourcelist'] = $oerlist;

            // display folder
            $renderer = $PAGE->get_renderer('core');
            $oerlist = $renderer->render_from_template('mod_oercollection/oercourseinfo', $templatecontext);
            //$cm->content = $oerlist;
            $cm->set_content($oerlist, false);
        }
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $oercollection   oercollection object
 * @param  stdClass $course  course object
 * @param  stdClass $cm      course module object
 * @param  stdClass $context context object
 * @since Moodle 2.9
 */
function oercollection_view($oercollection, $course, $cm, $context) {

    //Todo: erweitern sobald es möglich is zu tracken ob einzelne Resourcen aufgerufen wurden. 
        // Completion.
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        // Trigger
        $params = array(
            'context' => $context,
            'objectid' => $oercollection->id
        );
        
        $event = \mod_oercollection\event\course_module_viewed::create($params);
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('oercollection', $oercollection);
        $event->trigger();
}