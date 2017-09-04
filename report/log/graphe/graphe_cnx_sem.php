<?php
require_once('../../../config.php');
include("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_bar.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_pie.php");
include ("../jpgraph/src/jpgraph_pie3d.php");
include ("../jpgraph/src/jpgraph_error.php");
include ("../jpgraph/src/jpgraph_scatter.php");
include ("../jpgraph/src/jpgraph_regstat.php");


global $DB, $OUTPUT, $PAGE, $USER;

// Original data points
$xdata = array(10,12,14,16,18,20,22);
//-7
$sql7 = "SELECT COUNT( id ) AS weekconnections FROM mdl_logstore_standard_log WHERE action =  'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -7 *24 *3600 )" ;
$res7 = $DB->get_record_sql($sql7);
//-6
$sql6 = "SELECT COUNT( id ) AS weekconnections FROM mdl_logstore_standard_log WHERE action =  'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -6 *24 *3600 )" ;
$res6 = $DB->get_record_sql($sql6);
//-5
$sql5 = "SELECT COUNT( id ) AS weekconnections FROM mdl_logstore_standard_log WHERE action =  'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -5 *24 *3600 )" ;
$res5 = $DB->get_record_sql($sql5);
//-4
$sql4 = "SELECT COUNT( id ) AS weekconnections FROM mdl_logstore_standard_log WHERE action =  'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -4 *24 *3600 )" ;
$res4 = $DB->get_record_sql($sql4);
//-3
$sql3 = "SELECT COUNT( id ) AS weekconnections FROM mdl_logstore_standard_log WHERE action =  'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -3 *24 *3600 )" ;
$res3 = $DB->get_record_sql($sql3);
//-2
$sql2 = "SELECT COUNT( id ) AS weekconnections FROM mdl_logstore_standard_log WHERE action =  'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -2 *24 *3600 )" ;
$res2 = $DB->get_record_sql($sql2);
//-1
$sql1 = "SELECT COUNT( id ) AS weekconnections FROM mdl_logstore_standard_log WHERE action =  'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -1 *24 *3600 )" ;
$res1 = $DB->get_record_sql($sql1);
			   
$ydata = array($res7->weekconnections - $res6->weekconnections,
               $res6->weekconnections - $res5->weekconnections,
               $res5->weekconnections - $res4->weekconnections,
               $res4->weekconnections - $res3->weekconnections,
               $res3->weekconnections - $res2->weekconnections,
               $res2->weekconnections - $res1->weekconnections,
               $res1->weekconnections);

$spline = new Spline($xdata,$ydata);
list($newx,$newy) = $spline->Get(50);

// Create the graph
$g = new Graph(600,400);
$g->SetMargin(80,20,40,80);
$g->title->Set("Connexions depuis une semaine");
$g->subtitle->SetColor('darkred');
$g->SetMarginColor('lightblue');

//$g->img->SetAntiAliasing();

// We need a linlin scale since we provide both
// x and y coordinates for the data points.
$g->SetScale('linlin');
$jour = time();
$jour7 = date('d/m/Y', $jour);
$jr0 =  $jour -7 *24 *3600;
$jr1 =  $jour -6 *24 *3600;
$jr2 =  $jour -5 *24 *3600;
$jr3 =  $jour -4 *24 *3600;
$jr4 =  $jour -3 *24 *3600;
$jr5 =  $jour -2 *24 *3600;
$jr6 =  $jour -1 *24 *3600;

$jour0 = date('d/m/Y', $jr0);
$jour1 = date('d/m/Y', $jr1);
$jour2 = date('d/m/Y', $jr2);
$jour3 = date('d/m/Y', $jr3);
$jour4 = date('d/m/Y', $jr4);
$jour5 = date('d/m/Y', $jr5);
$jour6 = date('d/m/Y', $jr6);

$g->xaxis->SetTickLabels(array($jour0, '0', $jour1,'0',$jour2,'0',$jour3,'0',$jour4,'0',$jour5,'0',$jour6));
$g->xaxis->SetLabelAngle(30);


// We use a scatterplot to illustrate the original
// contro points.
$splot = new ScatterPlot($ydata,$xdata);

//
$splot->mark->SetFillColor('red@0.3');
$splot->mark->SetColor('red@0.5');

// And a line plot to stroke the smooth curve we got
// from the original control points
$lplot = new LinePlot($ydata,$xdata);
$lplot->SetColor('navy');

// Add the plots to the graph and stroke
$g->Add($lplot);
$g->Add($splot);
$g->Stroke();

?>
