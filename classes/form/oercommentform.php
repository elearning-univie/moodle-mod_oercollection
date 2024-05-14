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

//         $record = $DB->get_record('question_categories',
//                     array('id' => $question->questioncategoryid), 'contextid');
        
        $this->context = \context::instance_by_id($this->oerid);
        echo "UUUUUUUUUUUUUUUUUUUUUUU";

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
        $mform->addRule('notenameinternal', null, 'required', null, 'client');

        $mform->addElement('editor', 'notetextinternal', get_string('oercommentdescription', 'mod_oercollection'),
                array('rows' => 15), $this->editoroptions);
        $mform->setType('notetextinternal', PARAM_RAW);
        $mform->addRule('notetextinternal', null, 'required', null, 'client');

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

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'oereid');
        $mform->setType('oereid', PARAM_INT);
        
    }

    /**
     * Transforms data from the form into the question db form
     * @param array|\stdClass $question
     * @throws \coding_exception
     */
    public function set_data($question) {
        \question_bank::get_qtype($question->qtype)->set_default_options($question);

        // Prepare question text.
        $draftid = file_get_submitted_draft_itemid('questiontext');

        if (!empty($question->questiontext)) {
            $questiontext = $question->questiontext;
        } else {
            $questiontext = $this->_form->getElement('questiontext')->getValue();
            $questiontext = $questiontext['text'];
        }
        $questiontext = file_prepare_draft_area($draftid, $this->context->id,
                'question', 'questiontext', empty($question->id) ? null : (int) $question->id,
                $this->fileoptions, $questiontext);

        $question->questiontext = array();
        $question->questiontext['text'] = $questiontext;
        $question->questiontext['format'] = empty($question->questiontextformat) ?
                editors_get_preferred_format() : $question->questiontextformat;
        $question->questiontext['itemid'] = $draftid;

        $question = $this->data_preprocessing_answers($question, true);
        parent::set_data($question);
    }

    /**
     * Perform the necessary preprocessing for the fields added by
     * {@see add_per_answer_fields()}.
     * @param object $question the data being passed to the form.
     * @param boolean $withanswerfiles
     * @return object $question the modified data.
     */
    protected function data_preprocessing_answers($question, $withanswerfiles = false) {
        if (empty($question->options->answers)) {
            return $question;
        }

        $key = 0;
        foreach ($question->options->answers as $answer) {
            if ($withanswerfiles) {
                // Prepare the feedback editor to display files in draft area.
                $draftitemid = file_get_submitted_draft_itemid('answer['.$key.']');
                $question->answer['text'] = file_prepare_draft_area(
                        $draftitemid,
                        $this->context->id,
                        'question',
                        'answer',
                        !empty($answer->id) ? (int) $answer->id : null,
                        $this->fileoptions,
                        $answer->answer
                );
                $question->answer['itemid'] = $draftitemid;
                $question->answer['format'] = $answer->answerformat;
            } else {
                $question->answer[$key] = $answer->answer;
            }

            $key++;
        }

        return $question;
    }
}
