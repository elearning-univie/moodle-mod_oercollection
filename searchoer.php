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
$searchstring = optional_param('searchstring', null, PARAM_TEXT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'oercollection');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/oercollection:view', $context);

$wordcloud = $DB->get_record('oercollection', array('id' => $cm->instance));

$PAGE->set_url(new moodle_url("/mod/oercollection/searchoer.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_oercollection', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'oercollection');
$PAGE->set_title($wordcloud->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');

$templatecontext = [];
$oerexists = false;

//if ($isteacher) {
$overviewlink = new moodle_url('/mod/oercollection/resources.php', ['id' => $id]);
//$templatestablecontext['isteacher'] = 1;
$templatecontext['resourceslink'] = $overviewlink->out(false);
//}

if (has_capability('mod/oercollection:editresources', $context)) {
    $templatecontext['searchoer'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $id]);
    $templatecontext['studentpreviewlink'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $id]);
    if ($oerexists) {
        $templatecontext['linktext'] = 'bla';
    } else {
        $templatecontext['link'] = new moodle_url("/mod/oercollection/resources.php", ['id' => $id]);
    }
}

$PAGE->requires->js_call_amd('mod_oercollection/searchcontroller', 'init');

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();
echo $renderer->render_from_template('mod_oercollection/searchoerbuttons', $templatecontext);

$searchform = new \oerapi_oerhub\api\general($PAGE->url);
echo $searchform->get_search_form();

if (!is_null($searchstring)) {
    $oersearchresults = [];
    $results = $searchform->get_results($searchstring);

    if (count($results) != 0) {
        foreach ($results as $result) {
            $oersearchresults[] = array('oerhubid' => 111, 'oerhtml' => $result, 'oerresourcelink' => 'bla');
        }
        $templatecontext['oersearchresultlist'] = $oersearchresults;
        echo $renderer->render_from_template('mod_oercollection/searchoer', $templatecontext);
    } else {
        echo "nothing found";
    }
}

echo $renderer->footer();
