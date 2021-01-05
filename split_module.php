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
 * Upload video(s).
 *
 * @package    local_split_module
 * @copyright  2020 tovi@openapp.co.il
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once( __DIR__ . '/../../config.php');
require_once('locallib.php');

require_login();
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot."/course/lib.php");
require_once($CFG->dirroot."/question/editlib.php");
require_once($CFG->dirroot."/local/split_module/locallib.php");

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/modinfolib.php");
$PAGE->requires->css('/local/split_module/styles.css');
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/split_module/js/split_module.js'));
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('split', 'local_split_module'));
$PAGE->set_title(get_string('split', 'local_split_module'));
$PAGE->set_url('/local/split_module/split_module.php');
$PAGE->set_pagelayout('standard');

class split_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER, $COURSE, $settings, $PAGE;
        $cntpost = $this->_customdata["postcnt"];
        $cmid = required_param('cmid', PARAM_INT);
        $id = optional_param('quizid', 0, PARAM_INT);
        $addpart = optional_param('part', 0, PARAM_INT);
        $addpart = 0;
        $mform = $this->_form;
        $count = 3;

        $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
        $module = get_module_from_cmid($cm->id)[0];
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

        if (isset($module->timeopen)) { //quiz
            $timeopen = $module->timeopen;
            $timeclose = $module->timeclose;
            $url = '/mod/quiz/view.php?id=' . $cm->id;
        } else { // assign
            $timeopen = $module->allowsubmissionsfromdate;
            $timeclose = $module->duedate;
            $url = '/mod/assign/view.php?id=' . $cm->id;
        }

        $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php?id=' . $course->id));
        $PAGE->navbar->add($module->name, new moodle_url($url));
        $PAGE->navbar->add(get_string('split', 'local_split_module'));
        
        $time = $timeclose - $timeopen;
        $h = $time / 60 / 60;
        $m = $time / 60  % 60;
        $num =  $h . "." . $m;
        $totalhours =  number_format($num, 2, '.', '');
        $totalminutes = $time / 60;
        if ($timeopen == 0) {
            $timeopen = get_string('notset', 'local_split_module');
        } else {
            $timeopen = date('d/m/Y H:i', $timeopen);
        }
        if ($timeclose == 0) {
            $timeclose = get_string('notset', 'local_split_module');
        } else {
            $timeclose =date('d/m/Y H:i', $timeclose);
        }
        $a = new stdClass();
        
        $a->timeopen = $timeopen;
        $a->timeclose = $timeclose;
        $a->totalhours = $totalhours;
        $a->totalminutes = $totalminutes;

        $mform->addElement('html', '<div class="local_split_instructions">' . get_string('instructions', 'local_split_module', $a) . '</div>');

        $mform->addElement('text', 'part-1', get_string('parttime', 'local_split_module') . '1');
        $mform->setType('part-1', PARAM_RAW);
       
        $mform->addElement('text', 'break-2', get_string('breaktime', 'local_split_module'));
        $mform->setType('break-2', PARAM_RAW);
    
        $mform->addElement('text', 'part-2', get_string('parttime', 'local_split_module') . '2');
        $mform->setType('part-2', PARAM_RAW);
 
        for ($x = 3; $x < 9; $x++) {
            if ($x < $cntpost) {
                $class = "local_split_showpart";
            } else {
                $class = "local_split_hiddenpart";
            }  
            $mform->addElement('text', 'break-' . $x , get_string('breaktime', 'local_split_module'), ['class' => $class]);
            $mform->setType('break-' . $x, PARAM_RAW);
        
            $mform->addElement('text', 'part-'. $x, get_string('parttime', 'local_split_module') . $x, ['class' => $class]);
            $mform->setType('part-' . $x, PARAM_RAW);
        }   
        $mform->addElement('hidden', 'cmid', $cmid, ['id' => 'splitcmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'count', $count, ['id' => 'partcount']);
        $mform->setType('count', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'splitbutton', get_string('saveandsplit', 'local_split_module'));
        $buttonarray[] =& $mform->createElement('button', 'addpartbutton', get_string('addnewpart', 'local_split_module'));
        $buttonarray[] =& $mform->createElement('button', 'previewbutton', get_string('preview', 'local_split_module'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $count = $data["count"];
        for ($x = 1; $x < $count; $x++) {
            if ($x != 1) {
                $break = "break-" . $x;
                $breakvalue = $data[$break];
                if (strval($breakvalue) !== strval(intval($breakvalue))) {
                    $errors[$break] = get_string('minutesnotice', 'local_split_module');
                }
            }
            $part = 'part-' . $x;
            $partvalue = $data[$part];
            if (strval($partvalue) !== strval(intval($partvalue))) {
                $errors[$part] = get_string('minutesnotice', 'local_split_module');
            }
        }
        return $errors;
    }
}

$mform = new split_form(null, ["postcnt"=> 0]);
if ($mform->is_cancelled()) {
    $cmid = $_POST["cmid"];
    $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    redirect(new moodle_url('/course/view.php?id=' . $course->id));
    
} else if ($fromform = $mform->get_data()) {
    global $DB;
    $cmid = $fromform->cmid;
    $count = $fromform->count;
    if (!empty($cmid)) {
        $sum = 0;
        $sumparts = 0;
        $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
        $module = get_module_from_cmid($cm->id)[0];

        if (isset($module->timeopen)) { //quiz
            $timeopen = $module->timeopen;
            $timeclose = $module->timeclose;
            $timelimit = $module->timelimit;
        } else { // assign
            $timeopen = $module->allowsubmissionsfromdate;
            $timeclose = $module->duedate;
        }

        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $cmids = [];

        for ($x = 1; $x < $count; $x++) {
            // Duplicate the module.
            $newcm = duplicate_module($course, $cm);
            $cmobject = get_module_from_cmid($newcm->id)[0];
            $cmids[] = $newcm->id;
            $parttime = $_POST["part-" . $x];            
            if ($x != 1) {
                $breaktime = $_POST["break-" . $x];
                $sum += $breaktime;
            }
            //$name = str_replace(get_string('copy', 'local_split_module'), "", $cmobject->name);
            $name = str_replace('(copy)', "", $cmobject->name);
            $cmobject->name = $name . "-" . get_string('part', 'local_split_module') . $x . "";
            if (isset($module->timeopen)){ //quiz
                $cmobject->timeopen = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                $cmobject->timeclose = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                $cmobject->timelimit = $cmobject->timeclose - $cmobject->timeopen;
                $DB->update_record('quiz',  $cmobject);
            } else {
                $cmobject->allowsubmissionsfromdate = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                $cmobject->duedate = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                $cmobject->cutoffdate = $cmobject->duedate;
                $DB->update_record('assign',  $cmobject);
            }
            $sum += $parttime;

        // Insert data to DB - local_split_module table
        if ($x != 1) {
            $record = new \stdClass();
            $record->partid = null;
            $record->fatherid = $cm->id;
            $record->time = $_POST["break-" . $x];
            $DB->insert_record('local_split_module',  $record);
        }
        $record = new \stdClass();
        $record->partid = $newcm->id;
        $record->fatherid = $cm->id;
        $record->time = $_POST["part-" . $x];
        $DB->insert_record('local_split_module',  $record);
        }

        $section = $DB->get_record('course_sections', array('id' => $cm->section), '*', MUST_EXIST);
        $sequence = explode("," , $section->sequence);
        $pos = array_search(end($cmids), $sequence);            
        $a = array_slice($sequence, 0, $pos, true);
        $b = array_slice($sequence, $pos + count($cmids), count($sequence) - 1, true);
        $res = array_merge($a, $cmids);
        $res = array_merge($res, $b);

        $record = new \stdClass();
        $record->id = $section->id;
        $record->sequence = implode(",",$res);
        $DB->update_record("course_sections", $record, $bulk=false);

        // Declare father module not visisble to students
        $record = new \stdClass();
        $record->id = $cm->id;
        $record->visible = 0;
        $record->visibleold = 0;
        $DB->update_record("course_modules", $record, $bulk=false);

        if (isset($module->timeopen)) { //quiz
            $record = new \stdClass();
            $record->id = $module->id;
            $record->name = $module->name . "-" . get_string('dontdelete', 'local_split_module');
            $DB->update_record("quiz", $record, $bulk=false);

            $record = new \stdClass();
            $record->partid = $cm->id;
            $record->time = $module->timeclose - $module->timeopen;
            $record->oldtimeopen = $module->timeopen;
            $record->oldtimeclose = $module->timeclose;
            $DB->insert_record('local_split_module',  $record);	

        } else { // assign
            $record = new \stdClass();
            $record->id = $module->id;
            $record->name = $module->name . "-" . get_string('dontdelete', 'local_split_module');
            $DB->update_record("assign", $record, $bulk=false);
            
            $record = new \stdClass();
            $record->partid = $cmid;
            $record->time = $module->duedate - $module->allowsubmissionsfromdate;
            $record->oldtimeopen = $module->allowsubmissionsfromdate;
            $record->oldtimeclose = $module->duedate;
            $DB->insert_record('local_split_module',  $record);	
        }
        
        // update parts override
        add_parts_override($cmid); 
        rebuild_course_cache($course->id, $clearonly=false);
        $section = $DB->get_field("course_sections", "section", ["id" => $cm->section], $strictness=IGNORE_MISSING);
        redirect($CFG->wwwroot . '/course/view.php?id=' . $course->id . '#section-' . $section);
    }
} 
    // Displays the form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('declareexamparts', 'local_split_module'));
echo $mform->display();
$data = new stdClass();
$data->uniqid = "split_mypop";
$data->body = '
<div id="splitpreview" class="form-group"></div>';
$data->footer = "<button class='btn btn-secondary closebtn'>".get_string('close', 'local_split_module')."</button>";
$data->title = get_string('pluginname', 'local_split_module');
echo $OUTPUT->render_from_template('core/modal', $data);
echo $OUTPUT->footer();




