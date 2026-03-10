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
 * Activity index for the mod_oercollection plugin.
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once('locallib.php');
require_once(__DIR__ . '/resource_list_builder.php');

global $PAGE, $OUTPUT, $DB, $CFG;

$cmid = required_param('id', PARAM_INT);
$filter = optional_param('oerefilter', 1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$deleted = optional_param('del', 0, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'oercollection');
require_login($course, false, $cm);

$perpage = oercollection_get_perpage();

$context = context_module::instance($cm->id);
oercollection_require_capability($context, $cmid);

$oerid = $DB->get_record('oercollection', ['id' => $cm->instance]);

$params = [
    'id' => $cmid,
    'del' => $deleted,
    'perpage' => $perpage,
];
if ($page) {
    $params['page'] = $page;
}

$homeurl = new moodle_url("/mod/oercollection/resources.php", $params);
$PAGE->set_url($homeurl);
$PAGE->set_title($oerid->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

oercollection_activate_settings_node();

// Get resource counts using centralized function
$counts = oercollection_get_resource_counts($oerid->id);

// Determine visibility filter and filtered count based on filter parameter
$visibility_filter = 'all';
$filteredcount = $counts['total'];
if ($filter == 2) {
    $visibility_filter = 'visible';
    $filteredcount = $counts['visible'];
} else if ($filter == 3) {
    $visibility_filter = 'hidden';
    $filteredcount = $counts['hidden'];
}

// Fetch and format OER resources using centralized function
$resource_data = oercollection_get_resources_for_display(
    $oerid->id,
    $PAGE->url,
    [
        'visibility_filter' => $visibility_filter,
        'use_caching' => true,
        'include_metadata' => true,
        'include_comment_link' => true,
        'cmid' => $cmid,
        'per_page' => $perpage,
        'page_offset' => $page * $perpage
    ]
);

// Prepare template context
$templatecontext = [
    'oernumber' => $counts['total'],
    'oernumberhidden' => $counts['hidden'],
    'actionurl' => $PAGE->url,
    'id' => $cmid,
    'deleted' => $deleted,
    'selected' . $perpage => true,
    'selected2' . $filter => true,
    'sesskey' => sesskey(),
    'oerid' => $oerid->id,
    'oerexists' => !empty($resource_data['oerresourcelist']),
    'searchoer' => new moodle_url("/mod/oercollection/searchoer.php", ['id' => $cmid]),
    'studentpreviewlink' => new moodle_url("/mod/oercollection/studentview.php", ['id' => $cmid]),
];

// Merge resource data into template context
$templatecontext = array_merge($templatecontext, $resource_data);

// Prepare modal data with array_chunk
$allresources = $DB->get_records('oercollection_resource', ['oerid' => $oerid->id], 'position ASC');
$chunkedresources = array_chunk($allresources, $perpage);
$pagedresources = [];
foreach ($chunkedresources as $index => $chunk) {
    $pagedresources[] = [
        'lines' => array_map(function($resource) {
            return [
                'id' => $resource->id,
                'name' => s($resource->resourcename),
                'hidden' => !$resource->showresource,
            ];
        }, $chunk),
        'title' => get_string('page') . ' ' . ($index + 1),
        'pnr' => $index + 1,
        'open' => ($index === $page),
    ];
}

$templatecontext['resourcecount'] = count($resource_data['oerresourcelist']);
$templatecontext['page'] = $pagedresources;

$PAGE->requires->js_call_amd('mod_oercollection/resourcecontroller', 'init');
$PAGE->requires->js_call_amd('mod_oercollection/defaultcontroller', 'init');
$PAGE->requires->js_call_amd('mod_oercollection/sync_controller', 'init');

// Output rendering
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_oercollection/resources', $templatecontext);
echo $OUTPUT->paging_bar($filteredcount, $page, $perpage, $homeurl);
if ($counts['total']) {
    echo $OUTPUT->render_from_template('mod_oercollection/resourcesactionsandoptions', $templatecontext);
}
echo $OUTPUT->footer();
