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

global $PAGE, $OUTPUT, $DB, $CFG;

$id = required_param('id', PARAM_INT);
$searchstring = optional_param('searchstring', null, PARAM_TEXT);
$filter = optional_param('filterdata', null, PARAM_TEXT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/oercollection:editresources', $context);

$oercollection = $DB->get_record('oercollection', array('id' => $cm->instance));

$PAGE->set_url(new moodle_url("/mod/oercollection/searchoer.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oercollection->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

$PAGE->requires->js_call_amd('mod_oercollection/searchcontroller', 'init');

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();

$searchform = new \oerapi_oerhub\api\general($PAGE->url, $oercollection->id);
$templatecontext = [
    'searchoer' => new moodle_url("/mod/oercollection/resources.php", ['id' => $id]),
    'searchform' => $searchform->get_search_form($searchstring),
    'actionurl' => $PAGE->url,
];

if (!is_null($searchstring)) {
    $oersearchresults = [];
    $templatecontext['resultlist'] = $searchform->get_results($searchstring, $filter);
}

echo $renderer->render_from_template('mod_oercollection/searchoer', $templatecontext);
echo $renderer->footer();
