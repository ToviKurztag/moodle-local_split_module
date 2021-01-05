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
 * Version details.
 *
 * @package   local_split_module
 * @copyright 2020 OPENAPP
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot."/course/lib.php");
require_once($CFG->dirroot."/question/editlib.php");

function add_parts_override($cmid) {

    global $DB;
    $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
    $module = get_module_from_cmid($cmid)[0];
    $parts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    $type = $DB->get_field("modules", "name", ["id" => $cm->module], $strictness=IGNORE_MISSING);
    $courseid = $cm->course;

    if ($type == "quiz") { //quiz
        if (isset($parts) && !empty($parts)) {
            $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            $overrides = $DB->get_records("quiz_overrides", ["quiz" => $module->id], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            foreach ($parts as $part) {
                if (isset($part->partid)) {
                $partmodule = get_module_from_cmid($part->partid)[0];
                $DB->delete_records('quiz_overrides', ["quiz" => $partmodule->id]); // before insert new data- delete the old overrides
                }
            }
                foreach ($overrides as $override) {     
                    if (isset($override->timeopen)) {
                        $newtimeopen = $override->timeopen;
                    } else {
                        $newtimeopen = $module->timeopen;
                    }
                    if (isset($override->timeclose)) {
                        $newtimeclose = $override->timeclose;
                    } else {
                        $newtimeclose = $module->timeclose;
                    }
                    
                    $timeopen = $module->timeopen;
                    $timeclose = $module->timeclose;
                    $sumbreak = 0;
                    $sum = 0;

                    foreach ($breaks as $break) {
                            $sumbreak += $break->time;
                    } 
                    $oldtime = $module->timeclose - $module->timeopen;
                    $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
                    $newtime = $newtimeclose- $newtimeopen;
                    $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
                    $per = $newtime/ $oldtime;
                    
                    foreach ($parts as $part) {
                        $parttime = ceil($part->time * $per);
                        if (!isset($part->partid)) {
                            $sum += $part->time;
                        } else {
                            $partmodule = get_module_from_cmid($part->partid)[0];
                            $record = new \stdClass();
                            $record->quiz = $partmodule->id;
                            if (isset($override->groupid)) { // group_override
                                $record->groupid = $override->groupid;
                            } else {
                                $record->userid = $override->userid;
                            }
                            $record->timeopen = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                            $record->timeclose = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                            $record->timelimit = $record->timeclose - $record->timeopen;
                            $DB->insert_record('quiz_overrides',  $record);
                            $sum += $parttime;
                        }  
                    } 
                }
        }
    } else {
        if (isset($parts) && !empty($parts)) {
            $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            $overrides = $DB->get_records("assign_overrides", ["assignid" => $module->id], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            foreach ($parts as $part) {
                if (isset($part->partid)) {
                    $partmodule = get_module_from_cmid($part->partid)[0];
                    $DB->delete_records('assign_overrides', ["assignid" => $partmodule->id]); // before insert new data- delete the old overrides
                }
            }
            foreach ($overrides as $override) {
                       
                if (isset($override->allowsubmissionsfromdate)) {
                    $newtimeopen = $override->allowsubmissionsfromdate;
                } else {
                    $newtimeopen = $module->allowsubmissionsfromdate;
                }
                if (isset($override->duedate)) {
                    $newtimeclose = $override->duedate;
                } else {
                    $newtimeclose = $module->duedate;
                }
                $timeopen = $module->allowsubmissionsfromdate;
                $timeclose = $module->duedate;
                $sumbreak = 0;
                $sum = 0;

                foreach ($breaks as $break) {
                        $sumbreak += $break->time;
                } 
                $oldtime = $module->duedate - $module->allowsubmissionsfromdate;
                $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
                $newtime = $newtimeclose - $newtimeopen;
                $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
                $per = $newtime / $oldtime;

                foreach ($parts as $part) {
                    $parttime = ceil($part->time * $per);
                    if (!isset($part->partid)) {
                        $sum += $part->time;
                    } else {
                        $partmodule = get_module_from_cmid($part->partid)[0];
                        $record = new \stdClass();
                        $record->assignid = $partmodule->id;
                        if (isset($override->groupid)) { // group_override
                            $record->groupid = $override->groupid;
                        } else {
                            $record->userid = $override->userid;
                        }
                        $record->allowsubmissionsfromdate = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                        $record->duedate = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                        $record->cutoffdate = $record->duedate;
                        $DB->insert_record('assign_overrides',  $record);
                        $sum += $parttime;
                    }  
                } 
            }
        }
    }
}

function cleandata_split_module() {

    global $DB;
    $parts = $DB->get_records("local_split_module", null, $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    foreach($parts as $part) {
        if (isset($part->partid)) { //part
            $DB->set_debug(1);
            $exist = $DB->record_exists("course_modules", ["id"=> $part->partid]);
            $DB->set_debug(0);
        } else { //break
            $exist = $DB->record_exists("course_modules", ["id"=> $part->fatherid]);
        }
        if (!isset($exist) || empty($exist)) {
            $DB->delete_records("local_split_module", ["id"=> $part->id]);
        }
    }
}

function course_module_updated_parts_timing($event) {
    
    global $DB;
    $cmid = $event->contextinstanceid;
    $action = $event->action;
    $userid = $event->relateduserid;
    $courseid = $event->courseid;
    $modulename = $event->other["modulename"];
    $instanceid = $event->other["instanceid"];     
   
    if ($modulename == "quiz" || $modulename == "assign") {
        $module = get_module_from_cmid($cmid)[0];
        $father = $DB->get_record("local_split_module", ["fatherid" => null, "partid" => $cmid], $fields='*', [IGNORE_MISSING,IGNORE_MULTIPLE]);
       
        if (isset($father) && !empty($father)) {
            $parts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            $sum = 0;
            $sumbreak = 0;
            
            if(isset($module->timeopen)){ //quiz
                $timeopen = $module->timeopen;
                $timeclose = $module->timeclose;
            } else {
                $timeopen = $module->allowsubmissionsfromdate;
                $timeclose = $module->duedate;
            }
            $oldtime = $father->time;
            $newtime = $timeclose - $timeopen;
            if ($oldtime != $newtime || $timeopen != $father->oldtimeopen || $timeclose != $father->oldtimeclose) {
                foreach ($breaks as $break) {
                        $sumbreak += $break->time;
                } 
                $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
                $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);

                if ($newtime != $oldtime) { // increase or decrease timing 
                    $per = $newtime / $oldtime;
                } else { // change date or hour
                    $per = 1;
                }
                foreach ($parts as $part) {
                    $parttime = ceil($part->time * $per);
                    if (!isset($part->partid)) {
                        $parttime = $part->time;
                        $sum += $parttime;
                    }
                    else if (isset($module->timeopen)) { //quiz
                        $partmodule = get_module_from_cmid($part->partid)[0];
                        $record = new \stdClass();
                        $record->id = $partmodule->id;
                        $record->timeopen = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                        $record->timeclose = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                        $record->timelimit = $record->timeclose - $record->timeopen;
                        $DB->update_record('quiz',  $record);
                        $sum += $parttime;
                    } else { //assign
                        $partmodule = get_module_from_cmid($part->partid)[0];
                        $record = new \stdClass();
                        $record->id = $partmodule->id;
                        $record->allowsubmissionsfromdate = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                        $record->duedate = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                        $record->cutoffdate = $record->duedate;
                        $DB->update_record('assign',  $record);
                        $sum += $parttime;
                    }
                    // Update parts timing
                    $record = new \stdClass();
                    $record->id = $part->id;
                    $record->time = $parttime;
                    $DB->update_record('local_split_module',  $record);	
                }
                if ($newtime != $oldtime) { // increase or decrease timing 
                    $record = new \stdClass();
                    $record->id = $father->id;
                    $record->time = $timeclose - $timeopen;
                    $record->oldtimeopen = $timeopen;
                    $record->oldtimeclose = $timeclose;
                    $DB->update_record('local_split_module',  $record);	
                } 
            }
        }
    }
}

function add_quiz_override_group($event, $issplit){

    global $DB;
    $cmid = $event->contextinstanceid;
    $action = $event->action;
    $courseid = $event->courseid;
    $groupid = $event->other["groupid"];
    $quizid = $event->other["quizid"];
    $module = get_module_from_cmid($cmid)[0];
    $parts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    if (isset($parts) && !empty($parts)) {
        $sql = "SELECT *
        FROM {quiz_overrides}
        WHERE quiz = ? AND groupid = ?
        ORDER BY id DESC
        LIMIT 1";
        $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
        $override = $DB->get_record_sql($sql , [$quizid, $groupid], $strictness=IGNORE_MISSING);

        if (isset($override->timeopen)) {
            $newtimeopen = $override->timeopen;
        } else {
            $newtimeopen = $module->timeopen;
        }
        if (isset($override->timeclose)) {
            $newtimeclose = $override->timeclose;
        } else {
            $newtimeclose = $module->timeclose;
        }
        
        $timeopen = $module->timeopen;
        $timeclose = $module->timeclose;
        $sumbreak = 0;
        $sum = 0;

        foreach ($breaks as $break) {
                $sumbreak += $break->time;
        } 
        $oldtime = $module->timeclose - $module->timeopen;
        $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
        $newtime = $newtimeclose- $newtimeopen;
        $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
        if (isset($event->per)) {
            $per = $event->per;
        } else {
            $per = $newtime/ $oldtime;
        }
        //print_r($parts);die;

        foreach ($parts as $part) {
            $parttime = ceil($part->time * $per);
            if (!isset($part->partid)) {
                $sum += $part->time;
            } else {
                $partmodule = get_module_from_cmid($part->partid)[0];
                if ($issplit == 1) {
                    $DB->delete_records("quiz_overrides", ["quiz"=> $partmodule->id, "groupid"=> $groupid]);
                }
                $record = new \stdClass();
                $record->quiz = $partmodule->id;
                $record->groupid = $groupid;
                $record->timeopen = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                $record->timeclose = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                $record->timelimit = $record->timeclose - $record->timeopen;

                if ($action == "created") {
                    $DB->insert_record('quiz_overrides',  $record);
                } else if($action == "updated") {
                    $partoverride = $DB->get_record("quiz_overrides", ["quiz" => $partmodule->id, "groupid" => $groupid], $fields='*', $strictness=IGNORE_MISSING);
                    $record->id = $partoverride->id;
                    $DB->update_record('quiz_overrides',  $record);
                } else {
                    $DB->delete_records('quiz_overrides', ["quiz" => $partmodule->id, "groupid" => $groupid]);
                }
                $sum += $parttime;
            }  
        } 
}
}

function add_quiz_override_user($event){
    
    global $DB;
    $cmid = $event->contextinstanceid;
    $action = $event->action;
    $userid = $event->relateduserid;
    $courseid = $event->courseid;
    $quizid = $event->other["quizid"];
    $module = get_module_from_cmid($cmid)[0];
    $parts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    if (isset($parts) && !empty($parts)) {

        $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
        $override = $DB->get_record("quiz_overrides", ["quiz" => $quizid, "userid" => $userid], $fields='*', $strictness=IGNORE_MISSING);
        if (isset($override->timeopen)) {
            $newtimeopen = $override->timeopen;
        } else {
            $newtimeopen = $module->timeopen;
        }
        if (isset($override->timeclose)) {
            $newtimeclose = $override->timeclose;
        } else {
            $newtimeclose = $module->timeclose;
        }

        $timeopen = $module->timeopen;
        $timeclose = $module->timeclose;
        $sumbreak = 0;
        $sum = 0;

        foreach ($breaks as $break) {
            $sumbreak += $break->time;
        } 
        $oldtime = $module->timeclose - $module->timeopen;
        $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
        $newtime = $newtimeclose - $newtimeopen;
        $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
        $per = $newtime/ $oldtime;

        
        foreach ($parts as $part) {
            $parttime = ceil($part->time * $per);
            if (!isset($part->partid)) {
                $sum += $part->time;
            } else {
                $partmodule = get_module_from_cmid($part->partid)[0];
                $record = new \stdClass();
                $record->quiz = $partmodule->id;
                $record->userid = $userid;
                $record->timeopen = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                $record->timeclose = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                $record->timelimit = $record->timeclose - $record->timeopen;

                if ($action == "created") {
                    $DB->insert_record('quiz_overrides',  $record);
                } else if($action == "updated") {
                    $partoverride = $DB->get_record("quiz_overrides", ["quiz" => $partmodule->id, "userid" => $userid], $fields='*', $strictness=IGNORE_MISSING);
                    $record->id = $partoverride->id;
                    $DB->update_record('quiz_overrides',  $record);
                } else {
                    $DB->delete_records('quiz_overrides', ["quiz" => $partmodule->id, "userid" => $userid]);
                }
                $sum += $parttime;
            }  
        } 
    }
 }

 //OK
 function add_assign_override_group($event, $issplit){
   
    global $DB;
    $cmid = $event->contextinstanceid;
    $action = $event->action;
    $courseid = $event->courseid;
    $groupid = $event->other["groupid"];
    $assignid = $event->other["assignid"];
    $module = get_module_from_cmid($cmid)[0];
    $parts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    if (isset($parts) && !empty($parts)) {

        $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
        $override = $DB->get_record("assign_overrides", ["assignid" => $assignid, "groupid" => $groupid], $fields='*', $strictness=IGNORE_MISSING);
        if (isset($override->allowsubmissionsfromdate)) {
            $newtimeopen = $override->allowsubmissionsfromdate;
        } else {
            $newtimeopen = $module->allowsubmissionsfromdate;
        }
        if (isset($override->duedate)) {
            $newtimeclose = $override->duedate;
        } else {
            $newtimeclose = $module->duedate;
        }

        $timeopen = $module->allowsubmissionsfromdate;
        $timeclose = $module->duedate;
        $sumbreak = 0;
        $sum = 0;

        foreach ($breaks as $break) {
            $sumbreak += $break->time;
        } 
        $oldtime = $module->duedate - $module->allowsubmissionsfromdate;
        $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
        $newtime = $newtimeclose - $newtimeopen;
        $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
        if (isset($event->per)) {
            $per = $event->per;
        } else {
            $per = $newtime / $oldtime;
        }
        foreach ($parts as $part) {
            $parttime = ceil($part->time * $per);
            if (!isset($part->partid)) {
                $sum += $part->time;
            } else {
                $partmodule = get_module_from_cmid($part->partid)[0];
                if ($issplit == 1) {
                    $DB->delete_records("assign_overrides", ["assignid"=> $partmodule->id, "groupid"=> $groupid]);
                }
                $record = new \stdClass();
                $record->assignid = $partmodule->id;
                $record->groupid = $groupid;
                $record->allowsubmissionsfromdate = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                $record->duedate = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                $record->cutoffdate = $record->duedate;

                if ($action == "created") {
                    $DB->insert_record('assign_overrides',  $record);
                } else if($action == "updated") {
                    $partoverride = $DB->get_record("assign_overrides", ["assignid" => $partmodule->id, "groupid" => $groupid], $fields='*', $strictness=IGNORE_MISSING);
                    $record->id = $partoverride->id;
                  //  print_r($record);die;
                    $DB->update_record('assign_overrides',  $record);
                } else {
                    $DB->delete_records('assign_overrides', ["assignid" => $partmodule->id, "groupid" => $groupid]);
                }
                $sum += $parttime;
            }  
        } 
    }
 }
 
 function add_assign_override_user($event){
    
    global $DB;
    $cmid = $event->contextinstanceid;
    $action = $event->action;
    $userid = $event->relateduserid;
    $courseid = $event->courseid;
    $assignid = $event->other["assignid"];
    $module = get_module_from_cmid($cmid)[0];
    $parts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    if (isset($parts) && !empty($parts)) {

        $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
        $override = $DB->get_record("assign_overrides", ["assignid" => $assignid, "userid" => $userid], $fields='*', $strictness=IGNORE_MISSING);
        if (isset($override->allowsubmissionsfromdate)) {
            $newtimeopen = $override->allowsubmissionsfromdate;
        } else {
            $newtimeopen = $module->allowsubmissionsfromdate;
        }
        if (isset($override->duedate)) {
            $newtimeclose = $override->duedate;
        } else {
            $newtimeclose = $module->duedate;
        }
        
        $timeopen = $module->allowsubmissionsfromdate;
        $timeclose = $module->duedate;
        $sumbreak = 0;
        $sum = 0;


        foreach ($breaks as $break) {
            $sumbreak += $break->time;
        } 
        $oldtime = $module->duedate - $module->allowsubmissionsfromdate;
        $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
        $newtime = $newtimeclose - $newtimeopen;
        $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
        $per = $newtime/ $oldtime;
        

        foreach ($parts as $part) {
            $parttime = ceil($part->time * $per);
            if (!isset($part->partid)) {
                $sum += $part->time;
            } else {
                $partmodule = get_module_from_cmid($part->partid)[0];
                $record = new \stdClass();
                $record->assignid = $partmodule->id;
                $record->userid = $userid;
                $record->allowsubmissionsfromdate = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                $record->duedate = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                $record->cutoffdate = $record->duedate;

                if ($action == "created") {
                    $DB->insert_record('assign_overrides',  $record);
                } else if($action == "updated") {
                    $partoverride = $DB->get_record("assign_overrides", ["assignid" => $partmodule->id, "userid" => $userid], $fields='*', $strictness=IGNORE_MISSING);
                    $record->id = $partoverride->id;
                    $DB->update_record('assign_overrides',  $record);
                } else {
                    $DB->delete_records('assign_overrides', ["assignid" => $partmodule->id, "userid" => $userid]);
                }
                $sum += $parttime;
            }  
        } 
    }
}



/*
function course_module_created_override($cmid, $courseid, $modulename, $issplit) {
    
    global $DB;
    $module = get_module_from_cmid($cmid)[0];

    if ($modulename == "quiz" || $modulename == "assign") {
        
        $father = $DB->get_record("local_split_module", ["fatherid" => null, "partid" => $cmid], $fields='*', $strictness=IGNORE_MISSING);
        
        if (!isset($father) || empty($father)) { // Add father override
            $issplit = 0;
        } else if ($issplit == 1){ // Add parts override
            $issplit = 1;
        } else {
            $issplit = -1;
        }

        if ($issplit != -1) {

            $sql = "SELECT * FROM mdl_groups
            WHERE courseid = ?
            AND name like '%הארכת זמן%'";
            $groups = $DB->get_records_sql($sql, ["courseid" => $courseid], $limitfrom=0, $limitnum=0);
            $per = 1.25;

            if ($modulename == "quiz") {
                
                $timeopen = $module->timeopen;
                $timeclose = $module->timeclose;
                $time = ceil(($timeclose - $timeopen) * $per);
                
                foreach ($groups as $group) {
                    if ($issplit == 1) {
                        $DB->delete_records("quiz_overrides", ["quiz"=> $module->id, "groupid"=> $group->id]);
                    }
                    $record = new \stdClass();
                    $record->quiz = $module->id;
                    $record->groupid = $group->id;
                    $record->timeopen = $timeopen;
                    $record->timeclose = $timeopen + $time;
                    $record->timelimit = $record->timeclose - $record->timeopen;

                    $DB->insert_record('quiz_overrides',  $record);
                    if ($issplit = 1) {
                        $event = new \stdClass();
                        $event->contextinstanceid = $cmid;
                        $event->action = "created";
                        $event->courseid = $courseid;
                        $event->other["groupid"] = $group->id;
                        $event->other["quizid"] = $module->id;
                        $event->per = 1.25;
                        add_quiz_override_group($event, $issplit);
                    }
                }
                $record = new \stdClass();
                $record->partid = $cmid;
                $record->time = $module->timeclose - $module->timeopen;
                $record->oldtimeopen = $module->timeopen;
                $record->oldtimeclose = $module->timeclose;
        
                $DB->insert_record('local_split_module',  $record);	
        
            } else {
                $timeopen = $module->allowsubmissionsfromdate;
                $timeclose = $module->duedate;
                $time = ceil(($timeclose - $timeopen) * $per);
                foreach ($groups as $group) {
                    if ($issplit == 1) {
                        $DB->delete_records("assign_overrides", ["assignid"=> $module->id, "groupid"=> $group->id]);
                    }
                    $record = new \stdClass();
                    $record->assignid = $module->id;
                    $record->groupid = $group->id;
                    $record->allowsubmissionsfromdate = $timeopen;
                    $record->duedate = $timeopen + $time;
                    $record->cutoffdate = $record->duedate;
                    $DB->insert_record('assign_overrides',  $record);
        
                    if ($issplit == 1) {
                        $event = new \stdClass();
                        $event->contextinstanceid = $cmid;
                        $event->action = "created";
                        $event->courseid = $courseid;
                        $event->other["groupid"] = $group->id;
                        $event->other["assignid"] = $module->id;
                        $event->per = 1.25;
                        add_assign_override_group($event, $issplit);
                    }
                }
                $record = new \stdClass();
                $record->partid = $cmid;
                $record->time = $module->duedate - $module->allowsubmissionsfromdate;
                $record->oldtimeopen = $module->allowsubmissionsfromdate;
                $record->oldtimeclose = $module->duedate;
                $DB->insert_record('local_split_module',  $record);	
            } 
    }
}
}

*/
//CHECK
/*
function course_module_created_override($event) {
        global $DB;
        $cmid = $event->contextinstanceid;
        $action = $event->action;
        $userid = $event->relateduserid;
        $courseid = $event->courseid;
        $modulename = $event->other["modulename"];
        $instanceid = $event->other["instanceid"];     
        $module = get_module_from_cmid($cmid)[0];

        if ($modulename == "quiz" || $modulename == "assign") {
            
            //$sql = "SELECT *
            //FROM {local_split_module}
            //WHERE partid = ? AND fatherid IS NOT NULL";

            //$parts = $DB->get_record_sql($sql, ["partid" => $cmid], $strictness=IGNORE_MISSING);
            //$father = $DB->get_record("local_split_module", ["fatherid" => null, "partid" => $cmid], $fields='*', $strictness=IGNORE_MISSING);

            $sql = "SELECT * FROM mdl_groups
            WHERE courseid = ?
            AND name like '%הארכת זמן%'";
            $groups = $DB->get_records_sql($sql, ["courseid" => $courseid], $limitfrom=0, $limitnum=0);
            $per = 1.25;
            
            if ($modulename == "quiz") {
                $timeopen = $module->timeopen;
                $timeclose = $module->timeclose;
                $time = ceil(($timeclose - $timeopen) * $per);
                
                foreach ($groups as $group) {
                    $record = new \stdClass();
                    $record->quiz = $instanceid;
                    $record->groupid = $group->id;
                    $record->timeopen = $timeopen;
                    $record->timeclose = $timeopen + $time;
                    
                    $DB->insert_record('quiz_overrides',  $record);
    
                    $event = new \stdClass();
                    $event->contextinstanceid = $cmid;
                    $event->action = "created";
                    $event->userid = $userid;
                    $event->courseid = $courseid;
                    $event->other["groupid"] = $group->id;
                    $event->other["quizid"] = $instanceid;
                    $event->per = 1.25;
                    add_quiz_override_group($event);
                }
                $record = new \stdClass();
                $record->partid = $cmid;
                $record->time = $module->timeclose - $module->timeopen;
                $record->oldtimeopen = $module->timeopen;
                $record->oldtimeclose = $module->timeclose;

                $DB->insert_record('local_split_module',  $record);	

            } else {
                $timeopen = $module->allowsubmissionsfromdate;
                $timeclose = $module->duedate;
                $time = ceil(($timeclose - $timeopen) * $per);
                foreach ($groups as $group) {
                    $record = new \stdClass();
                    $record->assignid = $instanceid;
                    $record->groupid = $group->id;
                    $record->allowsubmissionsfromdate = $timeopen;
                    $record->duedate = $timeopen + $time;

                    $DB->insert_record('assign_overrides',  $record);
    
                    $event = new \stdClass();
                    $event->contextinstanceid = $cmid;
                    $event->action = "created";
                    $event->userid = $userid;
                    $event->courseid = $courseid;
                    $event->other["groupid"] = $group->id;
                    $event->other["assignid"] = $instanceid;
                    $event->per = 1.25;
                    add_assign_override_group($event);
                }
                $record = new \stdClass();
                $record->partid = $cmid;
                $record->time = $module->duedate - $module->allowsubmissionsfromdate;
                $record->oldtimeopen = $module->allowsubmissionsfromdate;
                $record->oldtimeclose = $module->duedate;
                $DB->insert_record('local_split_module',  $record);	
            } 
    }
}
*/

/*
function course_module_deleted_split_module($event) {

    global $DB;
    $cmid = $event->contextinstanceid;
    $action = $event->action;
    $userid = $event->userid;
    $courseid = $event->courseid;
    $modulename = $event->other["modulename"];
    $instanceid = $event->other["instanceid"]; 

    if ($modulename == "quiz" || $modulename == "assign") {

        $part = $DB->get_record("local_split_module", ["partid" => $cmid], $fields='*', $strictness=IGNORE_MISSING);
        $fatherparts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);

        // Delete part
        if (isset($part) && !empty($part)) {
            $DB->delete_records("local_split_module", ["partid" => $cmid]);
            if (!isset($fatherparts) || empty($fatherparts)) {
                //$allparts = $DB->get_records("local_split_module", ["fatherid" => $part->fatherid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
                //foreach($allparts as $part) {
                //    course_delete_module($part->partid, $async = false);
                //}
                //$DB->delete_records("local_split_module", ["fatherid" => $cmid]);
            }
        // Delete father + parts
        } 
        if (isset($fatherparts) && !empty($fatherparts)) {  
            //print_r($fatherparts);
            foreach($fatherparts as $part) {
                course_delete_module($part->partid, $async = false);
            }
            $DB->delete_records("local_split_module", ["fatherid" => $cmid]);
        }
        rebuild_course_cache($courseid, $clearonly=false);
    }
}



function add_parts_override($cmid ,$action) {

    global $DB;
    $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
    $module = get_module_from_cmid($cmid)[0];
    $parts = $DB->get_records("local_split_module", ["fatherid" => $cmid], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    $type = $DB->get_field("modules", "name", ["id" => $cm->module], $strictness=IGNORE_MISSING);
    $courseid = $cm->course;

    if ($type == "quiz") { //quiz
        if (isset($parts) && !empty($parts)) {
            $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            $overrides = $DB->get_records("quiz_overrides", ["quiz" => $module->id], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            foreach ($parts as $part) {
                if (isset($part->partid)) {
                $partmodule = get_module_from_cmid($part->partid)[0];
                $DB->delete_records('quiz_overrides', ["quiz" => $partmodule->id]); // before insert new data- delete the old overrides
                }
            }
            foreach ($overrides as $override) {     
                if (isset($override->timeopen)) {
                    $newtimeopen = $override->timeopen;
                } else {
                    $newtimeopen = $module->timeopen;
                }
                if (isset($override->timeclose)) {
                    $newtimeclose = $override->timeclose;
                } else {
                    $newtimeclose = $module->timeclose;
                }
                
                $timeopen = $module->timeopen;
                $timeclose = $module->timeclose;
                $sumbreak = 0;
                $sum = 0;

                foreach ($breaks as $break) {
                        $sumbreak += $break->time;
                } 
                $oldtime = $module->timeclose - $module->timeopen;
                $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
                $newtime = $newtimeclose- $newtimeopen;
                $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
                $per = $newtime/ $oldtime;
                
                foreach ($parts as $part) {
                    $parttime = ceil($part->time * $per);
                    if (!isset($part->partid)) {
                        $sum += $part->time;
                    } else {
                        $partmodule = get_module_from_cmid($part->partid)[0];
                        $record = new \stdClass();
                        $record->quiz = $partmodule->id;
                        if (isset($override->groupid)) { // group_override
                            $record->groupid = $override->groupid;
                        } else {
                            $record->userid = $override->userid;
                        }
                        $record->timeopen = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                        $record->timeclose = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                        $record->timelimit = $record->timeclose - $record->timeopen;
                        if ($action == "c") {
                            $DB->insert_record('quiz_overrides',  $record);
                        } else if($action == "u") {
                            if (isset($override->groupid)) { // group_override
                                $partoverride = $DB->get_record("quiz_overrides", ["quiz" => $partmodule->id, "groupid" => $override->groupid], $fields='*', $strictness=IGNORE_MISSING);
                            } else {
                                $partoverride = $DB->get_record("quiz_overrides", ["quiz" => $partmodule->id, "userid" => $override->userid], $fields='*', $strictness=IGNORE_MISSING);
                            }
                            $record->id = $partoverride->id;
                            //print_r($record);die;
                            $DB->update_record('quiz_overrides',  $record);
                        } else {
                            if (isset($override->groupid)) { // group_override
                                $DB->delete_records('quiz_overrides', ["quiz" => $partmodule->id, "groupid" => $override->groupid]);
                            } else {
                                $DB->delete_records('quiz_overrides', ["quiz" => $partmodule->id, "userid" => $override->userid]);
                            }                }
                        $sum += $parttime;
                    }  
                } 
            }
        }
    } else {
        if (isset($parts) && !empty($parts)) {
            $breaks = $DB->get_records("local_split_module", ["fatherid" => $cmid, "partid" => null], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            $overrides = $DB->get_records("assign_overrides", ["assignid" => $module->id], $sort='', $fields='*', $limitfrom=0, $limitnum=0);
            foreach ($parts as $part) {
                if (isset($part->partid)) {
                    $partmodule = get_module_from_cmid($part->partid)[0];
                    $DB->delete_records('assign_overrides', ["assignid" => $partmodule->id]); // before insert new data- delete the old overrides
                }
            }
            foreach ($overrides as $override) {
                       
                if (isset($override->allowsubmissionsfromdate)) {
                    $newtimeopen = $override->allowsubmissionsfromdate;
                } else {
                    $newtimeopen = $module->allowsubmissionsfromdate;
                }
                if (isset($override->duedate)) {
                    $newtimeclose = $override->duedate;
                } else {
                    $newtimeclose = $module->duedate;
                }
                $timeopen = $module->allowsubmissionsfromdate;
                $timeclose = $module->duedate;
                $sumbreak = 0;
                $sum = 0;

                foreach ($breaks as $break) {
                        $sumbreak += $break->time;
                } 
                $oldtime = $module->duedate - $module->allowsubmissionsfromdate;
                $oldtime = strtotime('-' . $sumbreak . ' minutes' ,$oldtime);
                $newtime = $newtimeclose - $newtimeopen;
                $newtime = strtotime('-' . $sumbreak . ' minutes' ,$newtime);
                $per = $newtime / $oldtime;
               // print_r($parts);die;

                foreach ($parts as $part) {
                    $parttime = ceil($part->time * $per);
                    if (!isset($part->partid)) {
                        $sum += $part->time;
                    } else {
                        $partmodule = get_module_from_cmid($part->partid)[0];
                        $record = new \stdClass();
                        $record->assignid = $partmodule->id;
                        if (isset($override->groupid)) { // group_override
                            $record->groupid = $override->groupid;
                        } else {
                            $record->userid = $override->userid;
                        }
                        $record->allowsubmissionsfromdate = strtotime(' +' . $sum . ' minutes' ,$timeopen);
                        $record->duedate = strtotime(' +' . ($sum + $parttime) . ' minutes' ,$timeopen);
                        $record->cutoffdate = $record->duedate;
                       // print_r($record);die;
                        if ($action == "c") {
                            $DB->insert_record('assign_overrides',  $record);
                        } else if($action == "u") {
                            if (isset($override->groupid)) { // group_override
                                $partoverride = $DB->get_record("assign_overrides", ["assignid" => $partmodule->id, "groupid" => $override->groupid], $fields='*', $strictness=IGNORE_MISSING);
                            } else {
                                $partoverride = $DB->get_record("assign_overrides", ["assignid" => $partmodule->id, "userid" => $override->userid], $fields='*', $strictness=IGNORE_MISSING);
                            }
                            $record->id = $partoverride->id;
                            //print_r($record);die;
                            $DB->update_record('assign_overrides',  $record);
                        } else {
                            if (isset($override->groupid)) { // group_override
                                $DB->delete_records('assign_overrides', ["assignid" => $partmodule->id, "groupid" => $override->groupid]);
                            } else {
                                $DB->delete_records('assign_overrides', ["assignid" => $partmodule->id, "userid" => $override->userid]);
                            }                }
                        $sum += $parttime;
                    }  
                } 
            }
        }
    }
}

*/
