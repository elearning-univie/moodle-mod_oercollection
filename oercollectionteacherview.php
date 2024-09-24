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

// $id = required_param('id', PARAM_INT);
// [$course, $cm] = get_course_and_cm_from_cmid($id, 'oercollection');
// $instance = $DB->get_record('oercollection', ['id'=> $cm->instance], '*', MUST_EXIST);

global $PAGE, $OUTPUT, $DB, $CFG;

$id = required_param('id', PARAM_INT);
$listview = optional_param('listview', 0, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/oercollection:view', $context);

$caneditresources = has_capability('mod/oercollection:editresources', $context);

if (!has_capability('mod/oercollection:editresources', $context)) {
    $url = new moodle_url("/mod/oercollection/oercollectionstudentview.php", ['id' => $cm->id]);
    redirect($url);
}

$oercollection = $DB->get_record('oercollection', array('id' => $cm->instance));

$PAGE->set_url(new moodle_url("/mod/oercollection/view.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}
$PAGE->add_body_class('limitedwidth');

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oercollection->name);
$PAGE->set_heading($course->shortname);

$oeretotalnumber = $DB->count_records('oercollection_resource', ['oerid' => $oercollection->id]);
$oerevisiblenumber = $DB->count_records('oercollection_resource', ['oerid' => $oercollection->id, 'showresource' => 1]);
$oerehiddennumber = $DB->count_records('oercollection_resource', ['oerid' => $oercollection->id, 'showresource' => 0]);

$templatecontext = [];
$oerexists = false;

//if ($caneditresources) {
if ($oeretotalnumber) {
    $oerexists = true;
    $templatecontext['oeretotalnumber'] = $oeretotalnumber;
    $templatecontext['oerevisiblenumber'] = $oerevisiblenumber; // oerefilter=2
    $templatecontext['oerehiddennumber'] = $oerehiddennumber; // oerefilter=3
    $linkvisible = new moodle_url("/mod/oercollection/resources.php", ['id' => $id, 'oerefilter' => 2]);
    $templatecontext['oerresourcelinkvisible'] = $linkvisible->out(false);
    $linkhidden = new moodle_url("/mod/oercollection/resources.php", ['id' => $id, 'oerefilter' =>3]);
    $templatecontext['oerresourcelinkhidden'] = $linkhidden->out(false);
    
}
    $templatecontext['oerexists'] = $oerexists;
    $templatecontext['oerresourcelink'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $id]);
    $templatecontext['oersearchlink'] = new moodle_url("/mod/oercollection/searchoer.php", ['id' => $id]);
    $templatecontext['studentpreviewlink'] = new moodle_url("/mod/oercollection/oercollectionstudentview.php", ['id' => $id]);
    
    
    
    if ($oerexists) {
        $templatecontext['linktext'] = 'bla';
    } else {
        $templatecontext['link'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $id]);
    }
//}

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
//if ($caneditresources) {
    echo $renderer->render_from_template('mod_oercollection/oercollectionmain', $templatecontext);
// } else {
//     echo $renderer->render_from_template('mod_oercollection/studentresources', $templatecontext);
    
// }

echo $renderer->footer();