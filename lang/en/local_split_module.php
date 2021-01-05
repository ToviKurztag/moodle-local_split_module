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
 * You may localized strings in your plugin
 *
 * @package    local_split_module
 * @copyright  2020 OPENAPP
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */

$string['pluginname'] = 'Split Module';
$string['split'] = 'Split';
$string['part'] = ' part ';
$string['break'] = ' break ';
$string['parttime'] = 'time for part';
$string['breaktime'] = 'time for break';
$string['addnewpart'] = 'Add new part ';
$string['saveandsplit'] = 'Save and split';
$string['preview'] = 'Preview';
$string['split'] = 'Split';
$string['close'] = 'Close';
$string['fromtime'] = 'from time: ';
$string['totime'] = 'to time: ';
$string['notequaltiming'] = 'The times you set for the activity parts are not equals to the total time of the activity';
$string['copy'] = '(copy)';
$string['dontdelete'] = " -father module don't delete!";
$string['declarefathertiming'] = "You must declare father activity before";
$string['declareexamparts'] = "Defining exam parts";
$string['cleandata'] = "clean data split module";
$string['notset'] = "not set";
$string['minutesnotice'] = "A positive integer must be set";

$string['instructions'] = '</br>Notice! Enter an integer in minutes</br>
For example: for an hour and a half test, enter the number 90 in the field</br>
The father module is from: {$a->timeopen}, to: {$a->timeclose} </br> the total: {$a->totalhours} hours = {$a->totalminutes} minutes</br></br></br>';
