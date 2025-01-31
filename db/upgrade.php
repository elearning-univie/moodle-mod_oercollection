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
 * Upgrade steps for the mod_oercollection plugin.
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * xmldb_oercollection_upgrade
 *
 * @param int $oldversion
 * @return true
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 */
function xmldb_oercollection_upgrade($oldversion = 0) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024041710.10) {
        $table = new xmldb_table('oercollection_resource');
        $field = new xmldb_field('oerresourceid', XMLDB_TYPE_TEXT);

        $dbman->change_field_type($table, $field);

        // Oercollection savepoint reached.
        upgrade_mod_savepoint(true, 2024041710.10, 'oercollection');
    }

    if ($oldversion < 2024041710.12) {
        $table = new xmldb_table('oercollection_resource');
        $field = new xmldb_field('oerresourceid', XMLDB_TYPE_CHAR, '255');

        $dbman->change_field_type($table, $field);

        // Oercollection savepoint reached.
        upgrade_mod_savepoint(true, 2024041710.12, 'oercollection');
    }

    // Everything has succeeded to here. Return true.
    return true;
}
