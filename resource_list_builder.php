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
 * Centralized OER resource data fetching and formatting for display
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_oercollection\oerapi\factory;

/**
 * Fetch and format OER resources for template display.
 * Centralizes the duplicate logic.
 *
 * @param int $oerid OER collection ID
 * @param object $page_url Current page URL for API initialization
 * @param array $options Configuration options
 * @return array Template context with 'oerresourcelist' and 'apiwarning'
 */
function oercollection_get_resources_for_display($oerid, $page_url, $options = []) {
    global $DB, $OUTPUT;

    $defaults = [
        'show_hidden' => false,
        'visibility_filter' => null,
        'use_caching' => true,
        'include_metadata' => false,
        'include_comment_link' => false,
        'cmid' => 0,
        'per_page' => 5000,
        'page_offset' => 0,
        'notification_wrapper' => false,
    ];
    $options = array_merge($defaults, $options);

    $templatecontext = [];

    // Fetch OER entries from database and init api.
    $oerentries = oercollection_fetch_oer_entries($oerid, $options);
    $oerapi = factory::create($page_url, $oerid);

    if ($oerapi === null) {
        $templatecontext['apiwarning'] = get_string('nooerapiplugins', 'oercollection');
        $templatecontext['oerresourcelist'] = [];
        return $templatecontext;
    }

    $apiavailable = $oerapi->is_api_available();

    if (!$apiavailable && !empty($oerentries)) {
        $warningtext = get_string('resourceunavailable', 'oercollection');
        if ($options['notification_wrapper']) {
            $templatecontext['apiwarning'] = $OUTPUT->notification($warningtext, 'info');
        } else {
            $templatecontext['apiwarning'] = $warningtext;
        }
    }

    $oerlist = [];

    if ($apiavailable && !empty($oerentries)) {
        $apicache = null;
        $cachedresources = [];
        if ($options['use_caching']) {
            $apicache = cache::make('mod_oercollection', 'entries');
            $resourceids = array_column($oerentries, 'oerresourceid');
            $cachedresources = $apicache->get_many($resourceids);
        }

        foreach ($oerentries as $oerentry) {
            $oerhtml = null;
            if ($options['use_caching']) {
                if (isset($cachedresources[$oerentry->oerresourceid]) &&
                    $cachedresources[$oerentry->oerresourceid] !== false) {
                    $oerhtml = $cachedresources[$oerentry->oerresourceid];
                } else {
                    $oerhtml = $oerapi->get_resource_html($oerentry->oerresourceid);
                    if ($oerhtml !== null) {
                        $apicache->set($oerentry->oerresourceid, $oerhtml);
                    }
                }
            } else {
                $oerhtml = $oerapi->get_resource_html($oerentry->oerresourceid);
            }

            // Format entry for template.
            $oerlist[] = oercollection_format_entry_for_template($oerentry, $oerhtml, $options);
        }
    }

    $templatecontext['oerresourcelist'] = $oerlist;

    return $templatecontext;
}

/**
 * Fetch OER entries from database with filtering and pagination
 *
 * @param int $oerid OER collection ID
 * @param array $options Query options (show_hidden, per_page, page_offset)
 * @return array Database records
 */
function oercollection_fetch_oer_entries($oerid, $options) {
    global $DB;

    // Where clause for visibility filter.
    $sqlshow = "";

    // Check advanced visibility_filter first.
    if ($options['visibility_filter'] !== null) {
        switch ($options['visibility_filter']) {
            case 'visible':
                $sqlshow = " AND showresource = 1 ";
                break;
            case 'hidden':
                $sqlshow = " AND showresource = 0 ";
                break;
            case 'all':
            default:
                $sqlshow = "";
                break;
        }
    } else {
        // Fall back to simple show_hidden boolean
        if (!$options['show_hidden']) {
            $sqlshow = " AND showresource = 1 ";
        }
    }

    $sql = "SELECT *
              FROM {oercollection_resource}
             WHERE oerid = :oerid $sqlshow
          ORDER BY position ASC";

    $params = ['oerid' => $oerid];

    // Apply pagination if specified
    $limitfrom = $options['page_offset'];
    $limitnum = $options['per_page'];

    $oerentries = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

    return $oerentries;
}

/**
 * Format single OER entry for template
 *
 * @param object $oerentry Database record
 * @param string $oerhtml Rendered HTML from API
 * @param array $options Display options
 * @return array Formatted entry data
 */
function oercollection_format_entry_for_template($oerentry, $oerhtml, $options) {
    // Basic data common to all views.
    $entry = [
        'oerentryid' => $oerentry->id,
        'oerhtml' => $oerhtml,
        'resourceloadfailed' => empty($oerhtml),
        'commentexists' => oercollection_has_comment($oerentry),
        'commenttext' => format_text($oerentry->notetextinternal, FORMAT_MOODLE),
        'commentname' => s($oerentry->notenameinternal),
    ];

    // Metadata teacherview.
    if ($options['include_metadata']) {
        $entry['oerhidden'] = !$oerentry->showresource;
        $entry['resourcelink'] = $oerentry->resourcelink;
        $entry['resourcename'] = s($oerentry->resourcename);
        $entry['background'] = $oerentry->showresource ? '' : 'bg-light';
    }

    // Teacher view comment edit link.
    if ($options['include_comment_link'] && $options['cmid']) {
        $commentlink = new moodle_url("/mod/oercollection/oercomment.php", [
            'id' => $options['cmid'],
            'oereid' => $oerentry->id,
        ]);
        $entry['commentlink'] = $commentlink->out(false);
    }

    return $entry;
}

/**
 * Check if comment exists on entry
 *
 * @param object $oerentry Database record
 * @return bool True if comment exists
 */
function oercollection_has_comment($oerentry) {
    return !empty($oerentry->notetextinternal);
}

/**
 * Get resource counts for an OER collection
 *
 * Returns total, visible, and hidden counts in a single call,
 * reducing duplicate database queries across views.
 *
 * @param int $oerid OER collection ID
 * @return array ['total' => int, 'visible' => int, 'hidden' => int]
 */
function oercollection_get_resource_counts($oerid) {
    global $DB;

    return [
        'total' => $DB->count_records('oercollection_resource', ['oerid' => $oerid]),
        'visible' => $DB->count_records('oercollection_resource', ['oerid' => $oerid, 'showresource' => 1]),
        'hidden' => $DB->count_records('oercollection_resource', ['oerid' => $oerid, 'showresource' => 0]),
    ];
}

/**
 * Validate and return a valid perpage value
 *
 * @param int $perpage Requested page size
 * @return int Valid page size (returns DEFAULT_PAGE_SIZE if invalid)
 */
function oercollection_validate_perpage($perpage) {
    $validperpages = [5, 10, 20, 50, 100, 5000];
    return in_array($perpage, $validperpages, true) ? $perpage : DEFAULT_PAGE_SIZE;
}
