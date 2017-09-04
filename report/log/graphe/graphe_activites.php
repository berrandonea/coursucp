<?php 
require_once('../../../config.php');
include("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_bar.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_pie.php");
include ("../jpgraph/src/jpgraph_pie3d.php");
include ("../jpgraph/src/jpgraph_error.php");

global $DB, $OUTPUT, $PAGE, $USER;

//Ressources
$sqlressources = "SELECT COUNT(id) AS nbr FROM mdl_resource";
$ressources = $DB->get_record_sql($sqlressources);
//Devoirs
$sqldevoirs = "SELECT COUNT(id) AS nbr FROM mdl_assign";
$devoirs = $DB->get_record_sql($sqldevoirs);
//Forums
$sqlforums = "SELECT COUNT(DISTINCT forum) AS nbr FROM `mdl_forum_discussions`";
$forums = $DB->get_record_sql($sqlforums);
//Salon de tchat
$sqlsalons = "SELECT COUNT(DISTINCT chatid) AS nbr FROM `mdl_chat_messages`";
$salons = $DB->get_record_sql($sqlsalons);
//Pages web
$sqlpages = "SELECT COUNT(id) AS nbr FROM `mdl_page`";
$pages = $DB->get_record_sql($sqlpages);
//Quiz
$sqlquizs = "SELECT COUNT(DISTINCT quiz) AS nbr FROM `mdl_quiz_attempts`";
$quizs = $DB->get_record_sql($sqlquizs);
//Présence
$sqlpresences = "SELECT COUNT(id) AS nbr FROM mdl_attendance";
$presences = $DB->get_record_sql($sqlpresences);
//SCORM
$sqlscorm = "SELECT COUNT(id) AS nbr FROM mdl_scorm";
$scorms = $DB->get_record_sql($sqlscorm);
//Feedbacks
$sqlfeedbacks = "SELECT COUNT(id) AS nbr FROM mdl_feedback";
$feedbacks = $DB->get_record_sql($sqlfeedbacks);
//Lecons
$sqllecons = "SELECT COUNT(id) AS nbr FROM mdl_lesson";
$lecons = $DB->get_record_sql($sqllecons);
//Visioconférences
$sqlvisio = "SELECT COUNT(id) AS nbr FROM mdl_bigbluebuttonbn";
$visios = $DB->get_record_sql($sqlvisio);
//Ateliers
$sqlateliers = "SELECT COUNT(id) AS nbr FROM mdl_workshop";
$ateliers = $DB->get_record_sql($sqlateliers);
//Wikis
$sqlwikis = "SELECT COUNT(DISTINCT wikiid) AS nbr FROM `mdl_wiki_subwikis`";
$wikis = $DB->get_record_sql($sqlwikis);
//Sondages
$sqlsondages = "SELECT COUNT(id) AS nbr FROM mdl_choice";
$sondages = $DB->get_record_sql($sqlsondages);
//Glossaires
$sqlglossaires = "SELECT COUNT(id) AS nbr FROM `mdl_glossary`";
$glossaires = $DB->get_record_sql($sqlglossaires);
//Consultation
$sqlconsultations = "SELECT COUNT(id) AS nbr FROM mdl_survey";
$consultations = $DB->get_record_sql($sqlconsultations);
// Some data
$data = array($ressources->nbr,$devoirs->nbr,$forums->nbr,$salons->nbr,$pages->nbr,$quizs->nbr,$presences->nbr,$scorms->nbr,$feedbacks->nbr,$lecons->nbr,$visios->nbr,$ateliers->nbr,$wikis->nbr,$sondages->nbr,$glossaires->nbr,$consultations->nbr);
 
// A new pie graph
$graph = new PieGraph(400,400,'auto');
 
// Don't display the border
$graph->SetFrame(false);
 
// Uncomment this line to add a drop shadow to the border
// $graph->SetShadow();
 
// Setup title
$graph->title->Set("Instances utilisées");
//$graph->title->SetFont(FF_ARIAL,FS_BOLD,15);
$graph->title->SetMargin(5); // Add a little bit more margin from the top
 $graph->SetScale("textlin");
$graph->SetY2OrderBack(false);
$graph->SetMargin(35,50,20,5);
// Create the pie plot
$p1 = new PiePlotC($data);
 
// Set size of pie
$p1->SetSize(0.35);
 
// Label font and color setup
//$p1->value->SetFont(FF_ARIAL,FS_BOLD,12);
$p1->value->SetColor('white');
 
$p1->value->Show();
 
// Setup the title on the center circle
$p1->midtitle->Set("Instances utilisées");
//$p1->midtitle->SetFont(FF_VERDANA,FS_NORMAL,11);
 
// Set color for mid circle
$p1->SetMidColor('yellow');
 
// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);
 
// The label array values may have printf() formatting in them. The argument to the
// form,at string will be the value of the slice (either the percetage or absolute
// depending on what was specified in the SetLabelType() above.
$lbl = array("Ressources\n%.1f%%","Devoirs\n%.1f%%","Forums\n%.1f%%","Salons de tchat\n%.1f%%",
"Pages web\n%.1f%%","Quiz\n%.1f%%","Présence \n%.1f%%","Paquetages SCORM\n%.1f%%","Feedbacks \n%.1f%%",
"Leçons\n%.1f%%","Visioconférences\n%.1f%%","Ateliers\n%.1f%%","Wikis\n%.1f%%","Sondages\n%.1f%%",
"Glossaires\n%.1f%%","Consultations\n%.1f%%");
$p1->SetLabels($lbl);
 
// Uncomment this line to remove the borders around the slices
// $p1->ShowBorder(false);
 
// Add drop shadow to slices
$p1->SetShadow();
 
// Explode all slices 15 pixels
$p1->ExplodeAll(15);
 
// Add plot to pie graph
$graph->Add($p1);
 
// .. and send the image on it's marry way to the browser
$graph->Stroke();


?>