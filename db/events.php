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

$observers = array(
 
array(
     'eventname'   => '\mod_quiz\event\group_override_created',
     'callback'    => '\local_split_module\observers::quiz_group_override',
),
array(
    'eventname'   => '\mod_quiz\event\user_override_created',
    'callback'    => '\local_split_module\observers::quiz_user_override',
),
array(
    'eventname'   => '\mod_assign\event\group_override_created',
    'callback'    => '\local_split_module\observers::assign_group_override',
),
array(
    'eventname'   => '\mod_assign\event\user_override_created',
    'callback'    => '\local_split_module\observers::assign_user_override',
),
array(
    'eventname'   => '\mod_quiz\event\group_override_deleted',
    'callback'    => '\local_split_module\observers::quiz_group_override',
),
array(
   'eventname'   => '\mod_quiz\event\user_override_deleted',
   'callback'    => '\local_split_module\observers::quiz_user_override',
),
array(
   'eventname'   => '\mod_assign\event\group_override_deleted',
   'callback'    => '\local_split_module\observers::assign_group_override',
),
array(
   'eventname'   => '\mod_assign\event\user_override_deleted',
   'callback'    => '\local_split_module\observers::assign_user_override',
),
array(
    'eventname'   => '\mod_quiz\event\group_override_updated',
    'callback'    => '\local_split_module\observers::quiz_group_override',
),
array(
   'eventname'   => '\mod_quiz\event\user_override_updated',
   'callback'    => '\local_split_module\observers::quiz_user_override',
),
array(
   'eventname'   => '\mod_assign\event\group_override_updated',
   'callback'    => '\local_split_module\observers::assign_group_override',
),
array(
   'eventname'   => '\mod_assign\event\user_override_updated',
   'callback'    => '\local_split_module\observers::assign_user_override',
),
array(
    'eventname'   => '\core\event\course_module_updated',
    'callback'    => '\local_split_module\observers::course_module_updated',
),
array(
    'eventname'   => '\core\event\course_module_created',
    'callback'    => '\local_split_module\observers::course_module_created',
),
array(
    'eventname'   => '\core\event\course_module_deleted',
    'callback'    => '\local_split_module\observers::course_module_deleted',
),
);
