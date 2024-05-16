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

$cmid = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'oercollection');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/oercollection:view', $context);

$oerid = $DB->get_record('oercollection', array('id' => $cm->instance));

$PAGE->set_url(new moodle_url("/mod/oercollection/resources.php", ['id' => $cmid]));
$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($oerid->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

$oerentries = $DB->get_records('oercollection_resource', ['oerid' => $oerid->id]); //, "postition ASC"

$templatecontext = [];

if (has_capability('mod/oercollection:editresources', $context)) {
    $oerexists = $oerentries ? true : false;
    if ($oerexists) {
        $templatecontext['oernumber'] = $DB->count_records('oercollection_resource', ['oerid' => $oerid->id]);
    }
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
$oerhtml1 = '<div class="d-flex flex-column p-0 text-dark">  
    <div class="p-1 flex-grow-1">
    	<a href="https://books.disney.com/book/look-out-for-the-little-guy/"><b>Look Out for the Little Guy!</b></a>
    </div>
    <div class="d-flex p-0 text-dark"> 
    <div class="p-1" style="width:20%"><img src="https://books.disney.com/content/uploads/2023/02/Look-Out-For-the-Little-Guy-280x419.jpg" alt="W3Schools.com" width="104" height="142" style="float:left"></div>
    <div class="p-1 flex-grow-1">
    	<p>In Look Out for the Little Guy, Scott Lang shares with the world a bracingly honest account of his struggles and triumphs, from serving time to being a divorced dad to becoming Ant-Man and joining The Avengers.</p>
        <p style="">Author: Scott Lang <br>
        Veröffentlicht: 5. September 2023
        </p>
    </div>
    </div>
  </div>';
$oerhtml2 = '<div class="d-flex flex-column p-0 text-dark">  
    <div class="p-1 flex-grow-1">
    	<a href="https://www.imdb.com/title/tt21190556/"><b>You Can Call Me Bill</b></a>
    </div>
    <div class="d-flex p-0 text-dark"> 
    <div class="p-1" style="width:20%"><img src="https://m.media-amazon.com/images/I/81fFryd7JdL._AC_UY218_.jpg" alt="W3Schools.com" width="104" height="142" style="float:left"></div>
    <div class="p-1 flex-grow-1">
    	<p>Wer kennt ihn nicht als Captain Kirk oder T.J. Hooker? Dies sind nur zwei der unvergesslichen Rollen, denen William Shatner im Laufe von sieben außergewöhnlichen Jahrzehnten auf der Bühne und vor der Kamera Leben eingehaucht hat. YOU CAN CALL ME BILL ist ein intimes Porträt von William Shatners persönlicher Reise durch neun Jahrzehnte.</p>
        <p style="">Author: William Shatner <br>
        Veröffentlicht: 16. März 2023
        </p>
    </div>
    </div>
  </div>';
$oerhtml = '<div>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.<br>
        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
      </div>';


// get oer entries


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
        'background' => $background,
        'commentexists' => $commentexists,
        'commentlink' => $commentlink->out(false),
        'commenttext' => $oerentry->notetextinternal,
        'commentname' => $oerentry->notenameinternal,
    );
}


// Testdata 1
$oerhidden = false;
$oerid = 1;
$oerentryid = 11;
$commentexists = true;
$commentlink = new moodle_url("/mod/oercollection/oercomment.php", ['id' => $cmid, 'oereid' => $oerentryid]);
$oerlist[] = array('oerid' => '11', 'oerhtml' => $oerhtml1, 'oerhidden' => $oerhidden, 'background' => '', 'commentexists' => $commentexists, 'commentlink' => $commentlink->out(false));
// Testdata 2
$oerhidden = true;
$oerentryid = 12;
$commentlink = new moodle_url("/mod/oercollection/oercomment.php", ['id' => $cmid, 'oereid' => $oerentryid]);
$commentexists = true;
$oerlist[] = array('oerid' => '11', 'oerhtml' => $oerhtml2, 'oerhidden' => $oerhidden, 'background' => 'bg-light', 'commentexists' => $commentexists, 'commentlink' => $commentlink->out(false));
// Testdata 3
$oerhidden=true;
$oerentryid = 13;
$commentexists = false;
$commentlink = new moodle_url("/mod/oercollection/oercomment.php", ['id' => $cmid, 'oereid' => $oerentryid]);
$oerlist[] = array('oerid' => '22', 'oerhtml' => $oerhtml, 'oerhidden' => $oerhidden, 'background' => 'bg-light', 'commentexists' => $commentexists, 'commentlink' => $commentlink->out(false));
//$templatetable = [];
$templatecontext['oerresourcelist'] = $oerlist;
//=====================

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();

echo $renderer->render_from_template('mod_oercollection/resources', $templatecontext);
echo $renderer->footer();
