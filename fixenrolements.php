
<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

$roleassignments = $DB->get_recordset('role_assignments');
foreach($roleassignments as $roleassignment) {
	$user = $DB->get_record('user', array('id' => $roleassignment->userid));
	if ($user) {
		$context = $DB->get_record('context', array('id' => $roleassignment->contextid));
		if ($context) {
			if ($context->contextlevel == 50) {
				fixenrolment($roleassignment, $context, $user);
			}
		}
	}	
}
$roleassignments->close();


function fixenrolment($roleassignment, $context, $user) {
	global $DB;
	$course = $DB->get_record('course', array('id' => $context->instanceid));	
	$guestenrol = $DB->get_record('enrol', array('enrol' => 'guest', 'courseid' => $course->id));
	$manualenrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $course->id));
	$selfenrol = $DB->get_record('enrol', array('enrol' => 'self', 'courseid' => $course->id));
	$isenroled = false;
	$guestenrolment = $DB->get_record('user_enrolments', array('enrolid' => $guestenrol->id, 'userid' => $user->id));
	if ($guestenrolment) {
		$isenroled = true;
	}
	$manualenrolment = $DB->get_record('user_enrolments', array('enrolid' => $manualenrol->id, 'userid' => $user->id));
	if ($manualenrolment) {
		$isenroled = true;
	}
	$selfenrolment = $DB->get_record('user_enrolments', array('enrolid' => $selfenrol->id, 'userid' => $user->id));
	if ($selfenrolment) {
		$isenroled = true;
	}
	if (!$isenroled) {
		echo "$user->firstname $user->lastname $course->fullname";
		recreateenrolment($manualenrol, $user, $roleassignment);
	}
}

function recreateenrolment($manualenrol, $user, $roleassignment) {
	global $DB;
	$enrolment = new stdClass();
	$enrolment->status = 0;
	$enrolment->enrolid = $manualenrol->id;
	$enrolment->userid = $user->id;
	$enrolment->timestart = $roleassignment->timemodified;
	$enrolment->timecreated = $roleassignment->timemodified;
	$enrolment->timemodified = $roleassignment->timemodified;
	$enrolment->timeend = 0;
	$enrolment->modifierid = $roleassignment->modifierid;
	$enrolment->id = $DB->insert_record('user_enrolments', $enrolment);
	echo " OK\n";
}

