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
require_once(__DIR__ . '/resource_list_builder.php');

global $PAGE, $OUTPUT, $DB, $CFG;

$id = required_param('id', PARAM_INT);
$listview = optional_param('listview', 0, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);

oercollection_require_capability($context, $cm->id);

$oercollection = $DB->get_record('oercollection', ['id' => $cm->instance]);

$PAGE->set_url(new moodle_url("/mod/oercollection/view.php", ['id' => $id]));
oercollection_activate_settings_node();
$PAGE->add_body_class('limitedwidth');

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oercollection->name);
$PAGE->set_heading($course->shortname);

// Completion.
oercollection_view($oercollection, $course, $cm, $context);

// Get resource counts using centralized function
$counts = oercollection_get_resource_counts($oercollection->id);

$templatecontext = [
    'oerexists' => false,
    'oerresourcelink' => new moodle_url("/mod/oercollection/resources.php", ['id' => $id]),
    'oersearchlink' => new moodle_url("/mod/oercollection/searchoer.php", ['id' => $id]),
    'studentpreviewlink' => new moodle_url("/mod/oercollection/studentview.php", ['id' => $id]),
];

if ($counts['total']) {
    $templatecontext['oerexists'] = true;
    $templatecontext['oeretotalnumber'] = $counts['total'];
    $templatecontext['oerevisiblenumber'] = $counts['visible'];
    $templatecontext['oerehiddennumber'] = $counts['hidden'];
    $linkvisible = new moodle_url("/mod/oercollection/resources.php", ['id' => $id, 'oerefilter' => 2]);
    $templatecontext['oerresourcelinkvisible'] = $linkvisible->out(false);
    $linkhidden = new moodle_url("/mod/oercollection/resources.php", ['id' => $id, 'oerefilter' => 3]);
    $templatecontext['oerresourcelinkhidden'] = $linkhidden->out(false);
}

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
echo $renderer->render_from_template('mod_oercollection/collectionmain', $templatecontext);
echo $renderer->footer();
