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
                'show' => new external_value(PARAM_INT, 'oer entry id', VALUE_REQUIRED),
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
        
       // $DB->get_in_or_equal($items);
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

        list($inids, $oereids) = $DB->get_in_or_equal($oerentryids);
        $sql = "SELECT * FROM {oercollection_resource} oer
                        WHERE oer.id $inids";
        $oerentries = $DB->get_records_sql($sql, $oereids);
        foreach ($oerentries as $oerentry) {
            if ($oerentry) {
                $DB->delete_records('oercollection_resource', ['id' => $oerentry->id, 'oerid' => $oerid]);
            }
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
        
        if ($DB->record_exists('oercollection_resource', ['id' => $oerentryid, 'oerid' => $oerid])) {
            $DB->delete_records('oercollection_resource', ['id' => $oerentryid, 'oerid' => $oerid]);
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
}