<?php
require_once(dirname(__FILE__) . '/../config.php');
include ("../report/log/jpgraph-3.5.0b1/src/jpgraph.php");
include ("../report/log/jpgraph-3.5.0b1/src/jpgraph_bar.php");
include ("../report/log/jpgraph-3.5.0b1/src/jpgraph_line.php");
include ("../report/log/jpgraph-3.5.0b1/src/jpgraph_error.php");

$x_axis = array();
$y_axis = array();
$i = 0;
$id  = optional_param('id', 0, PARAM_INT);

$tableaudatesdelasemaine = array(); 
$convertdate = "SELECT timecreated,timemodified,diff, datesession, FROM_UNIXTIME(datesession) as valeur_datetime, CAST(FROM_UNIXTIME(datesession) as date) as valeur_date FROM mdl_log_session WHERE userid = 3029";
//$DB->set_debug(true);
$resultconvertdate = $DB->get_record_sql($convertdate);
//$DB->set_debug(false);

$now = time(); //Supposons que nous sommes jeudi

//vendredi
$first = $now - 6 * 24 * 3600;//BRICE $first= mktime(0, 0, 0, date("m")  , date("d")-6, date("Y"));
$last6 = date("Y-m-d", ($first));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last6"));

//samedi
$a = $now - 5 * 24 * 3600;//BRICE $a= mkime(0, 0, 0, date("m")  , date("d")-5, date("Y"));
$last5 = date("Y-m-d", ($a));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last5"));

//dimanche
$au = $now - 4 * 24 * 3600;//BRICE $au= mktime(0, 0, 0, date("m")  , date("d")-4, date("Y"));
$last4 = date("Y-m-d", ($au));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last4"));

//lundi
$auj = $now - 3 * 24 * 3600;//BRICE $auj= mktime(0, 0, 0, date("m")  , date("d")-3, date("Y"));
$last3 = date("Y-m-d", ($auj));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last3"));

//mardi
$aujo = $now - 2 * 24 * 3600;//BRICE $aujo= mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
$last2 = date("Y-m-d", ($aujo));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last2"));

//mercredi
$aujou = $now - 1 * 24 * 3600;//BRICE $aujou= mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
$last1 = date("Y-m-d", ($aujou));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last1"));

//exemple jeudi
$aujour = $now;//BRICE $aujour  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
$last = date("Y-m-d", ($aujour));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last"));

//print_r($tableaudatesdelasemaine);

foreach ($tableaudatesdelasemaine as $datesemaine)
{
	$now = date('Y-m-d');
	$sql = "SELECT timecreated,timemodified,diff, datesession, FROM_UNIXTIME(datesession) as valeur_datetime, CAST(FROM_UNIXTIME(datesession) as date) as valeur_date "
                . "FROM mdl_log_session "
                . "WHERE userid = 3029 "
                . "AND CAST(FROM_UNIXTIME(datesession) as date) = '$datesemaine'  "
                . "AND CAST(FROM_UNIXTIME(datesession) as date) <='$now'";
	$resultsql = $DB->get_record_sql($sql);
    $timestamp = strtotime($datesemaine);
    setlocale(LC_TIME, "fr");
    $convert= strftime("%A %d/%m", $timestamp);
    $x_axis = array_merge($x_axis, array("$convert"));
    //print_r($x_axis);  
	//echo "<br><br>";
	if($resultsql)
	{
            $onvertminute = $resultsql->diff /60;
	} 
	else
	{
            $onvertminute = 0;
    }       
    $y_axis =array_merge($y_axis,array("$onvertminute"));
	//print_r($y_axis);
}
$graph = new Graph(700,400);
$graph->img->SetMargin(40,40,40,40); 
$graph->img->SetAntiAliasing();
$graph->SetScale("textlin");
$graph->SetShadow();
$graph->title->SetFont(FF_FONT1,FS_BOLD);
 
 $graph->yscale->SetGrace(0);
 
 
$p1 = new LinePlot($y_axis);
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(4);
$p1->SetColor("blue");
$p1->SetCenter();
$graph->Add($p1);
$graph->xaxis->title->Set("Dates");
$graph->yaxis->title->Set("Temps en minutes");
$graph->xaxis->SetTickLabels($x_axis);
$graph->Stroke();

?>