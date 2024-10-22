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
 * Interface implementation of the external Webservices
 *-
 * @package   mod_oercollection
 * @author    Adrian Czermak
 * @author    Angela Baier
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_multiple_structure;
use core\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Class mod_oercollection_external
 *
 * @copyright  2021 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_oercollection_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function set_visibility_oerentry_parameters() {
        return new external_function_parameters(
            array(
                'oerid' => new external_value(PARAM_INT, 'flashcard activity id', VALUE_REQUIRED),
                'oerentryid' => new external_value(PARAM_INT, 'oer entry id', VALUE_REQUIRED),
                )
            );
    }
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function set_visibility_all_parameters() {
        return new external_function_parameters(
            array(
                'oerid' => new external_value(PARAM_INT, 'oerid', VALUE_REQUIRED),
                'oerentryids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'id array of questions')
                    ),
                'show' => new external_value(PARAM_BOOL, 'visibility value', VALUE_REQUIRED),
            )
            );
    }
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_selected_oerentries_parameters() {
        return new external_function_parameters(
            array(
                'oerid' => new external_value(PARAM_INT, 'oerid', VALUE_REQUIRED),
                'oerentryids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'id array of questions')
                    ),
                )
            );
    }
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_oerentry_parameters() {
        return new external_function_parameters(
            array(
                'oerid' => new external_value(PARAM_INT, 'flashcard activity id', VALUE_REQUIRED),
                'oerentryid' => new external_value(PARAM_INT, 'oer entry id', VALUE_REQUIRED),
            )
            );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function move_resource_parameters() {
        return new external_function_parameters(
            array(
                'oerid' => new external_value(PARAM_INT, 'flashcard activity id', VALUE_REQUIRED),
                'oereidtomove' => new external_value(PARAM_INT, 'flashcard activity id', VALUE_REQUIRED),
                'oereidmoveafter' => new external_value(PARAM_INT, 'oer entry id', VALUE_REQUIRED),
            )
            );
    }
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_entry_to_collection_parameters() {
        return new external_function_parameters([
            'oerid' => new external_value(PARAM_INT, 'flashcard activity id', VALUE_REQUIRED),
            'oerhubid' => new external_value(PARAM_TEXT, 'oer entry id', VALUE_REQUIRED),
            'resourcelink' => new external_value(PARAM_URL, 'resource direct link', VALUE_REQUIRED),
            'resourcename' => new external_value(PARAM_TEXT, 'resource name', VALUE_REQUIRED),
        ]);
    }
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_entries_to_collection_parameters() {
        return new external_function_parameters(
            array(
                'oerid' => new external_value(PARAM_INT, 'oerid', VALUE_REQUIRED),
                'oerhubids' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'id array of hub resources')
                    ),
                'resourcelinks' => new external_multiple_structure(
                    new external_value(PARAM_URL, 'id array of resourcelinks')
                    ),
                'resourcenames' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'id array of resourcetitles')
                    ),
            )
            );
    }
    /**
     *
     * Removes all selected questions from box 1 to box 0 for the activity
     *
     * @param int $oerid
     * @param int $oerentryid
     */
    public static function set_visibility_oerentry($oerid, $oerentryid) {
        global $DB, $USER;
        
        $params = self::validate_parameters(self::set_visibility_oerentry_parameters(),
            array('oerid' => $oerid, 'oerentryid' => $oerentryid));
        
        $oerentry = $DB->get_record('oercollection_resource', ['id' => $oerentryid]);
        if ($oerentry) {
            $oerentry->showresource = $oerentry->showresource ? 0 : 1;
            $DB->update_record('oercollection_resource', $oerentry);
        }
        
    }
    
    /**
     *
     * Removes all selected questions from box 1 to box 0 for the activity
     *
     * @param int $oerid
     * @param array $oerentryid
     * @param int $show
     */
    public static function set_visibility_all($oerid, $oerentryids, $show) {
        global $DB, $USER;
        
        $params = self::validate_parameters(self::set_visibility_all_parameters(),
            array('oerid' => $oerid, 'oerentryids' => $oerentryids, 'show' => $show));

        if ($oerentryids) {
            list($inids, $oereids) = $DB->get_in_or_equal($oerentryids);
            $sql = "SELECT * FROM {oercollection_resource}
                            WHERE id $inids";
            $oerentries = $DB->get_records_sql($sql, $oereids);
            foreach ($oerentries as $oerentry) {
                if ($oerentry) {
                    $oerentry->showresource = $show;
                    $DB->update_record('oercollection_resource', $oerentry);
                }
            }
        }
    }
    /**
     * deletes all selected oer entries from collection
     *
     * @param int $oerid
     * @param int $oerentryid
     */
    public static function delete_selected_oerentries($oerid, $oerentryids) {
        global $DB;
        
        $params = self::validate_parameters(self::delete_selected_oerentries_parameters(),
            array('oerid' => $oerid, 'oerentryids' => $oerentryids));

        $cm = get_coursemodule_from_instance('oercollection', $params['oerid'], 0, false, MUST_EXIST);
        $eventparams = array(
            'objectid' => $oerid,
            'context' => context_module::instance($cm->id),
        );

        if ($oerentryids) {
            list($inids, $oereids) = $DB->get_in_or_equal($oerentryids);
            $sql = "SELECT * FROM {oercollection_resource} oer
                            WHERE oer.id $inids";
            $oerentries = $DB->get_records_sql($sql, $oereids);
            foreach ($oerentries as $oerentry) {
                if ($oerentry) {
                    $DB->delete_records('oercollection_resource', ['id' => $oerentry->id, 'oerid' => $oerid]);
                    $event = \mod_oercollection\event\oer_resource_removed::create($eventparams);
                    $event->trigger();
                }
            }
        }
    }
    /**
     * deletes all selected oer entries from collection
     *
     * @param int $oerid
     * @param int $oerentryid
     */
    public static function move_resource($oerid, $oereidtomove, $oereidmoveafter) {
         global $DB;

        $params = self::validate_parameters(self::move_resource_parameters(),
            array('oerid' => $oerid, 'oereidtomove' => $oereidtomove, 'oereidmoveafter' => $oereidmoveafter));

        $sql = "SELECT *
                  FROM {oercollection_resource} oer
                 WHERE oer.oerid = $oerid
              ORDER BY oer.position ASC ";
        $resourcelist = $DB->get_records_sql($sql);

        $sql = "SELECT oer.position
                  FROM {oercollection_resource} oer
                 WHERE oer.oerid = $oerid
                   AND id = $oereidtomove
              ORDER BY oer.position ASC ";
        $resourcetomove = $DB->get_record_sql($sql);
        $sql = "SELECT oer.position
                  FROM {oercollection_resource} oer
                 WHERE oer.oerid = $oerid
                   AND id = $oereidmoveafter
              ORDER BY oer.position ASC ";
        $resourcemoveafter =  $DB->get_record_sql($sql);

        $xx = 0;
        if ($resourcetomove->position < $resourcemoveafter->position) {
            $xx = 1;
        }

        $move = array_splice($resourcelist, ($resourcetomove->position - 1), 1);
        $newlist = array_merge(
            array_slice( $resourcelist, 0, ($resourcemoveafter->position - 1 - $xx)),
            $move,
            array_slice( $resourcelist, ($resourcemoveafter->position - 1 - $xx))
            );

        $ctr = 1;
        foreach ($newlist as $n) {
            $n->position = $ctr;
            $DB->update_record('oercollection_resource', $n);
            $ctr++;
        }
    }
    /**
     * Removes all selected questions from box 1 to box 0 for the activity
     *
     * @param int $oerid
     * @param int $oerentryid
     */
    public static function delete_oerentry($oerid, $oerentryid) {
        global $DB;

        $params = self::validate_parameters(self::delete_oerentry_parameters(),
            array('oerid' => $oerid, 'oerentryid' => $oerentryid));

        $cm = get_coursemodule_from_instance('oercollection', $params['oerid'], 0, false, MUST_EXIST);

        if ($DB->record_exists('oercollection_resource', ['id' => $oerentryid, 'oerid' => $oerid])) {
            $DB->delete_records('oercollection_resource', ['id' => $oerentryid, 'oerid' => $oerid]);
            $params = array(
                'objectid' => $oerid,
                'context' => context_module::instance($cm->id),
            );
            $event = \mod_oercollection\event\oer_resource_removed::create($params);
            $event->trigger();
        }
    }

    /**
     * add oerhub entry to collectyion
     *
     * @param int $oerid
     * @param int $oerhubid
     */
    public static function add_entry_to_collection($oerid, $oerhubid, $resourcelink, $resourcename) {
        global $DB, $OUTPUT;
        
        $params = self::validate_parameters(self::add_entry_to_collection_parameters(), [
                'oerid' => $oerid,
                'oerhubid' => $oerhubid,
                'resourcelink' => $resourcelink,
                'resourcename' => $resourcename,
            ]);

        $cm = get_coursemodule_from_instance('oercollection', $oerid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        self::validate_context($context);
        require_login($course, false, $cm);
        require_capability('mod/oercollection:addinstance', $context);

        $sqlwhere = 'oerid = :oerid and oerresourceid = ' . $DB->sql_compare_text(':oerresourceid');
        $sqlparams = ['oerid' => $oerid, 'oerresourceid' => $oerhubid];

        $maxpossql = "SELECT MAX(oerr.position)
                       FROM {oercollection_resource} oerr
                      WHERE oerr.oerid = $oerid";
        $maxpos = $DB->get_field_sql($maxpossql);
        if (!$maxpos) {
            $maxpos = 0;
        }

        $params = array(
            'objectid' => $oerid,
            'context' => context_module::instance($cm->id),
        );

        if (!$DB->get_record_select('oercollection_resource', $sqlwhere, $sqlparams)) {
           $DB->insert_record('oercollection_resource', [
               'oerid' => $oerid,
               'oerresourceid' => $oerhubid,
               'resourcelink' => $resourcelink,
               'resourcename' => $resourcename,
               'position' => ($maxpos +1),
           ]);
           $event = \mod_oercollection\event\oer_resource_added::create($params);
           $event->trigger();
        } else {
            //echo $OUTPUT->notification('OBACHT', \core\output\notification::NOTIFY_ERROR);
        }
    }
    
    //add_entries_to_collection
    /**
     * add oerhub entry to collectyion
     *
     * @param int $oerid
     * @param int $oerhubid
     */
    public static function add_entries_to_collection($oerid, $oerhubids, $resourcelinks, $resourcenames) {
        global $DB;
        
        $params = self::validate_parameters(self::add_entries_to_collection_parameters(), [
            'oerid' => $oerid,
            'oerhubids' => $oerhubids,
            'resourcelinks' => $resourcelinks,
            'resourcenames' => $resourcenames,
        ]);

        $cm = get_coursemodule_from_instance('oercollection', $params['oerid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);
        
        self::validate_context($context);
        require_login($course, false, $cm);
        require_capability('mod/oercollection:addinstance', $context);

        $eventparams = array(
            'objectid' => $oerid,
            'context' => context_module::instance($cm->id),
        );

        $ctr = 0;
        foreach ($oerhubids as $oerhubid) {
            $sqlwhere = 'oerid = :oerid and oerresourceid = ' . $DB->sql_compare_text(':oerresourceid');
            $sqlparams = ['oerid' => $params['oerid'], 'oerresourceid' => $oerhubid];
            
            $maxpossql = "SELECT MAX(oerr.position)
                       FROM {oercollection_resource} oerr
                      WHERE oerr.oerid = $oerid";
            $maxpos = $DB->get_field_sql($maxpossql);
            if (!$maxpos) {
                $maxpos = 0;
            }
            if (!$DB->get_record_select('oercollection_resource', $sqlwhere, $sqlparams)) {
                $DB->insert_record('oercollection_resource', [
                    'oerid' => $oerid,
                    'oerresourceid' => $oerhubid,
                    'resourcelink' => $resourcelinks[$ctr],
                    'resourcename' => $resourcenames[$ctr],
                    'position' => ($maxpos +1),
                ]);
                $event = \mod_oercollection\event\oer_resource_added::create($eventparams);
                $event->trigger();
            }
            $ctr++;
        }
    }
    
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function set_visibility_oerentry_returns() {
        return null;
    }
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function set_visibility_all_returns() {
        return null;
    }
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function delete_oerentry_returns() {
        return null;
    }
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function delete_selected_oerentries_returns() {
        return null;
    }
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function add_entry_to_collection_returns() {
        return null;
    }
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function add_entries_to_collection_returns() {
        return null;
    }
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function move_resource_returns() {
        return null;
    }
}