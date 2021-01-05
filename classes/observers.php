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
namespace local_split_module;

require_once(__DIR__.'/../locallib.php');
class observers {
    public static function quiz_group_override($event) {
        $issplit = 0;
        add_quiz_override_group($event, $issplit);
    }
    public static function quiz_user_override($event) {
       add_quiz_override_user($event);
    }
    public static function assign_group_override($event) {
        $issplit = 0;
        add_assign_override_group($event, $issplit);
    }
    public static function assign_user_override($event) {
       add_assign_override_user($event);
    }
    public static function course_module_updated($event) {
        course_module_updated_parts_timing($event);
    }
    public static function course_module_created($event) {
        //$cmid = $event->contextinstanceid;
        //$courseid = $event->courseid;
        //$modulename = $event->other["modulename"];
        //$issplit = 0;
        //course_module_created_override($cmid, $courseid, $modulename, $issplit);
    }
    // In case recycle bin disable
    //public static function course_module_deleted($event) {
       // course_module_deleted_split_module($event);
    //}
}
