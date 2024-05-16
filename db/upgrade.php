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
 * @author    Adrian Czermak
 * @author    Angela Baier
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_oercollection_upgrade($oldversion = 0) {
    global $DB, $CFG;
    
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2024041701) {
        
        // Define table oercollection_resource to be created.
        $table = new xmldb_table('oercollection_resource');
        
        // Adding fields to table oercollection_resource.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('oerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('oerresourceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('apipluginid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('showresource', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('order', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('notenameinternal', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('notetextinternal', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('resourcelink', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('resourcename', XMLDB_TYPE_TEXT, null, null, null, null, null);
        
        // Adding keys to table oercollection_resource.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        
        // Conditionally launch create table for oercollection_resource.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Oercollection savepoint reached.
        upgrade_mod_savepoint(true, 2024041701, 'oercollection');
    }

    if ($oldversion < 2024041702) {
        
        // Define field showseperate to be added to oercollection.
        $table = new xmldb_table('oercollection');
        $field = new xmldb_field('showseperate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        
        // Conditionally launch add field showseperate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Oercollection savepoint reached.
        upgrade_mod_savepoint(true, 2024041702, 'oercollection');
    }

    if ($oldversion < 2024041703) {
        
        // Rename field displaymode on table oercollection to NEWNAMEGOESHERE.
        $table = new xmldb_table('oercollection');
        $field = new xmldb_field('showseperate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        
        // Launch rename field displaymode.
        $dbman->rename_field($table, $field, 'displaymode');
        
        // Oercollection savepoint reached.
        upgrade_mod_savepoint(true, 2024041703, 'oercollection');
    }
    
    if ($oldversion < 2024041704) {
        
        // Rename field position on table oercollection_resource to NEWNAMEGOESHERE.
        $table = new xmldb_table('oercollection_resource');
        $field = new xmldb_field('order', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'showresource');
        
        // Launch rename field position.
        $dbman->rename_field($table, $field, 'position');
        
        // Oercollection savepoint reached.
        upgrade_mod_savepoint(true, 2024041704, 'oercollection');
    }

    // Everything has succeeded to here. Return true.
    return true;
}