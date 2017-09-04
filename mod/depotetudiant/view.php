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
 * Folder module main user interface
 *
 * @package    mod
 * @subpackage depotetudiant
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("../../lib/accesslib.php");
require_once("$CFG->dirroot/mod/depotetudiant/locallib.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);  // Course module ID
$f  = optional_param('f', 0, PARAM_INT);   // Folder instance id

if ($f) {  // Two ways to specify the module
    $depotetudiant = $DB->get_record('depotetudiant', array('id'=>$f), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('depotetudiant', $depotetudiant->id, $depotetudiant->course, true, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('depotetudiant', $id, 0, true, MUST_EXIST);
    $depotetudiant = $DB->get_record('depotetudiant', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/depotetudiant:view', $context);
if ($depotetudiant->display == DEPOTETUDIANT_DISPLAY_INLINE) {
    redirect(course_get_url($depotetudiant->course, $cm->sectionnum));
}

add_to_log($course->id, 'depotetudiant', 'view', 'view.php?id='.$cm->id, $depotetudiant->id, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/depotetudiant/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$depotetudiant->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($depotetudiant);


/*//Si ce dossier est le dossier partagé des étudiants d'un groupe de situation problème
$sql = "SELECT COUNT(id) AS isshareddepotetudiant, groupid FROM mdl_sp_groups WHERE depotetudiantcmid = $id";
//echo $sql."<br/>";

$spdata = $DB->get_record_sql($sql);

//echo "isshareddepotetudiant: $spdata->isshareddepotetudiant<br/>";


if ($spdata->isshareddepotetudiant > 0) {
    $allowedgroupid = $spdata->groupid;
    //echo "allowedgroupid: $allowedgroupid<br/>";
        
    //On regarde si l'utilisateur courant est dans le groupe associé à cette section.
    $sql = "SELECT COUNT(id) AS isinallowedgroup FROM mdl_groups_members WHERE groupid = $allowedgroupid AND userid = $USER->id";
    //echo $sql."<br/>";
    $isinallowedgroup = $DB->get_record_sql($sql)->isinallowedgroup;
    //echo "isinallowedgroup: $isinallowedgroup<br/>";
    
    $sql = "SELECT COUNT(id) AS couldedit FROM mdl_role_assignments WHERE roleid = 3 AND contextid = $context->id AND userid = $USER->id";
    //echo $sql."<br/>";
    $couldedit = $DB->get_record_sql($sql)->couldedit;       
    //echo "couldedit: $couldedit<br/>";

    //S'il y est, il doit avoir le rôle enseignant dans le contexte de ce dossier (c'est le contexte courant)
    if ($isinallowedgroup > 0) {        
        //S'il n'a pas encore le rôle enseignant dans ce dossier, on le lui donne
        if ($couldedit == 0) {
         role_assign(3, $USER->id, $context->id);
        }    
    } else {
        //S'il n'y est pas, il ne doit pas avoir le rôle enseignant dans ce dossier. S'il l'a, on le lui retire
        if ($couldedit > 0) {
            role_unassign(3, $USER->id, $context->id);
        }
    }
}*/





$output = $PAGE->get_renderer('mod_depotetudiant');

echo $output->header();

echo $output->heading(format_string($depotetudiant->name), 2);

echo $output->display_depotetudiant($depotetudiant);

echo $output->footer();
