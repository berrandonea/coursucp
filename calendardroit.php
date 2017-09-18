
<title>
Cron quotidien - Indiquer les séances de cours dans le calendrier (pas seulement en Droit)
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
//~ $xmldoc->load('/home/referentiel/testdroit.xml');
$xpathvar = new Domxpath($xmldoc);

$sqlcourses = "SELECT * FROM {course} WHERE idnumber LIKE 'Y2017-%-%'";
$courses = $DB->get_recordset_sql($sqlcourses);
foreach ($courses as $course) {
	$codes = explode('-', $course->idnumber);
	$codeslength = count($codes);
	if (($codeslength != 3) || ($codes[0] != 'Y2017')) {
		continue;
	}	
	$querytext = '//Etape[@flag_etp="'.$codes[1].'"]/ELP[@ModuleCode="'.$codes[2].'"]/Enseignant/EDT';
	$query = $xpathvar->query($querytext);
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
$courses->close();
?>
