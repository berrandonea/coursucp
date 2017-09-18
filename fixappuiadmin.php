<?php
define('CLI_SCRIPT', true);
require_once('config.php');

$administratifs = $DB->get_records('role_assignments', array('roleid' => 15));
foreach ($administratifs as $administratif) {
	fixappuiadmin($administratif->userid);
}

function fixappuiadmin($userid) {
	global $DB;
	$user = $DB->get_record('user', array('id' => $userid));
	echo "$user->firstname $user->lastname\n";
	$teacherassignments = $DB->get_records('role_assignments', array('userid' => $userid, 'roleid' => 3));
	foreach ($teacherassignments as $teacherassignment) {
		echo "  contextid : $teacherassignment->contextid\n";
		// Cet utilisateur a-t-il le "Appui administratif et pédagogique dans ce même cours
		$appuiassignment = $DB->get_record('role_assignments', array('userid' => $userid, 'contextid' => $teacherassignment->contextid, 'roleid' => 16));
		if ($appuiassignment) {
			echo "    Déjà\n";
			$DB->delete_records('role_assignments', array('id' => $teacherassignment->id));
			echo "    Suppression du rôle Enseignant\n";
		} else {
			echo "    Pas encore\n";
			$DB->set_field('role_assignments', 'roleid', 16, array('id' => $teacherassignment->id));
			echo "    Transformation en rôle Appui administratif et pédagogique\n";
		}
	}
}
