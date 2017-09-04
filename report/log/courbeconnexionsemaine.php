<?php
//require_once(dirname(__FILE__) . '/../config.php');
        //include ("../report/log/jpgraph-3.5.0b1/src/jpgraph.php");
        //include ("../report/log/jpgraph-3.5.0b1/src/jpgraph_bar.php");
       // include ("../report/log/jpgraph-3.5.0b1/src/jpgraph_line.php");
       // include ("../report/log/jpgraph-3.5.0b1/src/jpgraph_error.php");
        require('../../config.php');
        include ("jpgraph-3.5.0b1/src/jpgraph.php");
        include ("jpgraph-3.5.0b1/src/jpgraph_bar.php");
       include ("jpgraph-3.5.0b1/src/jpgraph_line.php");
        include ("jpgraph-3.5.0b1/src/jpgraph_error.php");
        
$x_axis = array();
$y_axis = array();
$i = 0;
$id  = optional_param('id', 0, PARAM_INT);

$tableaudatesdelasemaine = array(); 

/*$lundi= date('l d F Y', mktime(0, 0, 0, date('m'), date('d')-date('N')+1, date('Y')));
//echo "$lundi<br>";
$lundic = date("Y-m-d", strtotime($lundi));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$lundic"));

$mardi = date('l d F Y', mktime(0, 0, 0, date('m'), date('d')-date('N')+2, date('Y')));
//echo "$mardi<br>";
$mardic = date("Y-m-d", strtotime($mardi));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$mardic"));

$mercredi = date('l d F Y', mktime(0, 0, 0, date('m'), date('d')-date('N')+3, date('Y')));
//echo "$mercredi<br>";
$mercredic = date("Y-m-d", strtotime($mercredi));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$mercredic"));

$jeudi = date('l d F Y', mktime(0, 0, 0, date('m'), date('d')-date('N')+4, date('Y')));
//echo "$jeudi<br>";
$jeudic = date("Y-m-d", strtotime($jeudi));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$jeudic"));
$vendredi = date('l d F Y', mktime(0, 0, 0, date('m'), date('d')-date('N')+5, date('Y')));
//echo "$vendredi<br>";
$vendredic = date("Y-m-d", strtotime($vendredi));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$vendredic"));
//print_r($tableaudatesdelasemaine) ;*/

//date('d/m/Y', 1234567890)
$convertdate = "SELECT timecreated,timemodified,diff, datesession, FROM_UNIXTIME(datesession) as valeur_datetime, CAST(FROM_UNIXTIME(datesession) as date) as valeur_date FROM mdl_log_session where userid=12";
$resultconvertdate = $DB->get_record_sql($convertdate);


//vendredi
$first= mktime(0, 0, 0, date("m")  , date("d")-6, date("Y"));
$last6 = date("Y-m-d", ($first));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last6"));

//samedi
$a= mktime(0, 0, 0, date("m")  , date("d")-5, date("Y"));
$last5 = date("Y-m-d", ($a));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last5"));

//dimanche
$au= mktime(0, 0, 0, date("m")  , date("d")-4, date("Y"));
$last4 = date("Y-m-d", ($au));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last4"));

//lundi
$auj= mktime(0, 0, 0, date("m")  , date("d")-3, date("Y"));
$last3 = date("Y-m-d", ($auj));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last3"));

//mardi
$aujo= mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
$last2 = date("Y-m-d", ($aujo));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last2"));

//mercredi
$aujou= mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
$last1 = date("Y-m-d", ($aujou));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last1"));

//exemple jeudi
$aujour  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
$last = date("Y-m-d", ($aujour));
$tableaudatesdelasemaine = array_merge($tableaudatesdelasemaine, array("$last"));

//print_r($tableaudatesdelasemaine) ;

foreach ($tableaudatesdelasemaine as $datesemaine)
{
	//echo "$datesemaine<br>";
	$now = date('Y-m-d');
	$sql = "SELECT timecreated,timemodified,diff, datesession, FROM_UNIXTIME(datesession) as valeur_datetime, CAST(FROM_UNIXTIME(datesession) as date) as valeur_date FROM mdl_log_session where userid=12 and CAST(FROM_UNIXTIME(datesession) as date) = '$datesemaine'  and CAST(FROM_UNIXTIME(datesession) as date) <='$now'";
	$resultsql = $DB->get_record_sql($sql);
	//foreach ($resultsql as $res)
	//{
	if($resultsql)
	{
	   	//$convert = date('d/m', $resultsql->timecreated);
	   	$convert =  $datesemaine;
	   	//$convert=$datesemaine[date('w')].' '.date('j').' '.$mois[date('n')-1];
	   	//$convert = date("D-Y-m-d", ($datesemaine));
	   	
   		$onvertminute = $resultsql->diff /60;
   	    $x_axis = array_merge($x_axis, array("$convert"));
        $y_axis =array_merge($y_axis,array("$onvertminute"));
	}
	else 
	{
   	    
   	    if($resultsql->valeur_date <= $now)
   	    {
   	    		$convert =  $datesemaine;  	
   		$onvertminute = $resultsql->diff /60;
   		$x_axis = array_merge($x_axis, array("$convert"));
   	    	$y_axis =array_merge($y_axis,array("$onvertminute"));
   	    }
		
	}
	//}
	
	//echo "$sql<br>";
}
$graph = new Graph(800,500);
$graph->img->SetMargin(40,40,40,40); 
$graph->img->SetAntiAliasing();
$graph->SetScale("textlin");
$graph->SetShadow();
$graph->title->Set("Temps de connexion par jour");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
 
 
// Use 20% "grace" to get slightly larger scale then min/max of
// data
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