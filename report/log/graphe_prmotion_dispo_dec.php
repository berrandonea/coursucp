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

$composantes = array(
    array("id" => "3" ,"idcat" => "1", "code" => "1", "name" => "  UFR DROIT", "nbknowncourses" => 0),
    array("id" => "8" ,"idcat" => "5222", "code" => "2", "name" => "  UFR ECONOMIE ET GESTION", "nbknowncourses" => 0),
    array("id" => "9" ,"idcat" => "6874", "code" => "3", "name" => "  UFR LANGUES ETUDES INTERNATIONALES", "nbknowncourses" => 0),
    array("id" => "10" ,"idcat" => "9033", "code" => "4", "name" => "  UFR LETTRES ET SCIENCES HUMAINES", "nbknowncourses" => 0),
    array("id" => "11" ,"idcat" => "11443", "code" => "5", "name" => "  UFR SCIENCES ET TECHNIQUES", "nbknowncourses" => 0),                        
    array("id" => "13" ,"idcat" => "14963", "code" => "7", "name" => "  INSTITUT UNIVERSITAIRE DE TECHNOLOGIE", "nbknowncourses" => 0),                    
    array("id" => "16" ,"idcat" => "16356", "code" => "A", "name" => "  ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION", "nbknowncourses" => 0),
    array("id" => "17" ,"idcat" => "20080", "code" => "B", "name" => "  SCIENCES-PO ST-GERMAIN-EN-LAYE", "nbknowncourses" => 0),                        
);
foreach ($composantes as $composante) {
	 $sqltr = "SELECT count(distinct vets.id) as nbrvets FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets where niveau.id = vets.parent_id and niveau.parent_id = '".$composante["idcat"]."'";        
	 $nbrvets = $DB->get_record_sql($sqltr)->nbrvets;
	 $sql = "SELECT promotions FROM mdl_ufr WHERE code = '".$composante["code"]."'";         
	 $nbrpromotions = $DB->get_record_sql($sql)->promotions;
	$composante = $composante['name'];
	$tableaucomp  =array_merge($tableaucomp ,array("$composante")); 
	
	//echo "<td style='text-align:center'>$nbrpromotions / $nbrvets ($promotionspercent%)</td>"; 
		$data3y  =array_merge($data3y ,array("$nbrpromotions"));
		$count = $nbrvets - $nbrpromotions;
        $data4y  =array_merge($data4y ,array("$count"));
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
$b4plot->SetLegend("Promotions déclarées");


$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(6);
$graph->legend->Pos(0.3,0.1);
//$graph->legend->SetColor('#4E4E4E','#00A78A');

$graph->title->Set("Promotions concernées / Promotions déclarées");

// Display the graph
$graph->Stroke();
 
?>