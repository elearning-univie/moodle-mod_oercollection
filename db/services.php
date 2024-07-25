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
 * External service definition
 *
 * @package    mod_oercollection
 * @copyright  2021 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = [
    'oercollectionservice' => [
        'functions' => [
            'mod_oercollection_set_visibility_oerentry',
            'mod_oercollection_set_visibility_all',
            'mod_oercollection_delete_oerentry',
            'mod_oercollection_delete_selected_oerentries',
            'mod_oercollection_add_to_collection',
            'mod_oercollection_move_resource',
        ],
        'shortname' => 'oercollection',
        'requiredcapability' => 'mod/oercollection:addinstance',
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];

$functions = array(
    'mod_oercollection_set_visibility_oerentry' => array(
        'classname' => 'mod_oercollection_external',
        'methodname' => 'set_visibility_oerentry',
        'classpath' => 'mod/oercollection/externallib.php',
        'description' => 'Change visibility setting of an oer collection entry',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'mod_oercollection_set_visibility_all' => array(
        'classname' => 'mod_oercollection_external',
        'methodname' => 'set_visibility_all',
        'classpath' => 'mod/oercollection/externallib.php',
        'description' => 'Change visibility setting of an oer collection entry',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'mod_oercollection_delete_oerentry' => array(
        'classname' => 'mod_oercollection_external',
        'methodname' => 'delete_oerentry',
        'classpath' => 'mod/oercollection/externallib.php',
        'description' => 'Change visibility setting of an oer collection entry',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'mod_oercollection_delete_selected_oerentries' => array(
        'classname' => 'mod_oercollection_external',
        'methodname' => 'delete_selected_oerentries',
        'classpath' => 'mod/oercollection/externallib.php',
        'description' => 'Change visibility setting of an oer collection entry',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'mod_oercollection_add_to_collection' => array(
        'classname' => 'mod_oercollection_external',
        'methodname' => 'add_entry_to_collection',
        'classpath' => 'mod/oercollection/externallib.php',
        'description' => 'Change visibility setting of an oer collection entry',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'mod_oercollection_move_resource' => array(
        'classname' => 'mod_oercollection_external',
        'methodname' => 'move_resource',
        'classpath' => 'mod/oercollection/externallib.php',
        'description' => 'Change visibility setting of an oer collection entry',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ),
);
