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

$org_data = array(
  "Ressources"         => $ressources->nbr,
  "Devoirs"  => $devoirs->nbr,
  "Forums"        => $forums->nbr,
  "Salons de tchat"        => $salons->nbr,
  "Pages web"       => $pages->nbr,
  "Quiz"        => $quizs->nbr,
  "Présence"         => $presences->nbr,
  "Paquetages SCORM"      => $scorms->nbr,
  "Feedbacks" =>$feedbacks->nbr,
  "Leçons" => $lecons->nbr,
  "Visioconférences"=>$visios->nbr,
  "Ateliers" =>$ateliers->nbr,
  "Wikis" =>$wikis->nbr,
  "Sondages" => $sondages->nbr,
  "Glossaires" =>$glossaires->nbr,
  "Consultations" =>$consultations->nbr
);
$data = array_values($org_data);
$legends = array_keys($org_data);
$color_list = array('#FF0F00','#FF6600','#FF9E01','#FCD202','#B0DE09','#03D215','#0D8ECF', '#0012DF');
 
$graph = new PieGraph(700, 700);
//$graph->title->SetFont(FF_PGOTHIC);
//$graph->title->Set("Activités ");

$graph->SetMargin(35,50,20,5);
$graph->SetMarginColor("#ffffff");
$graph->SetShadow(true, 1, "gray");
$graph->SetAntiAliasing();
$graph->legend->SetPos(0.3, 0.65);
 
$p1 = new PiePlot3D($data);
$p1->SetAngle(65);
$p1->SetSize(0.48);
$p1->SetCenter(0.40,0.35);
$p1->SetStartAngle(90);
$p1->ExplodeSlice(0);
$p1->value->SetFont(FF_FONT1);
$p1->value->SetColor("#eeeeee");
$p1->SetLabelPos(0.45);
$p1->SetSliceColors($color_list);
$p1->SetLegends($legends);
 
$graph->Add($p1);
$graph->Stroke();

?>