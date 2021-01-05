
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
require_once( __DIR__ . '/../../lib/weblib.php');

function local_split_module_extend_settings_navigation( $nav, $context) {
    global $PAGE, $CFG, $DB, $COURSE, $USER;
    if ($PAGE->url->out_omit_querystring() == $CFG->wwwroot . "/course/view.php") {
        $PAGE->requires->js('/local/split_module/js/split_module.js');
    }
    $reportsnode = $nav->find('modulesettings', navigation_node::TYPE_SETTING);
    if ($reportsnode) {
    $cmid = $context->instanceid;
    $cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
    $type = $DB->get_field("modules", "name", ["id" => $cm->module], $strictness=IGNORE_MISSING);

    if ($type == "quiz" || $type == "assign") {
    $icon = new pix_icon('i/settings', '');
    $text = get_string('split', 'local_split_module');
    $url = new moodle_url('/local/split_module/split_module.php', array('cmid' => $cmid));
    $reportsnode->add($text, $url, navigation_node::TYPE_SETTING, null, 'split', $icon);
    }

}
}
