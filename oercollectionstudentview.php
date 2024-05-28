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

global $PAGE, $OUTPUT, $DB;

$id = required_param('id', PARAM_INT); //cmid!!
$listview = optional_param('listview', 0, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/oercollection:view', $context);

$oerid = $DB->get_record('oercollection', array('id' => $cm->instance));

$oercollection = $DB->get_record('oercollection', array('id' => $cm->instance));

$PAGE->set_url(new moodle_url("/mod/oercollection/oercollectionstudentview.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$PAGE->set_title($oercollection->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

$sql = 'SELECT * FROM {oercollection_resource} oerr WHERE oerr.oerid = :oerid AND oerr.showresource = 1';
$oerentries = $DB->get_records_sql($sql, ['oerid' => $oerid->id]); //, "position ASC"

$templatecontext = [];

if (has_capability('mod/oercollection:editresources', $context)) {
    $oerexists = $oerentries ? true : false;
    if ($oerexists) {
        $templatecontext['oernumber'] = $DB->count_records('oercollection_resource', ['oerid' => $oerid->id]);
    }
    $templatecontext['oerid'] = $oerid->id;
    $templatecontext['oerexists'] = $oerexists;
    if ($oerexists) {
        $templatecontext['linktext'] = 'bla';
    } else {
        $templatecontext['link'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $cm->id]);
    }
}

//=========== Dummy template for table
$oerhtml = '<div>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.<br>
        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
      </div>';

// get oer entries

$oerlist = [];

foreach ($oerentries as $oerentry) {
    $commentexists = true;
    if (is_null($oerentry->notetextinternal) || empty($oerentry->notetextinternal)) {
        $commentexists = false;
    }
    $oerlist[] = array(
        'oerentryid' => $oerentry->id,
        'oerhtml' => $oerhtml,
        'commentexists' => $commentexists,
        'commenttext' => $oerentry->notetextinternal,
        'commentname' => $oerentry->notenameinternal,
    );
}

$templatecontext['oerresourcelist'] = $oerlist;

$backtoteacherview = has_capability('mod/oercollection:editresources', $context);

$templatecontext['backtoteacherview'] = $backtoteacherview;
$templatecontext['backtoteacherviewlink'] = new moodle_url("/mod/oercollection/oercollectionteacherview.php", ['id' => $id]);

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
echo $renderer->render_from_template('mod_oercollection/studentresources', $templatecontext);

echo $renderer->footer();