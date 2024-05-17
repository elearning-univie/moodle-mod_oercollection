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
    
//     /**
//      * Removes all selected questions from box 1 to box 0 for the activity
//      *
//      * @param int $oerid
//      * @param int $oerentryid
//      */
//     public static function delete_oerentry($oerid, $oerentryid) {
//         global $DB, $USER;

//         if ($DB->record_exists('oercollection_resource', ['id' => $oerentryid, 'oerid' => $oerid])) {
//             $DB->delete_records('oercollection_resource', ['id' => $oerentryid, 'oerid' => $oerid]);
//         }
//     }
    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function set_visibility_oerentry_returns() {
        return null;
    }

}