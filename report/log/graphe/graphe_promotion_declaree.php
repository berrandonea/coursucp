<?php 
require_once('../../../config.php');
include("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_bar.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_pie.php");
include ("../jpgraph/src/jpgraph_pie3d.php");
include ("../jpgraph/src/jpgraph_error.php");

global $DB, $OUTPUT, $PAGE, $USER;
$tableaucomposantes = array();
$tableauvets = array();

$composantes = $DB->get_records('chiffres_ufr');

foreach ($composantes as $composante) {

    $sql = "SELECT COUNT(DISTINCT vets.id) AS nbrvets
            FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets
            WHERE niveau.id = vets.parent_id AND niveau.parent_id = '".$composante->catdemandeid."'";
    $nbrvets = $DB->get_record_sql($sql);
    $vet = $nbrvets->nbrvets;
    $tableauvets = array_merge($tableauvets, array("$vet"));
    $tableaucomposantes[] = $composante->name;
}

// Create the Pie Graph.
$graph = new
PieGraph(650,550,"pieex1");
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Promotions déclarées");
//$graph->title->SetFont(FF_VERDANA,FS_BOLD,18);
$graph->title->SetColor("darkblue");

$graph->legend->SetColumns(2);
$graph->legend->Pos(-0.0,0.6);

// Create 3D pie plot
$p1 = new PiePlot3d($tableauvets);
//$p1->SetTheme('water');
$p1->SetTheme("earth");
$p1->SetCenter(0.5,0.3);
$p1->SetAngle(50);
$p1->SetLegends($tableaucomposantes);
$graph->Add($p1);
$graph->Stroke();
 
?>