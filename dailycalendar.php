
<title>
Cron quotidien - Indiquer les séances de cours dans le calendrier
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');
require_once($CFG->libdir .'/accesslib.php');

/* Suppression de tous les événements de type "coursetime" */
$DB->delete_records('event', array('eventtype' => 'coursetime'));

/* ON CHARGE LE XML */
$xmldoc = new DOMDocument();
$xmldoc->load('/home/referentiel/sefiap_edt_enseignants_droit.xml');
$xpathvar = new Domxpath($xmldoc);

/* Pour chaque VET ayant au moins un cours sur la plateforme */
$sql = "SELECT DISTINCT category FROM {course} WHERE idnumber LIKE 'Y2017-%-%'";
$vets = $DB->get_recordset_sql($sql);
foreach ($vets as $vet) {
	// Si la VET a déjà un espace commun
	$commonspacename = "Espace commun pour $vet->name";
	$commonspace = $DB->get_record('course', array('fullname' => $commonspacename));
	if (!$commonspace) {
		$coursedata = new stdClass;
        $coursedata->fullname = $commonspacename;
        $coursedata->category = $vet->id;
        $coursedata->shortname = "$vet->idnumber-COMMUN";
        $coursedata->idnumber = "$vet->idnumber-COMMUN";

        //Si le cours est créé à partir d'un brouillon
        if ($createdcourse->brouillonid) {
                $sql = "UPDATE mdl_course "
                        . "SET fullname = '".addslashes($coursedata->fullname)."', "
                        . "category = $coursedata->category, "
                        . "shortname = '$coursedata->shortname', "
                        . "idnumber = '$coursedata->idnumber' "
                        . "WHERE id = $createdcourse->brouillonid";
                echo "$sql<br>";
                $DB->execute($sql);
                $newcourseid = $createdcourse->brouillonid;
                echo "newcourseid : $newcourseid<br>";
        } else {
                if ($createdcourse->format == "Une section par semaine") {
                        $coursedata->format = "weeks";
                } else {
                        $coursedata->format = "topics";
                }

                echo "Création proprement dite<br>";
                $newcourse = create_course($coursedata);
		$commonspace = 
	}
}
$vets->close;

$sqlcourses = "SELECT idnumber FROM {course} WHERE shortname LIKE '%-%'";
$courses = $DB->get_recordset_sql($sqlcourses);
foreach ($courses as $course) {
	$codes = explode('-', $course->idnumber);
	$codeslength = count($codes);
	if (($codeslength == 3)) {
		if (substr($codes[0], 0, 3) == 'Y20') {
			$codes[0] = $codes[1];
			$codes[1] = $codes[2];
			unset($codes[2]);
			$codeslength = 2;
		} else {
			continue;
		}
	}
	if ($codeslength != 2) {
		continue;
	}
	$query = $xpathvar->query('//Etape[@flag_etp="'.$codes[0].'"]/ELP[@ModuleCode="'.$codes[1].'"]/EDT');
	$now = time();
	foreach ($query as $edt) {
		$roomcode = $edt->getAttribute('RoomCode');
		$starthour = explode(':', $edt->getAttribute('EventStartTime')); //  16:30:00	
		$endhour = explode(':', $edt->getAttribute('EventEndTime'));     //  18:30:00
		$date = explode('-', $edt->getAttribute('EventDate'));           // 24-01-2017
		$timestart = mktime($starthour[0], $starthour[1], $starthour[2], $date[1], $date[0], $date[2]);
		$timeend = mktime($endhour[0], $endhour[1], $endhour[2], $date[1], $date[0], $date[2]);
		$event = new stdClass();
		$event->name = $course->fullname;
		$event->description = '<div class="no-overflow"><p>'.$roomcode.'</p></div>';
		$event->format = 1;
		$event->courseid = $course->id;
		$event->groupid = 0;
		$event->userid = 0;
		$event->repeatid = 0;
		$event->modulename = 0;
		$event->instance = 0;
		$event->eventtype = 'coursetime';
		$event->timestart = $timestart;
		$event->timeduration = $timeend - $timestart;
		$event->visible = 1;
		$event->uuid = '';
		$event->sequence = 1; //??
		$event->timemodified = $now;
		$DB->insert_record('event', $event);
	}
}
$courses->close;

?>
