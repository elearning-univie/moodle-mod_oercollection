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
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('modsettings', new admin_category('modsettingoercollectionfolder',
    new lang_string('pluginname', 'mod_oercollection'), !$module->is_enabled()));

$settings = new admin_settingpage('modsettingoercollection',
    get_string('settings', 'oercollection'),
    'moodle/site:config',
    !$module->is_enabled());

if ($ADMIN->fulltree) {
    $plugins = \mod_oercollection\oerapi\factory::get_available_plugins();
    if (!empty($plugins)) {
        $settings->add(new admin_setting_configselect(
            'mod_oercollection/activeoerapi',
            get_string('activeoerapi', 'oercollection'),
            get_string('activeoerapi_desc', 'oercollection'),
            \mod_oercollection\oerapi\factory::DEFAULT_PLUGIN,
            $plugins
        ));
    }
}
// Load plugin settings into the main settings page.
foreach (core_plugin_manager::instance()->get_plugins_of_type('oerapi') as $plugin) {
    $plugin->load_settings($ADMIN, 'oerapiplugins', $hassiteconfig, $settings);
}

$ADMIN->add('modsettingoercollectionfolder', $settings);

$ADMIN->add('modsettingoercollectionfolder', new admin_category('oerapiplugins',
    new lang_string('oerapiplugins', 'oercollection'), !$module->is_enabled()));

$settings = null;

$ADMIN->add('oerapiplugins', new admin_externalpage('manageoerapiplugins',
    get_string('manageoerapiplugins', 'oercollection'),
    new moodle_url('/mod/oercollection/adminmanageplugins.php', ['subtype' => 'oerapi'])));
