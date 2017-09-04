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
 * Delete group
 *
 * @package   core_group
 * @copyright 2008 The Open University, s.marshall AT open.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once('lib.php');
//require_once('libsp.php');
// Get and check parameters
$spid = required_param('spid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id'=>$courseid));
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

$sql = "select * from mdl_groupings where spid=$spid";
$spgrouping = $DB->get_record_sql($sql);

$sql = "SELECT groupid FROM mdl_groupings_groups WHERE groupingid = $spgrouping->id";
$spgroupinggroups = $DB->get_recordset_sql($sql);


foreach($spgroupinggroups as $spgroupinggroup){
    $DB->delete_records('groups_members',array('groupid'=>$spgroupinggroup->groupid));
}

redirect(new moodle_url('/group/spmembers.php', array('id'=>$courseid,'spid'=>$spid)));


