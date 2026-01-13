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
require_once('locallib.php');

global $PAGE, $OUTPUT, $DB, $CFG;

$cmid = required_param('id', PARAM_INT);
$filter = optional_param('oerefilter', 1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
$deleted = optional_param('del', 0, PARAM_INT);

$validperpages = [5, 10, 20, 50, 100, 5000];
if (!in_array($perpage, $validperpages, true)) {
    $perpage = DEFAULT_PAGE_SIZE;
}

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'oercollection');
require_login($course, false, $cm);

$context = context_module::instance($cm->id);
if (!has_capability('mod/oercollection:editresources', $context)) {
    $url = new moodle_url("/mod/oercollection/studentview.php", ['id' => $cmid]);
    redirect($url);
}

$oerid = $DB->get_record('oercollection', ['id' => $cm->instance]);

$params = [
    'id' => $cmid,
    'del' => $deleted,
    'perpage' => $perpage,
];
if ($page) {
    $params['page'] = $page;
}

$homeurl = new moodle_url("/mod/oercollection/resources.php", $params);
$PAGE->set_url($homeurl);
$PAGE->set_title($oerid->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$paramsql = ['oerid' => $oerid->id];
switch ($filter) {
    case 2: // Only visible.
        $sqlshow = " AND showresource = 1 ";
        break;
    case 3: // Only hidden.
        $sqlshow = " AND showresource = 0 ";
        break;
    default:
        $sqlshow = "";
}

$sql = "SELECT *
          FROM {oercollection_resource}
         WHERE oerid = :oerid $sqlshow
      ORDER BY position ASC";

$oerentries = $DB->get_records_sql($sql, $paramsql, $page * $perpage, $perpage);
$totalentries = $DB->count_records('oercollection_resource', ['oerid' => $oerid->id]);
$filteredcount = $DB->count_records_select('oercollection_resource', "oerid = :oerid $sqlshow", $paramsql);

// Prepare template context
$templatecontext = [
    'oernumber' => $totalentries,
    'oernumberhidden' => $DB->count_records('oercollection_resource', ['oerid' => $oerid->id, 'showresource' => 0]),
    'actionurl' => $PAGE->url,
    'id' => $cmid,
    'deleted' => $deleted,
    'selected' . $perpage => true,
    'selected2' . $filter => true,
    'sesskey' => sesskey(),
    'oerid' => $oerid->id,
    'oerexists' => !empty($oerentries),
    'searchoer' => new moodle_url("/mod/oercollection/searchoer.php", ['id' => $cmid]),
    'studentpreviewlink' => new moodle_url("/mod/oercollection/studentview.php", ['id' => $cmid]),
];

// Prepare OER entries with caching
$oerlist = [];
$oerapi = new \oerapi_oerhub\api\general($PAGE->url, $oerid->id);
$apicache = cache::make('mod_oercollection', 'entries');
$resourceids = array_column($oerentries, 'oerresourceid');

$apiavailable = $oerapi->is_api_available();

if (!$apiavailable && !empty($resourceids)) {
    $templatecontext['apiwarning'] = get_string('resourceunavailable', 'oerapi_oerhub');
} else if ($apiavailable) {
    $cachedresources = $apicache->get_many($resourceids);
    foreach ($oerentries as $oerentry) {
        if (!isset($cachedresources[$oerentry->oerresourceid]) || $cachedresources[$oerentry->oerresourceid] === false) {
            $oerhtml = $oerapi->get_resource_html($oerentry->oerresourceid);
            if ($oerhtml !== null) {
                $apicache->set($oerentry->oerresourceid, $oerhtml);
            }
        } else {
            $oerhtml = $cachedresources[$oerentry->oerresourceid];
        }

        $commentlink = new moodle_url("/mod/oercollection/oercomment.php", [
            'id' => $cmid,
            'oereid' => $oerentry->id,
        ]);

        $oerlist[] = [
            'oerentryid' => $oerentry->id,
            'oerhtml' => $oerhtml,
            'resourceloadfailed' => empty($oerhtml),
            'oerhidden' => !$oerentry->showresource,
            'resourcelink' => $oerentry->resourcelink,
            'resourcename' => s($oerentry->resourcename),
            'background' => $oerentry->showresource ? '' : 'bg-light',
            'commentexists' => !empty($oerentry->notetextinternal),
            'commentlink' => $commentlink->out(false),
            'commenttext' => format_text($oerentry->notetextinternal),
            'commentname' => s($oerentry->notenameinternal),
        ];
    }
}

// Prepare modal data with array_chunk
$allresources = $DB->get_records('oercollection_resource', ['oerid' => $oerid->id], 'position ASC');
$chunkedresources = array_chunk($allresources, $perpage);
$pagedresources = [];
foreach ($chunkedresources as $index => $chunk) {
    $pagedresources[] = [
        'lines' => array_map(function($resource) {
            return [
                'id' => $resource->id,
                'name' => s($resource->resourcename),
                'hidden' => !$resource->showresource,
            ];
        }, $chunk),
        'title' => get_string('page') . ' ' . ($index + 1),
        'pnr' => $index + 1,
        'open' => ($index === $page),
    ];
}

$templatecontext['oerresourcelist'] = $oerlist;
$templatecontext['resourcecount'] = count($oerlist);
$templatecontext['page'] = $pagedresources;

$PAGE->requires->js_call_amd('mod_oercollection/resourcecontroller', 'init');

// Output rendering
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_oercollection/resources', $templatecontext);
echo $OUTPUT->paging_bar($filteredcount, $page, $perpage, $homeurl);
if ($totalentries) {
    echo $OUTPUT->render_from_template('mod_oercollection/resourcesactionsandoptions', $templatecontext);
}
echo $OUTPUT->footer();
