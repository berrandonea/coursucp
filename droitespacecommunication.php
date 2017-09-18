<title>
Inscriptions à l'espace communication de l'UFR Droit
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');

$course = $DB->get_record('course', array('id' => 1611));
$coursecontext = $DB->get_record('context', array('contextlevel' => 50, 'instanceid' => $course->id));
$groups = $DB->get_records('groups', array('courseid' => $course->id));
$teachersgroup = $DB->get_record('groups', array('courseid' => $course->id, 'name' => 'Enseignants'));
$appuigroup = $DB->get_record('groups', array('courseid' => $course->id, 'name' => 'Appui administratif et pédagogique'));

foreach ($groups as $group) {
	// On cherche dans la table student_vet les étudiants qui doivent être inscrits dans ce groupe.
	$category = $DB->get_record('course_categories', array('idnumber' => "Y2017-$group->idnumber"));
	if ($category) {
		$vetstudents = $DB->get_records('student_vet', array('categoryid' => $category->id));
	    foreach ($vetstudents as $vetstudent) {
		    checkenrolandgroup($vetstudent->studentid, $group, $coursecontext, 5);
	    }
	}	
}

$administratifs = $DB->get_records('role_assignments', array('roleid' => 15));
foreach ($administratifs as $administratif) { // Attention, cela inscrit tous les administratifs. On ne peut pas vérifier que ce sont ceux de l'UFR Droit.
	checkenrolandgroup($administratif->userid, $appuigroup, $coursecontext, 16);
}


// On donne aux enseignants de l'UFR Droit le rôle "Enseignant non éditeur"

$context = get_context_instance(CONTEXT_SYSTEM);
$xmldoc = new DOMDocument();
$xmldoc->load('/home/referentiel/DOKEOS_Enseignants_Affectations.xml');
$xpathvar = new Domxpath($xmldoc);

$queryteachers = $xpathvar->query('//Teacher');
foreach($queryteachers as $result){
    if($teacheruid = $result->getAttribute('StaffUID')){        
        $diplomes = $xpathvar->query('//Teacher[@StaffUID="'.$teacheruid.'"]/Diplomes');
        foreach ($diplomes as $diplome) {
            $ufrcode = $diplome->getAttribute('Composante');
            if ($ufrcode == '1') {
				$teacher = $DB->get_record('user', array('username' => $teacheruid));
				if ($teacher) {
					checkenrolandgroup($teacher->id, $teachersgroup, $coursecontext, 4); // Rôle Enseignant non éditeur
				}
			}
        }
	}
}

function checkenrolandgroup($userid, $group, $coursecontext, $roleid) {
	global $DB;
	$previousassignment = $DB->get_record('role_assignments', array('contextid' => $coursecontext->id, 'userid' => $userid));
	if (!$previousassignment) {
	    enroluser($userid, $coursecontext, $roleid);
	}
	$grouped = $DB->get_record('groups_members', array('groupid' => $group->id, 'userid' => $userid));
	if (!$grouped) {
		groupuser($userid, $group);
	}
}

function enroluser($userid, $coursecontext, $roleid) {
	global $DB;
	$now = time();
	$manualenrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $coursecontext->instanceid));
	$userenrolment = new stdClass();
	$userenrolment->enrolid = $manualenrol->id;
	$userenrolment->userid = $userid;
	$userenrolment->timestart = $now;
	$userenrolment->timecreated = $now;
	$userenrolment->timemodified = $now;
	$userenrolment->modifierid = 2; //admin
	$userenrolment->id = $DB->insert_record('user_enrolments', $userenrolment);
	$roleassignment = new stdClass();
	$roleassignment->roleid = $roleid;
	$roleassignment->contextid = $coursecontext->id;
	$roleassignment->userid = $userid;
	$roleassignment->timemodified = $now;
	$roleassignment->modifierid = 2; //admin
	$roleassignment->id = $DB->insert_record('role_assignments', $roleassignment);
}

function groupuser($userid, $group) {
	global $DB;
	$groupmember = new stdClass();
	$groupmember->groupid = $group->id;
	$groupmember->userid = $userid;
	$groupmember->timeadded = time();
	$groupmember->id = $DB->insert_record('groups_members', $groupmember);
}


