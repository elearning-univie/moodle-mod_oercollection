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
 * API definition.
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oercollection\api;

/**
 * Defines the API
 */
abstract class general {

    /**
     * get_resource_html
     *
     * @param int $id
     * @return mixed
     */
    abstract public function get_resource_html($id);


    /**
     * get_search_form
     *
     * @return mixed
     */
    abstract public function get_search_form();


    /**
     * get_results
     *
     * @param object $searchstring
     * @return mixed
     */
    abstract public function get_results($searchstring);

    /**
     * is_api_available
     * 
     * @return boolean
     */
    abstract public function is_api_available();
}
