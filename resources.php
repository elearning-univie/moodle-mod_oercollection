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

global $PAGE, $OUTPUT, $DB, $CFG;

$cmid = required_param('id', PARAM_INT);
$filter = optional_param('oerefilter', 1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
$deleted = optional_param('del', 0, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'oercollection');

$context = context_module::instance($cm->id);

if (!in_array($perpage, [5, 10, 20, 50, 100, 5000], true)) {
    $perpage = DEFAULT_PAGE_SIZE;
}

require_login($course, false, $cm);
//require_capability('mod/oercollection:editresources', $context);
if (!has_capability('mod/oercollection:editresources', $context)) {
    $url = new moodle_url("/mod/oercollection/oercollectionstudentview.php", ['id' => $cmid]);
    redirect($url);
}

$oerid = $DB->get_record('oercollection', array('id' => $cm->instance));

$params = [
    'id' => $cmid,
    'del' => $deleted,
    'perpage' => $perpage,
];
if ($page) {
    $params['page'] = $page;
}

$homeurl = new moodle_url("/mod/oercollection/resources.php", $params);
$PAGE->set_url($homeurl->out(false));
$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oerid->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

switch ($filter) {
    case 2: // Only visible.
        $sqlshow = " AND oer.showresource = 1 ";
        break;
    case 3: // Only hidden.
        $sqlshow = " AND oer.showresource = 0 ";
        break;
    default:
        $sqlshow = "";
}

//pagination
$offset = ($page * $perpage);

$sql = "SELECT *
          FROM {oercollection_resource} oer
         WHERE oer.oerid = $oerid->id" . $sqlshow . " ORDER BY oer.position ASC";

$filteredentries = $DB->get_records_sql($sql);
$oerentries = array_slice($filteredentries, $offset, $perpage);

$sql = "SELECT *
          FROM {oercollection_resource} oer
         WHERE oer.oerid = $oerid->id
      ORDER BY oer.position ASC ";
$resourcesmodal = $DB->get_records_sql($sql);
$rtotal = count($resourcesmodal);

$selstring = 'selected2' . $filter;
$oerexists = count($oerentries) ? true : false;

$templatecontext = [
    'oernumber' => $rtotal,
    'oernumberhidden' => $DB->count_records('oercollection_resource', ['oerid' => $oerid->id, 'showresource' => 0]),
    'actionurl' => $PAGE->url,
    'id' => $cmid,
    'deleted' => $deleted,
    'selected' . $perpage => true,
    $selstring => true,
    'sesskey' => sesskey(),
    'oerid' => $oerid->id,
    'oerexists' => $oerexists,
    'searchoer' => new moodle_url("/mod/oercollection/searchoer.php", ['id' => $cmid]),
    'studentpreviewlink' => new moodle_url("/mod/oercollection/oercollectionstudentview.php", ['id' => $cmid])
];

$oerlist = [];

$oerapi = new \oerapi_oerhub\api\general($PAGE->url, $oerid->id);

foreach ($oerentries as $oerentry) {
    $oerhidden = true;
    $background = 'bg-light';
    if($oerentry->showresource) {
        $oerhidden = false;
        $background = '';
    }
    $commentexists = true;
    if (is_null($oerentry->notetextinternal) || empty($oerentry->notetextinternal)) {
        $commentexists = false;
    }
    $commentlink = new moodle_url("/mod/oercollection/oercomment.php", ['id' => $cmid, 'oereid' => $oerentry->id]);
    $oerlist[] = [
        'oerentryid' => $oerentry->id,
        'oerhtml' => $oerapi->get_resource_html($oerentry->oerresourceid),
        'oerhidden' => $oerhidden,
        'resourcelink' => $oerentry->resourcelink,
        'resourcename' => "'" . $oerentry->resourcename . "'",
        'background' => $background,
        'commentexists' => $commentexists,
        'commentlink' => $commentlink->out(false),
        'commenttext' => $oerentry->notetextinternal,
        'commentname' => $oerentry->notenameinternal,
    ];
}

//Modal data loop
$resourcestemp = [];
foreach ($resourcesmodal as $remod) {
    $oerhidden = true;
    if($remod->showresource) {
        $oerhidden = false;
    }
    $resourcestemp[] = ['id' => $remod->id, 'name' => $remod->resourcename, 'hidden' => $oerhidden];
}

$i=0;
$pagenr = 1;
$ll = [];
$pg = [];
$rr = [];
while ($i < $rtotal) {
    for ($x = $i; $x < ($i+$perpage); $x++) {
        if (array_key_exists($x,$resourcestemp)) {
            $ll[] = $resourcestemp[$x];
        }
    }
    $pg['lines'] = $ll;
    $pg['title'] = 'Page ' . $pagenr;
    $pg['pnr'] = $pagenr;
    $pg['open'] = true;
    $rr[] = $pg;
    unset($ll);
    unset($pg);
    $pagenr++;
    $i+=$perpage;
}

$templatecontext['page'] = $rr;
//Modal data loop end

$templatecontext['oerresourcelist'] = $oerlist;

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();

echo $renderer->render_from_template('mod_oercollection/resources', $templatecontext);
echo $OUTPUT->paging_bar(count($filteredentries), $page, $perpage, $homeurl);
echo $renderer->render_from_template('mod_oercollection/resourcesactionsandoptions', $templatecontext);
echo $renderer->footer();
