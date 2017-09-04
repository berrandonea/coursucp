<?php

require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/enrollib.php');

$groupid = required_param('group', PARAM_INT);
$cancel  = optional_param('cancel', false, PARAM_BOOL);
$empty  = optional_param('empty', 0, PARAM_INT); //BRICE
$unen  = optional_param('unen', 0, PARAM_INT); //BRICE

$group = $DB->get_record('groups', array('id'=>$groupid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$group->courseid), '*', MUST_EXIST);

$PAGE->set_url('/group/emptygroup.php', array('group'=>$groupid));
$PAGE->set_pagelayout('admin');

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

//BRICE


$unenrolverif = pow(3, ($groupid % 10 + 10));

global $USER;
if ($empty == 1) {
    $sql = "DELETE FROM mdl_groups_members WHERE groupid = $groupid";    
    $DB->execute($sql);    
}

if ($unen == $unenrolverif) {
    require_capability('enrol/manual:unenrol', $context);
    
    $sql = "SELECT DISTINCT userid FROM mdl_groups_members WHERE groupid = $groupid";
    $groupmembers = $DB->get_recordset_sql($sql);
    
    foreach($groupmembers as $groupmember) {
        print_object($groupmember);
        
        $sql = "SELECT e.* FROM mdl_user_enrolments ue, mdl_enrol e WHERE ue.userid = $groupmember->userid AND e.id = ue.enrolid AND e.courseid = $course->id";
//        echo "$sql<br>";        exit;
        $instance = $DB->get_record_sql($sql);
        
        $enrolplugin = enrol_get_plugin($instance->enrol);
        //print_object($enrolplugin);
        $enrolplugin->unenrol_user($instance, $groupmember->userid);
        
        
    }    
}


//FIN


header("Location: members.php?group=$groupid");

