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
 * Code for handling mass enrolment from a cvs file
 *

 *
 * @package local
 * @subpackage comparaison
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @copyright 2012 onwards Patrick Pollet {@link mailto:pp@patrickpollet.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();





/**
 * Enter description here ...
 * @param string $newgroupname
 * @param int $courseid
 * @return int id   Moodle id of inserted record 
 */
function comparaison_add_group($newgroupname, $courseid) {
    $newgroup = new stdClass();
    $newgroup->name = $newgroupname;
    $newgroup->courseid = $courseid;
    $newgroup->lang = current_language();
    return groups_create_group($newgroup);
}


/**
 * Enter description here ...
 * @param string $newgroupingname
 * @param int $courseid
 * @return int id Moodle id of inserted record
 */
function comparaison_add_grouping($newgroupingname, $courseid) {
    $newgrouping = new StdClass();
    $newgrouping->name = $newgroupingname;
    $newgrouping->courseid = $courseid;
    return groups_create_grouping($newgrouping);
}

/**
 * @param string $name group name
 * @param int $courseid course
 * @return string or false 
 */
function comparaison_group_exists($name, $courseid) {
    return groups_get_group_by_name($courseid, $name);
}

/**
 * @param string $name group name
 * @param int $courseid course
 * @return string or false
 */
function comparaison_grouping_exists($name, $courseid) {
    return groups_get_grouping_by_name($courseid, $name);

}

/**
 * @param int $gid group ID
 * @param int $gpid grouping ID
 * @return mixed a fieldset object containing the first matching record or false
 */
function comparaison_group_in_grouping($gid, $gpid) {
     global $DB;
    $sql =<<<EOF
   select * from {groupings_groups}
   where groupingid = ?
   and groupid = ?
EOF;
    $params = array($gpid, $gid);
    return $DB->get_record_sql($sql,$params,IGNORE_MISSING);
}

/**
 * @param int $gid group ID
 * @param int $gpid grouping ID
 * @return bool|int true or new id
 * @throws dml_exception A DML specific exception is thrown for any errors.
 */
function comparaison_add_group_grouping($gid, $gpid) {
     global $DB;
    $new = new stdClass();
    $new->groupid = $gid;
    $new->groupingid = $gpid;
    $new->timeadded = time();
    return $DB->insert_record('groupings_groups', $new);
}
