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
 * Oercollection comment page
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

$cmid = required_param('id', PARAM_INT);
$oerentryid = required_param('oereid', PARAM_INT);

$url = new moodle_url("/mod/oercollection/oercomment.php", ['id' => $cmid, 'oereid' => $oerentryid]);
$PAGE->set_url($url);

//list ($course, $cm) = get_course_and_cm_from_cmid($oerid, 'oercollection');
list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'oercollection');
$oerid = $cm->instance;
$context = \context::instance_by_id($oerid);

//context = \context::instance_by_id($this->oerid);
require_login($course, false, $cm);
require_capability('mod/oercollection:view', $context);

$oer = $DB->get_record('oercollection', array('id' => $cm->instance));


$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oer->name);
//$PAGE->set_title("Titolooo");
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

$oerentry = $DB->get_record('oercollection_resource', ['id' => $oerentryid]);


$origin = new moodle_url("/mod/oercollection/resources.php", ['id' => $cmid]);

$mform = new mod_oercollection\form\oercommentform($url->out(false), $oerentryid, $oerid, $cmid, true);

$formdata = new stdClass();
$formdata->notenameinternal = $oerentry->notenameinternal;
$formdata->notetextinternal['text'] = $oerentry->notetextinternal;
$mform->set_data($formdata);

if ($mform->is_cancelled()) {
    $DB->update_record('oercollection_resource', $oerentry);
    redirect($origin->out(false));
} else if ($fromform = $mform->get_data()) {
    $oerentry->notenameinternal = $fromform->notenameinternal;
    $oerentry->notetextinternal = $fromform->notetextinternal['text'];
   /// print_object($oerentry);
    $DB->update_record('oercollection_resource', $oerentry);
    redirect($origin->out(false));
}

$PAGE->set_title("Anmerkungen");
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add("Anmerkungen");
$activityheader = $PAGE->activityheader;
// $activityheader->set_attrs([
//     'description' => '',
//     'hidecompletion' => true,
// ]);

//echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
$mform->display();
echo $renderer->footer();
//echo $OUTPUT->footer();
