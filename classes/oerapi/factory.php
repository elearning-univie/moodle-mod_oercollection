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
 * Factory class for OER API plugin instantiation.
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oercollection\oerapi;

use mod_oercollection\api\general;
use mod_oercollection\plugininfo\oerapi as oerapi_plugininfo;

/**
 * Factory class for creating OER API instances.
 * Removes hard-coded references to specific oerapi subplugins.
 */
class factory {
    const CONFIG_ACTIVE_PLUGIN = 'activeoerapi';
    const DEFAULT_PLUGIN = 'oerhub';

    /**
     * Get the currently active OER API plugin name.
     * Falls back to the default plugin if no enabled plugin configured. 
     * 
     * @return string The plugin name
     */
    public static function get_active_plugin(): string {
        $configured = get_config('mod_oercollection', self::CONFIG_ACTIVE_PLUGIN);
        if (empty($configured)) {
            return self::DEFAULT_PLUGIN;
        }
        $enabledplugins = oerapi_plugininfo::get_enabled_plugins();
        if (!isset($enabledplugins[$configured])) {
            return self::DEFAULT_PLUGIN;
        }

        return $configured;
    }

    /**
     * Get list of available OER API plugins for selection.
     *
     * @return array Associative array of pluginname => display name
     */
    public static function get_available_plugins(): array {
        $enabledplugins = oerapi_plugininfo::get_enabled_plugins();
        $available = [];
        foreach ($enabledplugins as $pluginname => $unused) {
            $available[$pluginname] = get_string('pluginname', 'oerapi_' . $pluginname);
        }
        return $available;
    }

    /**
     * Create an instance of the active OER API plugin.
     *
     * @param mixed $baseurl The base URL for the API (string or moodle_url)
     * @param int $oercollectionid The OER collection instance ID
     * @return general|null The API instance, or null if unavailable
     */
    public static function create($baseurl, int $oercollectionid): ?general {
        $pluginname = self::get_active_plugin();
        $classname = "\\oerapi_{$pluginname}\\api\\general";

        if (!class_exists($classname)) {
            debugging("OER API class not found: {$classname}", DEBUG_DEVELOPER);
            return null;
        }

        return new $classname($baseurl, $oercollectionid);
    }

    /**
     * Get the full component name of the active plugin.
     *
     * @return string Component name
     */
    public static function get_active_component(): string {
        return 'oerapi_' . self::get_active_plugin();
    }
}
