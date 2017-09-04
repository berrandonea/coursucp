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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later *
 *
 * File : togglecolumn.php
 * Called within AJAX process when a teacher moves his course between columns.
 */

require_once('../../config.php');

global $DB, $USER;
$courseid = required_param('courseid', PARAM_INT);
$colnumber = required_param('colnumber', PARAM_INT);
$coursecontextid = $DB->get_field('context', 'id', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $courseid));
$userteacher = $DB->record_exists('role_assignments', array('roleid' => 3, 'contextid' => $coursecontextid, 'userid' => $USER->id));

if ($userteacher) {
    if ($colnumber) {
        $DB->delete_records('block_mytermcourses_col', array('courseid' => $courseid, 'position' => 2));
    } else {
        $col = new stdClass();
        $col->courseid = $courseid;
        $col->position = 2;
        $col->id = $DB->insert_record('block_mytermcourses_col', $col);
    }
}

