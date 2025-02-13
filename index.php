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

require_once('../../config.php');

global $DB, $PAGE, $OUTPUT;

// The `id` parameter is the course id.
$id = required_param('id', PARAM_INT);

// Fetch the requested course.
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$coursecontext = context_course::instance($course->id);

// Require that the user is logged into the course.
require_course_login($course);

$event = \mod_oercollection\event\course_module_instance_list_viewed::create([
    'context' => $coursecontext,
]);
$event->trigger();

$PAGE->set_url('/mod/oercollection/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

$stroercollections = get_string("modulenameplural", "oercollection");
if (!$oercollections = get_all_instances_in_course('oercollection', $course)) {
    notice(get_string('thereareno', 'moodle', $stroercollections), new moodle_url('/course/view.php', ['id' => $course->id]));
}

$table = new html_table();
if ($course->format == 'weeks') {
    $table->head  = [get_string('week'), get_string('name')];
    $table->align = ['center', 'left'];
} else if ($course->format == 'topics') {
    $table->head  = [get_string('topic'), get_string('name')];
    $table->align = ['center', 'left', 'left', 'left'];
} else {
    $table->head  = [get_string('name')];
    $table->align = ['left', 'left', 'left'];
}

foreach ($oercollections as $oercollection) {
    if (!$oercollection->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/oercollection/view.php', ['id' => $oercollection->coursemodule]),
            format_string($oercollection->name, true),
            ['class' => 'dimmed']);
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/oercollection/view.php', ['id' => $oercollection->coursemodule]),
            format_string($oercollection->name, true));
    }

    if ($course->format == 'weeks' || $course->format == 'topics') {
        $table->data[] = [$oercollection->section, $link];
    } else {
        $table->data[] = [$link];
    }
}

echo $OUTPUT->heading($stroercollections, 2);
echo html_writer::table($table);
echo $OUTPUT->footer();
