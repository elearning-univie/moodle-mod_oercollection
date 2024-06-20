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
 * This file adds the settings pages to the navigation menu
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('modsettings', new admin_category('modoercollectionfolder', new lang_string('pluginname', 'mod_oercollection'), $module->is_enabled() === false));

$ADMIN->add('modoercollectionfolder', new admin_category('oerapiplugins',
    new lang_string('oerapiplugins', 'oercollection'), !$module->is_enabled()));
$ADMIN->add('oerapiplugins', new admin_externalpage('manageoerapiplugins',
    get_string('manageoerapiplugins', 'oercollection'),
    new moodle_url('/mod/assign/adminmanageplugins.php', array('subtype'=>'oerapi'))));

foreach (core_plugin_manager::instance()->get_plugins_of_type('oerapi') as $plugin) {
    $plugin->load_settings($ADMIN, 'oerapiplugins', $hassiteconfig);
}

