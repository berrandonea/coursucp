<?php

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$PAGE->set_url('/blocks/calendar_upcoming/abonnement.php');
$PAGE->set_pagelayout('report');
 
$course = $DB->get_record('course', array('id'=>1), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);

$title = "Abonnements";
$PAGE->set_title($title);
$PAGE->set_heading($title);

//$previewnode = $PAGE->navigation->add(get_string('preview'), new moodle_url('/a/link/if/you/want/one.php'), navigation_node::TYPE_CONTAINER);
$previewnode = $PAGE->navigation->add(get_string('sitepages'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('Abonnements', new moodle_url('/blocks/enrol_demands/requests.php'));
$thingnode->make_active();

echo $OUTPUT->header();
if(isset($_POST['submit']))
{
	global $CFG;	
	$selected_radio = $_POST['choix'];
	if ($selected_radio == 'oui') 
	{		
		$DB->insert_record("abonnement", array('userid'=>$USER->id,'date_abonnement'=>time()));
		echo "<h4>Vous êtes abonné(e) aux actualités <strong>COURS UCP</strong></h4>";
		echo "Vous recevrez les nouvelles notification dans votre messagerie <strong>$USER->email</strong><br>";
		echo "<a href ='$CFG->wwwroot/index.php'>Retour</a>";
	}
	else
	{
		echo "<a href ='$CFG->wwwroot/index.php'>Retour</a>";
	}
}
else
{
	$sqldata = "select email from mdl_user where id =$USER->id";
	$resdata = $DB->get_record_sql($sqldata);
	echo '<form method="POST" action="abonnement.php">
	<h3>Souhaitez-vous s\'abonner aux actualités du COURS UCP ?</h3>
    <input type="radio" id="oui" name="choix" value="oui" required /> Oui <br>
	<input type="radio" id="non" name="choix" value="non" required /> Non<br><br>';
    echo '<input type="submit" name ="submit" value="S\'abonner"></form><br>';
	echo "PS : Les notifications vous seront envoyer à l'adresse <strong>$resdata->email</strong><br><br>"; 
}
echo $OUTPUT->footer();
?>


