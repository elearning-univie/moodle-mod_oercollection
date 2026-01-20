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

global $PAGE, $OUTPUT, $DB, $CFG;

$id = required_param('id', PARAM_INT);
$searchstring = optional_param('searchstring', null, PARAM_TEXT);
$filter = optional_param('filterdata', null, PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$reset = optional_param('reset', null, PARAM_TEXT);
$perpage = optional_param('perpage', 20, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');
$context = context_module::instance($cm->id);

$perpage = oercollection_validate_perpage($perpage);

require_login($course, true, $cm);
oercollection_require_capability($context, $cm->id);

$oercollection = $DB->get_record('oercollection', ['id' => $cm->instance]);

$params = [
    'id' => $id,
];
if ($searchstring) {
    $params['searchstring'] = $searchstring;
}
if ($page) {
    $params['page'] = $page;
}
if ($filter) {
    $params['filterdata'] = $filter;
}
if ($perpage) {
    $params['perpage'] = $perpage;
}

$PAGE->set_url(new moodle_url("/mod/oercollection/searchoer.php", $params));
oercollection_activate_settings_node();

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oercollection->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

$PAGE->requires->js_call_amd('mod_oercollection/searchcontroller', 'init');
$PAGE->requires->js_call_amd('mod_oercollection/resourcecontroller', 'init');
$PAGE->requires->js_call_amd('mod_oercollection/defaultcontroller', 'init');


$renderer = $PAGE->get_renderer('core');
//echo $renderer->header();

$searchform = new \oerapi_oerhub\api\general($PAGE->url, $oercollection->id);
$helpicon = new help_icon('searchoerhub', 'oercollection');
$templatecontext = [
    'searchoer' => new moodle_url("/mod/oercollection/resources.php", ['id' => $id]),
    'searchform' => $searchform->get_search_form($searchstring),
    'actionurl' => new moodle_url("/mod/oercollection/searchoer.php", ['id' => $id]),
    'oerid' => $oercollection->id,
    'helpicon' => $helpicon->export_for_template($renderer),
];

$apiavailable = $searchform->is_api_available();
$resultsarray = null;

if (!$apiavailable) {
    $templatecontext['apiwarning'] = get_string('resourceunavailable', 'oerapi_oerhub');
} else {
    if (!is_null($reset)) {
        $filter = '{}';
    }

    if (!is_null($searchstring)) {
        $oersearchresults = [];
        $resultsarray = $searchform->get_results($searchstring, $filter, $page, $perpage);
        $templatecontext['resultlist'] = $resultsarray['resulthtml'];
    }
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_oercollection/searchoer', $templatecontext);

if (!is_null($searchstring) && $resultsarray !== null) {
    echo $OUTPUT->paging_bar($resultsarray['foundcount'], $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
