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
 * Comment Form
 *
 * @package   mod_oercollection
 * @author    Adrian Czermak
 * @author    Angela Baier
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oercollection\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Flashcard form definition with less information.
 *
 * @copyright  2021 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class oercommentform extends \moodleform {

    /** @var object current context */
    public $context;
    /** @var array html editor options */
    public $editoroptions;
    /** @var object instance of question type */
    public $instance;
    /** @var int oer entry id */
    public $oerentryid;
    /** @var int oer activity id */
    public $oerid;
    /** @var int cmid */
    public $id;
    /**
     * simplequestionform constructor.
     *
     * @param string $submiturl
     * @param object $question
     * @param string $category
     * @param string $action
     * @param bool $formeditable
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct($submiturl, $oerentryid, $oerid, $cmid, $formeditable = true) {
        global $DB;

        $this->oerentryid = $oerentryid;
        $this->oerid = $oerid;
        $this->id = $cmid;

        $this->editoroptions = array('subdirs' => 1, 'maxfiles' => EDITOR_UNLIMITED_FILES,
            'context' => $this->context);

        $this->context = \context::instance_by_id($this->oerid);

        parent::__construct($submiturl, null, 'post', '', null, $formeditable);
    }

    /**
     * form definition
     *
     * @throws coding_exception
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;

        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        $mform->addElement('text', 'notenameinternal', get_string('oercommentname', 'mod_oercollection'),
                array('size' => 50, 'maxlength' => 255));
        $mform->setType('notenameinternal', PARAM_TEXT);

        $mform->addElement('editor', 'notetextinternal', get_string('oercommentdescription', 'mod_oercollection'),
                array('rows' => 15), $this->editoroptions);
        $mform->setType('notetextinternal', PARAM_RAW);

        $this->add_hidden_fields();
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * form validation
     *
     * @param array $fromform
     * @param array $files
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);

        return $errors;
    }

    /**
     * Add all the hidden form fields used by question/question.php
     */
    protected function add_hidden_fields() {
        $mform = $this->_form;
        
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'oereid', $this->oerentryid);
        $mform->setType('oereid', PARAM_INT);
        
    }
}
