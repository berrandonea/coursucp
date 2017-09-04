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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Displays the courses within which the user his enrolled, their categories, 
 * and their teachers. Courses open to all users are also displayed.
 * Courses are displayed on two columns, one for each term in the year. 
 * Teachers can move their courses to select the relevant column.
 *
 * @package    block_mytermcourses
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : lang/en/block_mytermcourses.php
 * English text strings
 * 
 */

$string['pluginname'] = 'My term courses';
$string['mytermcourses'] = 'My term courses';
$string['mytermcourses:addinstance'] = 'Add a new My term courses block';
$string['mytermcourses:myaddinstance'] = 'Add a new My term courses block to the My Moodle page';
$string['changetitle'] = 'Change block title';
$string['bgcolor'] = 'Categories background color';
$string['teacher'] = 'Teacher';
$string['teachers'] = 'Teachers';
$string['useidnumber'] = 'Sort courses by idnumber';
$string['commoncategories'] = 'Common categories';
$string['notenrolled'] = "You're not enrolled in any course";
$string['firstterm'] = 'First term';
$string['secondterm'] = 'Second term';
$string['configidnumber_help'] = 'When displaying the courses, instead of sorting them by sortorder, sort them by idnumber.';
$string['configcommon_help'] = 'Courses within theses categories will be displayed to all users, even if they are not enrolled in. You may want to make sure these courses are open to all users. Write categories ids, separated by ; symbols.';
