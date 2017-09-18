
<?php
define('CLI_SCRIPT', true);
require_once('config.php');

/*$droitstudents = $DB->get_recordset('ufr_student', array('ufrcode' => 'Y2017-1'));
foreach ($droitstudents as $droitstudent) {
    $DB->delete_records('user_enrolments', array(
}
$droitstudents->close();*/



$sqlcoursdroit = "SELECT id, idnumber FROM {course} WHERE idnumber LIKE 'Y2017-1%'";
$courses = $DB->get_recordset_sql($sqlcoursdroit);
foreach ($courses as $course) {
    echo "$course->id  $course->idnumber\n";
    $enrolmethods = $DB->get_records('enrol', array('courseid' => $course->id));
    foreach ($enrolmethods as $enrolmethod) {
        removelaststudents($enrolmethod);
    }
//    $context = $DB->get_record('context', array('contextlevel' => 50, 'instanceid' => $course->id));
//    unenroldroit($context->id, $enrolmethods);
}
$courses->close();

function unenroldroit($contextid, $enrolmethods) {
    global $DB;
    $unenroled = 0;
    $roleassignments = $DB->get_records('role_assignments', array('contextid' => $contextid, 'roleid' => 5));
    foreach ($roleassignments as $roleassignment) {
        deleteenrolmentdroit($roleassignment->userid, $enrolmethods);
        $DB->delete_records('role_assignments', array('id' => $roleassignment->id));
        $unenroled++;
    }
    echo "$unenroled étudiants désinscrits\n";
}

function deleteenrolmentdroit($userid, $enrolmethods) {
    global $DB;
    reset($enrolmethods);
    foreach ($enrolmethods as $enrolmethod) {
        $DB->delete_records('user_enrolments', array('enrolid' => $enrolmethod->id, 'userid' => $userid));
    }
}

function removelaststudents($enrolmethod) {
    global $DB;
    $userenrolments = $DB->get_records('user_enrolments', array('enrolid' => $enrolmethod->id));
    $unenroled = 0;
    foreach ($userenrolments as $userenrolment) {
        $ufrstudent = $DB->get_record('ufr_student', array('userid' => $userenrolment->userid, 'ufrcode' => 'Y2017-1'));
        if ($ufrstudent) {
            $DB->delete_records('user_enrolments', array('id' => $userenrolment->id));
            $unenroled++;
        }
    }
    echo "$unenroled étudiants désinscrits\n";
}
