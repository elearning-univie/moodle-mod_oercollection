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
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('DEFAULT_PAGE_SIZE', 5000);

/**
 * oercollection_add_to_cache
 *
 * @param int $oerid
 * @param int $oerresourceid
 * @return void
 * @throws \core\exception\coding_exception
 */
function oercollection_add_to_cache($oerid, $oerresourceid) {
    $apicache = cache::make('mod_oercollection', 'entries');
    $oerapi = new \oerapi_oerhub\api\general('', $oerid);
    $cacheobj = $oerapi->get_resource_html($oerresourceid);

    if (!$apicache->get($oerresourceid)) {
        $apicache->set($oerresourceid, $cacheobj);
    }
}
