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

$string['pluginname'] = 'פיצול פעילות';
$string['split'] = 'פיצול';
$string['parttime'] = ' זמן לחלק ';
$string['part'] = ' חלק ';
$string['break'] = ' הפסקה ';
$string['breaktime'] = 'זמן להפסקה';
$string['addnewpart'] = 'הוסף חלק נוסף';
$string['saveandsplit'] = 'שמירה ופיצול';
$string['preview'] = 'תצוגה מקדימה';
$string['close'] = 'סגור תצוגה מקדימה';
$string['fromtime'] = 'מהשעה: ';
$string['totime'] = 'עד השעה: ';
$string['notequaltiming'] = 'הזמנים שקבעת לחלקי הפעילות לא שווים לזמן הכולל של הפעילות';
$string['copy'] = '(העתק)';
$string['dontdelete'] = "פעילות אב - לא למחוק! ";
$string['declarefathertiming'] = "חובה להגדיר קודם את זמן ההתחלה והסיום של פעילות האב";
$string['declareexamparts'] = "הגדרת חלקי הבחינה";
$string['cleandata'] = "ניקוי נתונים פיצול פעילות";
$string['notset'] = "לא מוגדר";
$string['minutesnotice'] = "יש להגדיר מספר שלם חיובי";

$string['instructions'] = 'שימו לב! יש להכניס מספר שלם בדקות</br>
לדוגמא: עבור בחינה של שעה וחצי יש להזין את המספר 90</br>
פעילות האב מוגדרת מהשעה : {$a->timeopen}, ועד: {$a->timeclose} </br> סך הכל זמן הבחינה: {$a->totalhours} שעות = {$a->totalminutes} דקות</br></br></br>';