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
$data4y=array();
$tableaucomp =array();

$composantes = $DB->get_records('chiffres_ufr');
foreach ($composantes as $composante) {
    $nbpromotions = $composante->nbcreatedvets;
    $nbrvets = $composante->nbvets;
    if ($nbrvets) {
        $promotionspercent = round($nbpromotions * 100 /$nbrvets, 1);
    } else {
        $promotionspercent = 0;
    }
    $data3y = array_merge($data3y, array("$nbpromotions"));
    $count = $nbrvets - $nbpromotions;
    $data4y = array_merge($data4y, array("$count"));
    $tableaucomp = array_merge($tableaucomp, array("$composante->name"));
}

$graph = new Graph(750,750,'auto');
$graph->SetScale("textlin");
$graph->SetY2Scale("lin",0,90);
$graph->SetY2OrderBack(false);

$graph->SetMargin(35,50,20,5);

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

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
$b3plot->SetLegend("Promotions concernées");

$b4plot->SetColor("#DA70D6");
$b4plot->SetFillColor("#DA70D6");
$b4plot->SetLegend("Promotions déclarées dans Apogée");


$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(6);
$graph->legend->Pos(0.3,0.1);
//$graph->legend->SetColor('#4E4E4E','#00A78A');

$graph->title->Set("Promotions concernées");

// Display the graph
$graph->Stroke();
