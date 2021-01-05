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

//This is an ajax file
//The ajax is called by js/split_module.js

define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__).'/../../config.php');
require_once('locallib.php');
require_once($CFG->dirroot."/course/lib.php");
require_once($CFG->dirroot."/question/editlib.php");
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/modinfolib.php");

global $DB, $CFG, $PAGE;
$PAGE->set_context(context_system::instance());
$cmid = $_POST['cmid'];

if (!empty($cmid)) {

    $sumparts = 0;
    $count = $_POST['count'];
    $message = "";
    $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
    $module = get_module_from_cmid($cm->id)[0];
    
    if (isset($module->timeopen)) { //quiz
        $timeopen = $module->timeopen;
        $timeclose = $module->timeclose;
    } else { // assign
        $timeopen = $module->allowsubmissionsfromdate;
        $timeclose = $module->duedate;
    }
    if (isset($timeopen) && !empty($timeopen)) {
    
        for ($x = 1; $x < $count; $x++) {
            $parttime = $_POST["part" . $x];            
            if ($x != 1) {
                $breaktime = $_POST["break" . $x];
                $sumparts += $breaktime;
            }
            $sumparts += $parttime;
        }
        $res = strtotime(' +' . $sumparts . ' minutes' ,$timeopen);
        if ($timeclose != $res) {
            $message = get_string('notequaltiming', 'local_split_module');
        } 
    } else {
        $message = get_string('declarefathertiming', 'local_split_module');
    }
}
echo json_encode($message);
