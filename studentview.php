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

global $PAGE, $OUTPUT, $DB;

$id = required_param('id', PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
$pg = optional_param('page', 0, PARAM_INT);
$filter = optional_param('oerefilter', 1, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');

$context = context_module::instance($cm->id);

if (!in_array($perpage, [5, 10, 20, 50, 100, 5000], true)) {
    $perpage = DEFAULT_PAGE_SIZE;
}

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

$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$PAGE->set_title($oercollection->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

// Pagination.
$paginationsql = "";
$offset = ($pg) * $perpage;
$totalnumberresources = $DB->count_records('oercollection_resource', ['oerid' => $oercollection->id, 'showresource' => 1]);
if (($totalnumberresources / $perpage) > 1) {
    $paginationsql = " LIMIT $perpage OFFSET $offset";
}
$paginationsql = "LIMIT $perpage OFFSET $offset";
$sql = 'SELECT * FROM {oercollection_resource} oerr WHERE oerr.oerid = :oerid AND oerr.showresource = 1 ';
$sql .= " ORDER BY oerr.position ASC ";
$sql .= $paginationsql;
$oerentries = $DB->get_records_sql($sql, ['oerid' => $oercollection->id]);

$templatecontext = [];

if (has_capability('mod/oercollection:editresources', $context)) {
    $oerexists = $oerentries ? true : false;
    if ($oerexists) {
        $templatecontext['oernumber'] = $totalnumberresources;
    }
    $templatecontext['oerid'] = $oercollection->id;
    $templatecontext['oerexists'] = $oerexists;
    if ($oerexists) {
        $templatecontext['linktext'] = 'bla';
    } else {
        $templatecontext['link'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $cm->id]);
    }
}

// get oer entries

$oerlist = [];
$oerapi = new \oerapi_oerhub\api\general($PAGE->url, $oercollection->id);

$apiavailable = $oerapi->is_api_available();

if (!$apiavailable && !empty($oerentries)) {
    $templatecontext['apiwarning'] = get_string('resourceunavailable', 'oerapi_oerhub');
} else if ($apiavailable) {
    foreach ($oerentries as $oerentry) {
        $commentexists = true;
        if (is_null($oerentry->notetextinternal) || empty($oerentry->notetextinternal)) {
            $commentexists = false;
        }

        $oerhtml = $oerapi->get_resource_html($oerentry->oerresourceid);

        $oerlist[] = [
            'oerentryid' => $oerentry->id,
            'oerhtml' => $oerhtml,
            'resourceloadfailed' => empty($oerhtml),
            'commentexists' => $commentexists,
            'commenttext' => $oerentry->notetextinternal,
            'commentname' => $oerentry->notenameinternal,
        ];
    }
}

$templatecontext['oerresourcelist'] = $oerlist;

$backtoteacherview = has_capability('mod/oercollection:editresources', $context);

$templatecontext['backtoteacherview'] = $backtoteacherview;
$templatecontext['backtoteacherviewlink'] = new moodle_url("/mod/oercollection/teacherview.php", ['id' => $id]);
$templatecontext['selected' . $perpage] = true;
$templatecontext['actionurl'] = $PAGE->url;
$templatecontext['sesskey'] = sesskey();
$templatecontext['id'] = $id;

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
echo $renderer->render_from_template('mod_oercollection/studentresources', $templatecontext);
echo $OUTPUT->paging_bar($totalnumberresources, $pg, $perpage, $homeurl);
echo $renderer->footer();
