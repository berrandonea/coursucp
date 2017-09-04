<?php
require_once('../../../config.php');
include("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_bar.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_pie.php");
include ("../jpgraph/src/jpgraph_pie3d.php");
include ("../jpgraph/src/jpgraph_error.php");

global $DB, $OUTPUT, $PAGE, $USER;
$tableauAnnees = array();
$tableauNombreVentes = array();

/*$composantes = array(
    array("id" => "3" ,"idcat" => "1", "code" => "1", "name" => "  UFR DROIT", "nbknowncourses" => 0),
    array("id" => "8" ,"idcat" => "5222", "code" => "2", "name" => "  UFR ECONOMIE ET GESTION", "nbknowncourses" => 0),
    array("id" => "9" ,"idcat" => "6874", "code" => "3", "name" => "  UFR LANGUES ETUDES INTERNATIONALES", "nbknowncourses" => 0),
    array("id" => "10" ,"idcat" => "9033", "code" => "4", "name" => "  UFR LETTRES ET SCIENCES HUMAINES", "nbknowncourses" => 0),
    array("id" => "11" ,"idcat" => "11443", "code" => "5", "name" => "  UFR SCIENCES ET TECHNIQUES", "nbknowncourses" => 0),
    array("id" => "13" ,"idcat" => "14963", "code" => "7", "name" => "  INSTITUT UNIVERSITAIRE DE TECHNOLOGIE", "nbknowncourses" => 0),
    array("id" => "16" ,"idcat" => "16356", "code" => "A", "name" => "  ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION", "nbknowncourses" => 0),
    array("id" => "17" ,"idcat" => "20080", "code" => "B", "name" => "  SCIENCES-PO ST-GERMAIN-EN-LAYE", "nbknowncourses" => 0),
);*/
$composantes = $DB->get_records('chiffres_ufr');
foreach ($composantes as $composante) {
/*	$sql = "SELECT COUNT(id) AS nbknowncourses FROM `mdl_cat_demandecours` WHERE code LIKE '".$composante["code"]."%-%'";
    $nbknowncourses = $DB->get_record_sql($sql)->nbknowncourses;
	$composante = $composante['name'];*/
	$tableauAnnees  =array_merge($tableauAnnees ,array("$composante->name"));
    //$composantes[$numcomposante]["nbknowncourses"] = $nbknowncourses;
	$tableauNombreVentes  =array_merge($tableauNombreVentes ,array("$composante->nbcourses"));
}
  // Create the Pie Graph.
  $graph = new
  PieGraph(650,550,"pieex1");
  $graph->SetShadow();

  // Set A title for the plot
  $graph->title->Set("Cours déclarés");
  //$graph->title->SetFont(FF_VERDANA,FS_BOLD,18);
  $graph->title->SetColor("darkblue");

  $graph->legend->SetColumns(2);
  $graph->legend->Pos(-0.0,0.6);

  // Create 3D pie plot
  $p1 = new PiePlot3d($tableauNombreVentes);
  //$p1->SetTheme('water');
  $p1->SetTheme("earth");
  $p1->SetCenter(0.5,0.3);
  $p1->SetAngle(50);
  $p1->SetLegends($tableauAnnees);
  $graph->Add($p1);
  $graph->Stroke();
?>
