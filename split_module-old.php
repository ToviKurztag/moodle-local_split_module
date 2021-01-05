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
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/modinfolib.php");
$PAGE->requires->css('/local/split_module/styles.css');
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/split_module/js/split_module.js'));
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('split', 'local_split_module'));
$PAGE->set_title(get_string('split', 'local_split_module'));
$PAGE->set_url('/local/split_module/split_module.php');
$PAGE->set_pagelayout('standard');
//$settings = get_settings();
    // Check if user have permissions.
    $context = context_system::instance();
    if (!has_capability('local/video_directory:video', $context) && !is_video_admin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }
/*
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('upload', 'local_video_directory'));
$PAGE->set_title(get_string('upload', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/upload.php');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('upload', 'local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');
$PAGE->requires->css('/local/video_directory/styles/select2.min.css');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js_amd_inline('require([\'jquery\',\'local_video_directory/edit\'])');
*/
//$PAGE->requires->js_init_call('addpart', array($CFG->wwwroot, 'hhh'));

$context = context_user::instance($USER->id);

class split_form extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG, $DB, $USER, $COURSE, $settings, $context;
        $cntpost = $this->_customdata["postcnt"];
        $cmid = required_param('cmid', PARAM_INT);
        $id = optional_param('quizid', 0, PARAM_INT);
        $addpart = optional_param('part', 0, PARAM_INT);
        $addpart = 0;
        $mform = $this->_form;
        $count = 3;

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
        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'count', $count, ['id' => 'partcount']);
        $mform->setType('count', PARAM_INT);

        //$mform->addElement('hidden', 'flag', 0, ['id' => 'previewflag']);
        //$mform->setType('flag', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'splitbutton', get_string('saveandsplit', 'local_split_module'));
        $buttonarray[] =& $mform->createElement('button', 'addpartbutton', get_string('addnewpart', 'local_split_module'));
        $buttonarray[] =& $mform->createElement('button', 'previewbutton', get_string('preview', 'local_split_module'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
    // Custom validation should be added here.
    public function validation($data, $files) {
        return array();
    }
}

$mform = new split_form(null, ["postcnt"=> 0]);
$html = "";
$preview = "";
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/split_module/split_module.php', ["cmid"=> $_POST["cmid"]]));
} else if ($fromform = $mform->get_data()) {
    global $DB;
    if (isset($_POST["flag"])) {
        $cmid = $fromform->cmid;
        $count = $fromform->count;
        $preview = "";
        if (!empty($cmid)) {
            $sum = 0;
            $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
            $module = get_module_from_cmid($cm->id)[0];
            
            if (isset($module->timeopen)) { //quiz
                $timeopen = $module->timeopen;
                $timeclose = $module->timeclose;
            } else { // assign
                $timeopen = $module->allowsubmissionsfromdate;
                $timeclose = $module->duedate;
            }
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            for ($x = 1; $x < $count; $x++) {
                $parttime = $_POST["part-" . $x];            
                if ($x != 1) {
                    $breaktime = $_POST["break-" . $x];
                    $preview .= get_string('breaktime', 'local_split_module') . ": " . "<br/>";
                    $from = date('H:i', strtotime(' +' . $sum . ' minutes' ,$timeopen));
                    $to = date('H:i', strtotime(' +' . ($sum + $breaktime) . ' minutes' ,$timeopen));
                    $preview .= get_string('fromtime', 'local_split_module') . $from . "<br/>";
                    $preview .= get_string('totime', 'local_split_module') . $to . "<br/>";
                    $sum += $breaktime;
                }
                if(isset($module->timeopen)){ //quiz
                    $preview .= get_string('parttime', 'local_split_module') . $x . " : " . "<br/>";
                    $from = date('H:i', strtotime(' +' . $sum . ' minutes' ,$timeopen));
                    $to = date('H:i', strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen));
                    $preview .= get_string('fromtime', 'local_split_module') . $from . "<br/>";
                    $preview .= get_string('totime', 'local_split_module') . $to . "<br/>";
                } else {
                    $preview .= get_string('parttime', 'local_split_module') . $x . " : " . "<br/>";
                    $from = date('H:i', strtotime(' +' . $sum . ' minutes' ,$timeopen));
                    $to = date('H:i', strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen));
                    $preview .= get_string('fromtime', 'local_split_module') . $from . "<br/>";
                    $preview .= get_string('totime', 'local_split_module') . $to . "<br/>";
                }
                $sum += $parttime;
            }
       }
       $html .= "<div>" . $preview . "</div>";
       $mform = new split_form(null, ["postcnt"=> $count]);

    } else {  // Saveandsplit
        $cmid = $fromform->cmid;
        $count = $fromform->count;
        if (!empty($cmid)) {
            $sum = 0;
            $sumparts = 0;
            $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
            $module = get_module_from_cmid($cm->id)[0];
           //print_r($module);
            if (isset($module->timeopen)) { //quiz
                $timeopen = $module->timeopen;
                $timeclose = $module->timeclose;
            } else { // assign
                $timeopen = $module->allowsubmissionsfromdate;
                $timeclose = $module->duedate;
            }
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
                        
            for ($x = 1; $x < $count; $x++) {
               
                $parttime = $_POST["part-" . $x];            
                if ($x != 1) {
                    $breaktime = $_POST["break-" . $x];
                    $sumparts += $breaktime;
                }
                $sumparts += $parttime;
            }
            $res = strtotime(' +' . $sumparts . ' minutes' ,$timeopen);
            // print_r("timeopen:  " . $timeopen ."       ");
            //print_r("res:  " . $res . "         ");
            //print_r("time:  " . $timeclose . "         ");die;
            if ($timeclose != $res) {
                $message = get_string('notequaltiming', 'local_split_module');
                $notification = $OUTPUT->notification($message, 'error');
                $mform = new split_form(null, ["postcnt"=> $count]);
                //echo $OUTPUT->header();
                //echo $mform->display();
                //echo $html;
                //echo $notification;
                //echo $OUTPUT->footer();die;
            }
            $cmids = [];

            for ($x = 1; $x < $count; $x++) {
                // Duplicate the module.
                $newcm = duplicate_module($course, $cm);
                $cmobject = get_module_from_cmid($newcm->id)[0];
                $cmids[] = $newcm->id;
                $preview .= $_POST["part-" . $x];
                $parttime = $_POST["part-" . $x];            
                if ($x != 1) {
                    $preview .= $_POST["break-" . $x];
                    $breaktime = $_POST["break-" . $x];
                    $sum += $breaktime;
                }
                $name = str_replace(get_string('copy', 'local_split_module'), "", $cmobject->name);
                $cmobject->name = $name . "-" . get_string('part', 'local_split_module') . $x . "";
                if (isset($module->timeopen)){ //quiz
                    $cmobject->timeopen = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                    $cmobject->timeclose = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                   // print_r($cmobject);
                    $DB->update_record('quiz',  $cmobject);
                } else {
                    $cmobject->allowsubmissionsfromdate = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                    $cmobject->duedate = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
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

           //print_r($cmids);
            $section = $DB->get_record('course_sections', array('id' => $cm->section), '*', MUST_EXIST);
            $sequence = explode("," , $section->sequence);
           // print_r($sequence);

            $pos = array_search(end($cmids), $sequence);
           // print_r("pos:  " . $pos);
            
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
                course_module_created_override($cmid, $course->id, "quiz", 1);

            } else { // assign
                $record = new \stdClass();
                $record->id = $module->id;
                $record->name = $module->name . "-" . get_string('dontdelete', 'local_split_module');
                $DB->update_record("assign", $record, $bulk=false);
                course_module_created_override($cmid, $course->id, "assign", 1);
            }
            rebuild_course_cache($course->id, $clearonly=false);
            redirect($CFG->wwwroot . '/course/view.php?id=' . $course->id);
        }
    }
} 
//else {
    // Displays the form.
    echo $OUTPUT->header();
   // echo $OUTPUT->heading("jjj");
    echo $mform->display();
    echo $html;
    echo $OUTPUT->footer();
//}
//echo $html;
//$html .= $OUTPUT->footer();
/*
$preview = "hiiiiii";
$data = new stdClass();
$data->uniqid = "split_mypop";
$data->body = '
<div id="splitpreview" class="form-group">
<label> '.get_string("pluginname", "local_split_module").':</label>'
. $preview . '</div>';
//<button class='btn btn-primary savescormbtn'>".get_string('pluginname', 'local_split_module')."</button>
$data->footer = "<button class='btn btn-secondary closebtn'>".get_string('close', 'local_split_module')."</button>";
$data->title = get_string('pluginname', 'local_split_module');
//echo $OUTPUT->render_from_template('core/modal', $data);
*/

/*
\mod_quiz\event\group_override_created	
\mod_quiz\event\group_override_deleted
\mod_quiz\event\group_override_updated

\mod_quiz\event\user_override_created	
\mod_quiz\event\user_override_deleted	
\mod_quiz\event\user_override_updated	

\mod_assign\event\group_override_created	
\mod_assign\event\group_override_deleted	
\mod_assign\event\group_override_updated	

\mod_assign\event\user_override_created	
\mod_assign\event\user_override_deleted	
\mod_assign\event\user_override_updated	
*/

