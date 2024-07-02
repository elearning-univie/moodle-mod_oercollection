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

// $id = required_param('id', PARAM_INT);
// [$course, $cm] = get_course_and_cm_from_cmid($id, 'oercollection');
// $instance = $DB->get_record('oercollection', ['id'=> $cm->instance], '*', MUST_EXIST);

global $PAGE, $OUTPUT, $DB, $CFG;

$cmid = required_param('id', PARAM_INT);
$filter = optional_param('oerefilter', 1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'oercollection');

$context = context_module::instance($cm->id);

if (!in_array($perpage, [5, 10, 20, 50, 100, 5000], true)) {
    $perpage = DEFAULT_PAGE_SIZE;
}

require_login($course, false, $cm);
require_capability('mod/oercollection:view', $context);

$oerid = $DB->get_record('oercollection', array('id' => $cm->instance));

$params = array();
$params['id'] = $cmid;
$params['perpage'] = $perpage;
if ($page) {
    $params['page'] = $page;
}

$homeurl = new moodle_url("/mod/oercollection/resources.php", $params);
$PAGE->set_url($homeurl->out(false));
//$PAGE->set_url(new moodle_url("/mod/oercollection/resources.php", ['id' => $cmid]));
$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oerid->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');


$sql = "SELECT *
          FROM {oercollection_resource} oer
         WHERE oer.oerid = $oerid->id";

$sqlshow = "";
if ($filter) {
    switch ($filter) {
        case 1:
            break;
        case 2: // only visible
            $sqlshow .= " AND oer.showresource = 1 ";
            break;
        case 3: // only hidden
            $sqlshow .= " AND oer.showresource = 0 ";
    }
}

$sqlcount = "SELECT COUNT(oer.id)
          FROM {oercollection_resource} oer
         WHERE oer.oerid = $oerid->id" .$sqlshow;

$totalnumberresources = $DB->count_records_sql($sqlcount);

//pagination
$paginationsql = "";
$offset = ($page)*$perpage;
if (($totalnumberresources/$perpage) > 1) {
    $paginationsql = " LIMIT $perpage OFFSET $offset";
}
$paginationsql = " LIMIT $perpage OFFSET $offset";

$sql .= $sqlshow;
$sql .= " ORDER BY oer.position ASC ";
$sql .= $paginationsql;

$oerentries = $DB->get_records_sql($sql);

$sql = "SELECT *
          FROM {oercollection_resource} oer
         WHERE oer.oerid = $oerid->id
      ORDER BY oer.position ASC ";
$resourcesmodal = $DB->get_records_sql($sql);
$sql = "SELECT COUNT(oer.id)
          FROM {oercollection_resource} oer
         WHERE oer.oerid = $oerid->id
      ORDER BY oer.position ASC ";
$rtotal = $DB->count_records_sql($sql);

$templatecontext = [];

if (has_capability('mod/oercollection:editresources', $context)) {
    $oerexists = $oerentries ? true : false;
    if ($oerexists) {
        $templatecontext['oernumber'] = $DB->count_records('oercollection_resource', ['oerid' => $oerid->id]);
    }
    $templatecontext['actionurl'] = $PAGE->url;
    $templatecontext['id'] = $cmid;
    if ($filter) {
        $selstring = 'selected2' . $filter;
    }
    $templatecontext['selected' . $perpage] = true;
    $templatecontext[$selstring] = true;
    $templatecontext['sesskey'] = sesskey();
    $templatecontext['oerid'] = $oerid->id;
    $templatecontext['oerexists'] = $oerexists;
    $templatecontext['searchoer'] = new moodle_url("/mod/oercollection/searchoer.php", ['id' => $cmid]);
    $templatecontext['studentpreviewlink'] = new moodle_url("/mod/oercollection/oercollectionstudentview.php", ['id' => $cmid]);
    if ($oerexists) {
        $templatecontext['linktext'] = 'bla';
    } else {
        $templatecontext['link'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $cmid]);
    }
}

//=========== Dummy template for table
// $oerhtml1 = '<div class="d-flex flex-column p-0 text-dark">  
//     <div class="p-1 flex-grow-1">
//     	<a href="https://books.disney.com/book/look-out-for-the-little-guy/"><b>Look Out for the Little Guy!</b></a>
//     </div>
//     <div class="d-flex p-0 text-dark"> 
//     <div class="p-1" style="width:20%"><img src="https://books.disney.com/content/uploads/2023/02/Look-Out-For-the-Little-Guy-280x419.jpg" alt="W3Schools.com" width="104" height="142" style="float:left"></div>
//     <div class="p-1 flex-grow-1">
//     	<p>In Look Out for the Little Guy, Scott Lang shares with the world a bracingly honest account of his struggles and triumphs, from serving time to being a divorced dad to becoming Ant-Man and joining The Avengers.</p>
//         <p style="">Author: Scott Lang <br>
//         Veröffentlicht: 5. September 2023
//         </p>
//     </div>
//     </div>
//   </div>';
// $oerhtml2 = '<div class="d-flex flex-column p-0 text-dark">  
//     <div class="p-1 flex-grow-1">
//     	<a href="https://www.imdb.com/title/tt21190556/"><b>You Can Call Me Bill</b></a>
//     </div>
//     <div class="d-flex p-0 text-dark"> 
//     <div class="p-1" style="width:20%"><img src="https://m.media-amazon.com/images/I/81fFryd7JdL._AC_UY218_.jpg" alt="W3Schools.com" width="104" height="142" style="float:left"></div>
//     <div class="p-1 flex-grow-1">
//     	<p>Wer kennt ihn nicht als Captain Kirk oder T.J. Hooker? Dies sind nur zwei der unvergesslichen Rollen, denen William Shatner im Laufe von sieben außergewöhnlichen Jahrzehnten auf der Bühne und vor der Kamera Leben eingehaucht hat. YOU CAN CALL ME BILL ist ein intimes Porträt von William Shatners persönlicher Reise durch neun Jahrzehnte.</p>
//         <p style="">Author: William Shatner <br>
//         Veröffentlicht: 16. März 2023
//         </p>
//     </div>
//     </div>
//   </div>';
$oerhtml = '<div>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.<br>
        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
      </div>';

$oerlist = [];

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
    $oerlist[] = array(
        'oerentryid' => $oerentry->id,
        'oerhtml' => $oerhtml,
        'oerhidden' => $oerhidden,
        'resourcelink' => $oerentry->resourcelink,
        'background' => $background,
        'commentexists' => $commentexists,
        'commentlink' => $commentlink->out(false),
        'commenttext' => $oerentry->notetextinternal,
        'commentname' => $oerentry->notenameinternal,
    );
}


// dummy data fuer modal test
$ll[] = ['id' => 1, 'name' => 'bla1', 'isorigin' => false];
$ll[] =  ['id' => 2, 'name' => 'bla2', 'isorigin' => true];
$ll[] = ['id' => 3, 'name' => 'bla3', 'isorigin' => false];
$page1['lines'] = $ll;
$page1['title'] = 'Page 1';
$page1['pnr'] = 1;
$page1['open'] = true;
unset($ll);
$ll[] = ['id' => 4, 'name' => 'bla4', 'isorigin' => false];
$ll[] = ['id' => 5, 'name' => 'bla5', 'isorigin' => false];
$ll[] = ['id' => 6, 'name' => 'bla6', 'isorigin' => false];
$page2['lines'] = $ll;
$page2['title'] = 'Page 2';
$page2['pnr'] = 2;
$page2['open'] = false;
unset($ll);
$ll[] = ['id' => 7, 'name' => 'bla7', 'isorigin' => false];
$ll[] = ['id' => 8, 'name' => 'bla8', 'isorigin' => false];
$page3['lines'] = $ll;
$page3['title'] = 'Page 3';
$page3['pnr'] = 3;
$page3['open'] = false;
$rr2[] = $page1;
$rr2[] = $page2;
$rr2[] = $page3;

// $templatecontext['page'] = $rr2;

//Modal data loop
$resourcestemp = [];
foreach ($resourcesmodal as $remod) {
    $resourcestemp[] = ['id' => $remod->id, 'name' => $remod->resourcename];
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
    $pg['pnr'] = 1;
    $pg['open'] = true;
    $rr[] = $pg;
    unset($ll);
    unset($pg);
    $pagenr++;
    $i+=$perpage;
}
// while ($i < $rtotal) {
//     for ($x = $i; $x < ($i+$perpage); $x++) {
//         $ll[] = ['id' => $resourcestotal->id, 'name' => $resourcestotal->resourcename];
//     }
//     $page['lines'] = $ll;
//     $page['title'] = 'Page ' . $pagenr;
//     $page['pnr'] = 1;
//     $page['open'] = true;
//     $rr[] = $page;
//     unset($ll);
//     unset($page);
//     $pagenr++;
//     $i+=$perpage;
// }
$templatecontext['page'] = $rr;
//Modal data loop end

$templatecontext['oerresourcelist'] = $oerlist;
//=====================

$PAGE->requires->js_call_amd('mod_oercollection/resourcecontroller', 'init');

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
//print_object($resourcestemp);
echo $renderer->render_from_template('mod_oercollection/resources', $templatecontext);
//echo $renderer->render_from_template('mod_oercollection/moveresourcemodal', $templatecontext);
echo $OUTPUT->paging_bar($totalnumberresources, $page, $perpage, $homeurl);
echo $renderer->footer();
