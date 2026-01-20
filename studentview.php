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
 * @author    Adrian Czermak
 * @author    Angela Baier
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once('locallib.php');
require_once(__DIR__ . '/resource_list_builder.php');

global $PAGE, $OUTPUT, $DB;

$id = required_param('id', PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
$pg = optional_param('page', 0, PARAM_INT);
$filter = optional_param('oerefilter', 1, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');

$context = context_module::instance($cm->id);

$perpage = oercollection_validate_perpage($perpage);

require_login($course, false, $cm);
require_capability('mod/oercollection:view', $context);

$oercollection = $DB->get_record('oercollection', ['id' => $cm->instance]);

$params = [];
$params['id'] = $id;
$params['perpage'] = $perpage;
if ($pg) {
    $params['page'] = $pg;
}

// Completion.
oercollection_view($oercollection, $course, $cm, $context);

$homeurl = new moodle_url("/mod/oercollection/studentview.php", $params);
$PAGE->set_url($homeurl->out(false));

oercollection_activate_settings_node();

$PAGE->set_title($oercollection->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

// Pagination.
$offset = ($pg) * $perpage;
$counts = oercollection_get_resource_counts($oercollection->id);
$totalnumberresources = $counts['visible'];

// Initialize template context
$templatecontext = [];

// Fetch and format OER resources using centralized function
$resource_data = oercollection_get_resources_for_display(
    $oercollection->id,
    $PAGE->url,
    [
        'show_hidden' => false,
        'use_caching' => true,
        'per_page' => $perpage,
        'page_offset' => $offset
    ]
);
$templatecontext = array_merge($templatecontext, $resource_data);

$backtoteacherview = has_capability('mod/oercollection:editresources', $context);

$templatecontext['backtoteacherview'] = $backtoteacherview;
$templatecontext['backtoteacherviewlink'] = new moodle_url("/mod/oercollection/teacherview.php", ['id' => $id]);
$templatecontext['selected' . $perpage] = true;
$templatecontext['actionurl'] = $PAGE->url;
$templatecontext['sesskey'] = sesskey();
$templatecontext['id'] = $id;

$PAGE->requires->js_call_amd('mod_oercollection/resourcecontroller', 'init');

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
echo $renderer->render_from_template('mod_oercollection/studentresources', $templatecontext);
echo $OUTPUT->paging_bar($totalnumberresources, $pg, $perpage, $homeurl);
echo $renderer->footer();
