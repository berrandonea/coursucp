<?php
require_once('../../../config.php');
include("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_bar.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_pie.php");
include ("../jpgraph/src/jpgraph_pie3d.php");
include ("../jpgraph/src/jpgraph_error.php");

global $DB, $OUTPUT, $PAGE, $USER;
$data3y = array();
$data4y=array();// first track dispo
$tableaucomp =array();

//$composantes = array(
//    array("id" => "3" ,"idcat" => "1", "code" => "1", "name" => "  UFR DROIT", "nbknowncourses" => 0),
//    array("id" => "8" ,"idcat" => "5222", "code" => "2", "name" => "  UFR ECONOMIE ET GESTION", "nbknowncourses" => 0),
//    array("id" => "9" ,"idcat" => "6874", "code" => "3", "name" => "  UFR LANGUES ETUDES INTERNATIONALES", "nbknowncourses" => 0),
//    array("id" => "10" ,"idcat" => "9033", "code" => "4", "name" => "  UFR LETTRES ET SCIENCES HUMAINES", "nbknowncourses" => 0),
//    array("id" => "11" ,"idcat" => "11443", "code" => "5", "name" => "  UFR SCIENCES ET TECHNIQUES", "nbknowncourses" => 0),
//    array("id" => "13" ,"idcat" => "14963", "code" => "7", "name" => "  INSTITUT UNIVERSITAIRE DE TECHNOLOGIE", "nbknowncourses" => 0),
//    array("id" => "16" ,"idcat" => "16356", "code" => "A", "name" => "  ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION", "nbknowncourses" => 0),
//    array("id" => "17" ,"idcat" => "20080", "code" => "B", "name" => "  SCIENCES-PO ST-GERMAIN-EN-LAYE", "nbknowncourses" => 0),
//);
$composantes = $DB->get_records('chiffres_ufr');
foreach ($composantes as $composante) {
    //$sql = "SELECT COUNT(id) AS nbknowncourses FROM `mdl_cat_demandecours` WHERE code LIKE '".$composante["code"]."%-%'";
    $nbknowncourses = $composante->nbcourses;//$DB->get_record_sql($sql)->nbknowncourses;
    //$sqltr = "SELECT avail_courses FROM mdl_ufr WHERE code = '".$composante["code"]."'";
    $nbavailcourses = $composante->nbavailablecourses;//$DB->get_record_sql($sqltr)->avail_courses;
    $chaine = str_replace(" ", "\n", "$composante->name");
    $tableaucomp = array_merge($tableaucomp ,array("$composante->name"));
    //Cours déclarés
    $count = $nbknowncourses - $nbavailcourses;
    $data4y  =array_merge($data4y ,array("$count"));
    $data3y  =array_merge($data3y ,array("$nbavailcourses"));
}

$graph = new Graph(750,750,'auto');
$graph->SetScale("textlin");
$graph->SetY2Scale("lin",0,90);
$graph->SetY2OrderBack(false);

$graph->SetMargin(35,50,20,5);

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

/* $graph->yaxis->SetTickPositions(array(0,50,100,150,200,250,300,350), array(25,75,125,175,275,325));
$graph->y2axis->SetTickPositions(array(30,40,50,60,70,80,90)); */

$months = $gDateLocale->GetShortMonth();
$months = array_merge(array_slice($months,3,9), array_slice($months,0,3));
$graph->SetBox(false);

$graph->ygrid->SetFill(false);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

$graph->xaxis->SetTickLabels($tableaucomp);
$graph->xaxis->SetLabelAngle(30);

$b3plot = new BarPlot($data3y);
$b4plot = new BarPlot($data4y);

$gbbplot = new AccBarPlot(array($b3plot,$b4plot));
$gbplot = new GroupBarPlot(array($gbbplot));

$graph->Add($gbplot);

$b3plot->SetColor("#8B008B");
$b3plot->SetFillColor("#8B008B");
$b3plot->SetLegend("Cours disponibles");

$b4plot->SetColor("#DA70D6");
$b4plot->SetFillColor("#DA70D6");
$b4plot->SetLegend("Cours déclarés");


$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(6);
$graph->legend->Pos(0.3,0.1);
//$graph->legend->SetColor('#4E4E4E','#00A78A');

$graph->title->Set("Cours disponibles / Cours déclarés");

// Display the graph
$graph->Stroke();
?>
