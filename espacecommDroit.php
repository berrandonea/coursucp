
<?php
define('CLI_SCRIPT', true);
include 'config.php';

$ufrstudents = $DB->get_recordset_sql("SELECT * FROM {ufr_student} WHERE ufrcode LIKE '1%' AND student = 1");
$now = time();
foreach ($ufrstudents as $ufrstudent) {
    $userenrolment = $DB->get_record('user_enrolments', array('enrolid' => 4538, 'userid' => $ufrstudent->userid));
    if (!$userenrolment) {
        $userenrolment = new stdClass();
        $userenrolment->enrolid = 4538;
        $userenrolment->userid = $ufrstudent->userid;
        $userenrolment->timestart = $now;
        $userenrolment->timeend = 0;
        $userenrolment->modifierid = 2;
        $userenrolment->timecreated = $now;
        $userenrolment->timemodified = $now;
        $DB->insert_record('user_enrolments', $userenrolment);
        print_object($userenrolment);
    }
    $roleassignment = $DB->get_record('role_assignments', array('contextid' => 55302, 'roleid' => 5, 'userid' => $ufrstudent->userid));
    if (!$roleassignment) {
        $roleassignment = new stdClass();
        $roleassignment->roleid = 5;
        $roleassignment->contextid = 55302;
        $roleassignment->userid = $ufrstudent->userid;
        $roleassignment->timemodified = $now;
        $roleassignment->modifierid = 2;
        $DB->insert_record('role_assignments', $roleassignment);
        print_object($roleassignment);
    }
}
$ufrstudents->close();


$studentvets = $DB->get_recordset('student_vet');
$now = time();
foreach ($studentvets as $studentvet) {
    $vet = $DB->get_record('course_categories', array('id' => $studentvet->categoryid));
    $ufrcode = substr($vet->idnumber, 0, 1);
    if ($ufrcode == 1) {
        echo "$vet->name\n";
        //Groupe dans le cours 1512 ?
        $group = $DB->get_record('groups', array('courseid' => 1512, 'name' => $vet->name));
        if (!$group) {
            $group = new stdClass();
            $group->courseid = 1512;
            $group->idnumber = $vet->idnumber;
            $group->name = $vet->name;
            $group->timecreated = $now;
            $group->timemodified = $now;
            $group->id = $DB->insert_record('groups', $group);
            print_object($group);
        }
        $groupmember = $DB->get_record('groups_members', array('groupid' => $group->id, 'userid' => $studentvet->studentid));
        if (!$groupmember) {
            $groupmember = new stdClass();
            $groupmember->groupid = $group->id;
            $groupmember->userid = $studentvet->studentid;
            $groupmember->timeadded = $now;
            $DB->insert_record('groups_members', $groupmember);
            print_object($groupmember);
        }
    }
}
$studentvets->close();


?>
