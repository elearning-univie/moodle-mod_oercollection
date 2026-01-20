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

global $PAGE, $OUTPUT, $DB, $CFG, $COURSE;

$cmid = required_param('id', PARAM_INT);
$oerentryid = required_param('oereid', PARAM_INT);

$url = new moodle_url("/mod/oercollection/oercomment.php", ['id' => $cmid, 'oereid' => $oerentryid]);
$PAGE->set_url($url);

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'oercollection');
$oerid = $cm->instance;
$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/oercollection:editresources', $context);

$oer = $DB->get_record('oercollection', ['id' => $cm->instance]);

oercollection_activate_settings_node();

$pagetitle = get_string('pagetitle', 'mod_oercollection');
$PAGE->set_title($oer->name);
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
    if ($fromform->notenameinternal == "" || $fromform->notenameinternal == null) {
        $oerentry->notenameinternal = get_string('comment', 'oercollection');
    } else {
        $oerentry->notenameinternal = $fromform->notenameinternal;
    }
    $oerentry->notetextinternal = $fromform->notetextinternal['text'];
    $DB->update_record('oercollection_resource', $oerentry);
    redirect($origin->out(false));
}
$pageheading = get_string('editcomment', 'mod_oercollection');

$PAGE->set_title(get_string('annotation', 'mod_oercollection'));
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('annotation', 'mod_oercollection'));
$activityheader = $PAGE->activityheader;

echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($pageheading, '', '');
$mform->display();
echo $OUTPUT->footer();
